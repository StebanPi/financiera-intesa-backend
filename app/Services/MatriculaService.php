<?php

namespace App\Services;

use App\Http\Controllers\MoneyController;
use App\Services\CarteraService;
use App\Models\Cost;
use App\Models\Entry;
use App\Models\Matricula;
use App\Models\OtherEntry;
use App\Models\Purse;
use App\Models\historyPurse;
use App\Models\InstitutionSetting;
use Dompdf\Dompdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MatriculaService
{
    /**
     * Listar matrículas con filtros, orden y paginación.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $query = Matricula::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre_completo', 'like', "%{$search}%")
                    ->orWhere('numero_documento', 'like', "%{$search}%")
                    ->orWhere('correo_gmail', 'like', "%{$search}%")
                    ->orWhere('telefono_personal', 'like', "%{$search}%")
                    ->orWhere('programa', 'like', "%{$search}%");
            });
        }

        if ($request->filled('programa')) {
            $query->where('programa', 'like', "%{$request->programa}%");
        }

        if ($request->filled('horario')) {
            $query->where('horario', $request->horario);
        }

        $estadoFilter = $request->get('estado_estudiante', 'Activo');
        if ($estadoFilter !== 'Todos') {
            $query->where('estado_estudiante', $estadoFilter);
        }

        $query->where('sede', $request->get('sede_activa', 'BARRANCABERMEJA'));

        if ($request->filled('modalidad')) {
            $query->where('modalidad', $request->modalidad);
        }

        $sort = $request->get('sort', 'id');
        $order = strtolower($request->get('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $order);

        $perPage = min((int) $request->get('per_page', 15), 100);

        return $query->paginate($perPage);
    }

    /**
     * Obtener matrícula por cod_alumno, opcionalmente filtrando por sede.
     * Cuando se pasa sede se evita devolver el registro de otra sede con el mismo cod_alumno.
     */
    public function getByCodAlumno(string $cod_alumno, ?string $sede = null): Matricula
    {
        $query = Matricula::where('cod_alumno', $cod_alumno);
        if ($sede) {
            $query->where('sede', $sede);
        }
        return $query->firstOrFail();
    }

    /**
     * Obtener toda la información de la matrícula (Costos, Cartera, Abonos, Otros Ingresos).
     */
    public function getFullEnrollmentData(string $cod_alumno, ?string $sede = null): array
    {
        $matricula = $this->getByCodAlumno($cod_alumno, $sede);
        $costs = Cost::where('cod_alumno', $cod_alumno)->orderBy('numero_semestre', 'asc')->get();
        
        // Abonos (Entries) - Buscamos por cod_alumno directamente
        $entryData = Entry::where('cod_alumno', $cod_alumno)
            ->with(['conceptoObj', 'elaboradoObj', 'cost'])
            ->orderBy('fecha_recibo', 'desc')
            ->get();
        
        // Otros Ingresos (OtherEntries) - Buscamos por cod_alumno directamente
        $otherEntryData = OtherEntry::where('cod_alumno', $cod_alumno)
            ->with(['conceptoObj', 'elaboradoObj', 'cost'])
            ->orderBy('fecha_recibo', 'desc')
            ->get();

        $pursesData = [];

        if ($costs->isNotEmpty()) {
            // Cartera (Calculada con CarteraService)
            // Obtiene cuotas, estados (Al día, En Mora), saldos y totales
            $pursesData = CarteraService::calcularCartera(null, $cod_alumno);
        } else {
             // Estructura vacía si no hay costos
             $pursesData = [
                'cuotas' => [],
                'totales' => [
                    'total_abono' => 0,
                    'cuotas_total' => 0,
                    'total_abonado' => 0,
                    'saldo_pendiente' => 0,
                    'saldo_a_favor' => 0,
                    'saldo_en_mora' => 0,
                ],
                'hoy' => date('Y-m-d'),
            ];
        }

        return [
            'matricula' => $matricula,
            'costs' => $costs,
            'entries' => $entryData,
            'other_entries' => $otherEntryData,
            'cartera' => $pursesData, // Ahora contiene el objeto completo calculado (cuotas + totales)
        ];
    }

    /**
     * Crear matrícula. cod_alumno = numero_documento. Reutiliza IDs eliminados.
     *
     * @param  array<string, mixed>  $data  Datos ya validados (campos fillable de Matricula)
     * @throws \Illuminate\Validation\ValidationException si numero_documento ya existe
     */
    public function create(array $data): Matricula
    {
        $cod_alumno = $data['numero_documento'];

        $sede = $data['sede'] ?? null;

        if (Matricula::where('cod_alumno', $cod_alumno)->where('sede', $sede)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'numero_documento' => ['Este número de documento ya está registrado en esta sede.'],
            ]);
        }

        $availableId = $this->findAvailableId();

        $numeroMatricula = (int) (DB::table('matriculas')
            ->where('sede', $sede)
            ->max('numero_matricula') ?? 0) + 1;

        $fillable = (new Matricula)->getFillable();
        $row = array_intersect_key($data, array_flip($fillable));
        $row['cod_alumno'] = $cod_alumno;
        $row['id'] = $availableId;
        $row['numero_matricula'] = $numeroMatricula;
        $row['created_at'] = now();
        $row['updated_at'] = now();

        DB::table('matriculas')->insert($row);

        return Matricula::find($availableId);
    }

    private function findAvailableId(): int
    {
        if (!Matricula::where('id', 1)->exists()) {
            return 1;
        }

        $result = DB::selectOne("
            SELECT MIN(t1.id + 1) as available_id
            FROM matriculas t1
            LEFT JOIN matriculas t2 ON t1.id + 1 = t2.id
            WHERE t2.id IS NULL
            LIMIT 1
        ");

        if ($result === null || $result->available_id === null) {
            return (int) (Matricula::max('id') ?? 0) + 1;
        }

        return (int) $result->available_id;
    }

    /**
     * Actualizar matrícula. Solo campos enviados (fillable).
     *
     * @param  array<string, mixed>  $data
     */
    public function update(string $cod_alumno, array $data, ?string $sede = null): Matricula
    {
        $matricula = $this->getByCodAlumno($cod_alumno, $sede);
        $fillable = (new Matricula)->getFillable();
        $filtered = array_intersect_key($data, array_flip($fillable));
        $matricula->update($filtered);

        return $matricula->fresh();
    }

    /**
     * Eliminar matrícula. Si hay Cost/Entry/OtherEntry/Purse y $confirmarCascada=false, lanza ValidationException.
     * Si $confirmarCascada=true, elimina en cascada: entries, other_entries, history_purses, purses, cost, matricula.
     */
    public function delete(string $cod_alumno, bool $confirmarCascada = false, ?string $sede = null): void
    {
        $matricula = $this->getByCodAlumno($cod_alumno, $sede);
        $cost = Cost::where('cod_alumno', $cod_alumno)->first();

        $entriesCount = $cost ? Entry::where('id_cost', $cost->id)->count() : 0;
        $otherCount = $cost ? OtherEntry::where('id_cost', $cost->id)->count() : 0;
        $pursesCount = $cost ? Purse::where('id_cost', $cost->id)->count() : 0;
        $total = $entriesCount + $otherCount + $pursesCount;

        if ($total > 0 && !$confirmarCascada) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'confirmar_cascada' => ["No se puede eliminar: tiene datos relacionados ({$entriesCount} abonos, {$otherCount} otros ingresos, {$pursesCount} cuotas). Use ?confirmar_cascada=1 para eliminar en cascada."],
            ]);
        }

        if ($total > 0 && $cost) {
            $entries = Entry::where('id_cost', $cost->id)->get();
            foreach ($entries as $e) {
                $e->delete();
            }
            $otherEntries = OtherEntry::where('id_cost', $cost->id)->get();
            foreach ($otherEntries as $o) {
                $o->delete();
            }
            $pursesToDelete = Purse::where('id_cost', $cost->id)->get();
            if ($pursesToDelete->isNotEmpty()) {
                historyPurse::whereIn('id_purse', $pursesToDelete->pluck('id'))->delete();
            }
            Purse::where('id_cost', $cost->id)->delete();
            $cost->delete();
        }

        $matricula->delete();
    }

    /**
     * Subir foto del estudiante. Reemplaza la anterior. Guarda en storage/public (path: students/{cod}_{time}.{ext}).
     *
     * @return array{url: string, path: string, mime: string, size: int}
     */
    public function uploadPhoto(string $cod_alumno, UploadedFile $file, ?string $sede = null): array
    {
        $matricula = $this->getByCodAlumno($cod_alumno, $sede);

        if ($matricula->photo_path && Storage::disk('public')->exists($matricula->photo_path)) {
            Storage::disk('public')->delete($matricula->photo_path);
        }

        $filename = 'students/' . $cod_alumno . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public', $filename);

        $matricula->photo_path = $filename;
        $matricula->save();

        return [
            'url' => '/matriculas/' . $cod_alumno . '/foto', // Ruta relativa para que el frontend construya la URL completa
            'path' => $filename,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Generar y devolver PDF de la ficha de matrícula como stream (inline).
     */
    public function streamPdf(string $cod_alumno, ?string $sede = null): Response
    {
        $matricula = $this->getByCodAlumno($cod_alumno, $sede);
        $institucion = InstitutionSetting::getSettings();

        $costs = DB::table('costs')
            ->where('cod_alumno', $cod_alumno)
            ->orderBy('numero_semestre', 'asc')
            ->get();

        // Formatear todos los costs
        $costs->transform(function ($c) {
            return MoneyController::datas($c, ['valor_semestre', 'valor_total_semestre', 'descuento', 'valor_neto', 'saldo_financiar', 'valor_cuotas']);
        });

        $carteraData = null;
        if ($costs->isNotEmpty()) {
            try {
                // Calcular cartera con el primer costo para mantener compatibilidad si es necesario
                $carteraData = CarteraService::calcularCartera($costs->first()->id);
            } catch (\Throwable $e) {
                \Log::error('CarteraService PDF: ' . $e->getMessage());
            }
        }

        $photoBase64 = null;
        if ($matricula->photo_path && Storage::disk('public')->exists($matricula->photo_path)) {
            try {
                $data = Storage::disk('public')->get($matricula->photo_path);
                $ext = pathinfo($matricula->photo_path, PATHINFO_EXTENSION);
                $mime = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
                $photoBase64 = 'data:' . $mime . ';base64,' . base64_encode($data);
            } catch (\Throwable $e) {
                \Log::error('Foto PDF: ' . $e->getMessage());
            }
        }

        $qrCodeBase64 = null;
        $qrPath = public_path('images/qr-code.png');
        if (file_exists($qrPath)) {
            try {
                $data = file_get_contents($qrPath);
                $ext = pathinfo($qrPath, PATHINFO_EXTENSION);
                $mime = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
                $qrCodeBase64 = 'data:' . $mime . ';base64,' . base64_encode($data);
            } catch (\Throwable $e) {
                \Log::warning('QR PDF: ' . $e->getMessage());
            }
        }

        $html = view('matricula.ficha-pdf', [
            'matricula' => $matricula,
            'institucion' => $institucion,
            'costs' => $costs,
            'carteraData' => $carteraData,
            'photoBase64' => $photoBase64,
            'qrCodeBase64' => $qrCodeBase64,
            'hideDefaultFooter' => true,
        ])->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = \Illuminate\Support\Str::slug("Ficha Matricula {$matricula->nombre_completo} {$cod_alumno}") . '.pdf';
        $pdfBinary = $dompdf->output();

        return response($pdfBinary, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
