<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Models\Cost;
use App\Models\Entry;
use App\Models\OtherEntry;
use App\Models\Purse;
use App\Models\historyPurse;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\MoneyController;
use App\Http\Controllers\OtherEntryController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\PurseController;
use App\Models\consecutive;
use App\Models\concepto;
use App\Models\elaborado;
use App\Models\haber;
use App\Models\debe;
use App\Models\otrosConcepto;
use App\Models\InstitutionSetting;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MatriculaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Matricula::query();

        // Filtros de búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
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

        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', $request->tipo_documento);
        }

        $matriculas = $query->orderBy('nombre_completo', 'asc')->paginate(50);

        return view('matricula.index', [
            'matriculas' => $matriculas,
            'filters' => $request->all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Obtener datos desde ajustes del sistema
        $programs = Program::where('active', true)->orderBy('name')->get();
        $schedules = Schedule::where('active', true)->orderBy('name')->get();
        $groups = Group::where('active', true)->orderBy('name')->get();
        
        return view('matricula.create', [
            'programs' => $programs,
            'schedules' => $schedules,
            'groups' => $groups,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Obtener valores válidos desde las tablas de ajustes
        $validPrograms = Program::where('active', true)->pluck('name')->toArray();
        $validSchedules = Schedule::where('active', true)->pluck('name')->toArray();
        $validGroups = Group::where('active', true)->pluck('name')->toArray();
        
        $rules = [
            'nombre_completo' => 'required|string|max:255',
            'numero_documento' => 'required|string|max:255|unique:matriculas,numero_documento',
            'tipo_documento' => 'required|in:CC,TI,PPT',
            'departamento' => 'nullable|string|max:255',
            'estado_civil' => 'nullable|string|max:255',
            'ocupacion' => 'nullable|string|max:255',
            'nivel_formacion' => 'nullable|string|max:255',
            'tiene_discapacidad' => 'nullable|in:No,Sí,Prefiero no decir',
            'programa' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($validPrograms) {
                if (!in_array($value, $validPrograms)) {
                    $fail('El programa seleccionado no es válido o no está activo.');
                }
            }],
            'sede' => 'required|in:Barrancabermeja,Aguachica,Virtual',
            'estado_estudiante' => 'required|in:Activo,Inactivo,Por Certificar,Certificado,Retirado,Suspendido,Todos',
            'horario' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($validSchedules) {
                if (!in_array($value, $validSchedules)) {
                    $fail('El horario seleccionado no es válido o no está activo.');
                }
            }],
            'talla_uniforme' => 'nullable|in:XS,S,M,L,XL,XXL,XXXL',
            'semestre_actual' => 'required|in:I,II,Ninguno (curso)',
            'anio' => 'required|string|max:255',
            'numero_grupo' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($validGroups) {
                if (!in_array($value, $validGroups)) {
                    $fail('El grupo seleccionado no es válido o no está activo.');
                }
            }],
            'contraseña_plataforma' => 'nullable|string|max:255',
            'tipo_discapacidad' => 'required_if:tiene_discapacidad,Sí|nullable|string|max:255',
        ];

        $messages = [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.unique' => 'Este número de documento ya está registrado.',
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento debe ser C.C, T.I o PPT.',
            'tiene_discapacidad.in' => 'La opción de discapacidad no es válida.',
            'tipo_discapacidad.required_if' => 'Debe seleccionar el tipo de discapacidad.',
            'programa.required' => 'El programa es obligatorio.',
            'sede.required' => 'La sede es obligatoria.',
            'sede.in' => 'La sede seleccionada no es válida.',
            'estado_estudiante.required' => 'El estado del estudiante es obligatorio.',
            'estado_estudiante.in' => 'El estado del estudiante no es válido.',
            'horario.required' => 'El horario es obligatorio.',
            'talla_uniforme.in' => 'La talla del uniforme no es válida.',
            'semestre_actual.required' => 'El semestre actual es obligatorio.',
            'semestre_actual.in' => 'El semestre actual debe ser I, II o Ninguno (curso).',
            'anio.required' => 'El año es obligatorio.',
            'numero_grupo.required' => 'El número de grupo es obligatorio.',
            'numero_grupo.in' => 'El número de grupo debe ser 1A, 1B, 2A, 2B, 3A o 3B.',
        ];

        $request->validate($rules, $messages);

        // Generar código de estudiante automáticamente usando el número de documento
        $cod_alumno = $request->numero_documento;
        
        // Verificar que el código generado no exista ya
        $existeCodigo = Matricula::where('cod_alumno', $cod_alumno)->exists();
        if ($existeCodigo) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['numero_documento' => 'Este número de documento ya está registrado con otro código de estudiante.']);
        }

        // Buscar el primer ID disponible (reutilizar IDs eliminados) usando consulta SQL optimizada
        // Primero verificar si el ID 1 está disponible (caso más común)
        if (!Matricula::where('id', 1)->exists()) {
            $availableId = 1;
        } else {
            // Esta consulta encuentra el primer "gap" (hueco) en los IDs de forma eficiente
            // Busca el primer ID que no tiene un siguiente ID consecutivo
            $result = DB::selectOne("
                SELECT MIN(t1.id + 1) as available_id
                FROM matriculas t1
                LEFT JOIN matriculas t2 ON t1.id + 1 = t2.id
                WHERE t2.id IS NULL
                LIMIT 1
            ");
            
            // Si no hay gaps (todos los IDs están consecutivos), usar el siguiente ID disponible
            if ($result === null || $result->available_id === null) {
                $maxId = Matricula::max('id') ?? 0;
                $availableId = $maxId + 1;
            } else {
                $availableId = $result->available_id;
            }
        }

        // Agregar el código generado al request
        $request->merge(['cod_alumno' => $cod_alumno]);

        // Crear la matrícula con el ID específico usando inserción directa
        // Usar solo los campos fillable del modelo para evitar insertar campos no válidos
        $matricula = new Matricula();
        $fillable = $matricula->getFillable();
        
        // Preparar datos solo con campos fillable y excluir _token, _method
        $data = $request->only($fillable);
        $data['id'] = $availableId;
        $data['created_at'] = now();
        $data['updated_at'] = now();
        
        DB::table('matriculas')->insert($data);

        return redirect()->route('matricula.index')
            ->with('success', 'Ficha de matrícula creada exitosamente. Código de estudiante generado: ' . $cod_alumno);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($cod_alumno)
    {
        $matricula = Matricula::where('cod_alumno', $cod_alumno)->firstOrFail();
        
        // Obtener datos desde ajustes del sistema
        $programs = Program::where('active', true)->orderBy('name')->get();
        $schedules = Schedule::where('active', true)->orderBy('name')->get();
        $groups = Group::where('active', true)->orderBy('name')->get();
        
        // Intentar obtener datos del estudiante desde la base de datos mysql2 (opcional)
        $student = null;
        try {
            $studentData = DB::connection('mysql2')->select(
                'SELECT alumno.cod_alumno, alumno.nombre, alumno.cedula, programa.nombre_programa 
                 FROM alumno 
                 INNER JOIN relacion_programa_estudiante ON alumno.cod_alumno = relacion_programa_estudiante.Alumno_cod 
                 INNER JOIN programa ON relacion_programa_estudiante.programa_cod = programa.cod_programa 
                 WHERE cod_alumno = "'.$cod_alumno.'"'
            );
            if (!empty($studentData)) {
                $student = $studentData[0];
            }
        } catch (\Exception $e) {
            // Si no se puede conectar a mysql2, continuar sin esos datos
        }

        return view('matricula.ficha', [
            'matricula' => $matricula,
            'student' => $student,
            'cod_alumno' => $cod_alumno,
            'programs' => $programs,
            'schedules' => $schedules,
            'groups' => $groups,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $cod_alumno)
    {
        // Obtener valores válidos desde las tablas de ajustes
        $validPrograms = Program::where('active', true)->pluck('name')->toArray();
        $validSchedules = Schedule::where('active', true)->pluck('name')->toArray();
        $validGroups = Group::where('active', true)->pluck('name')->toArray();
        
        $rules = [
            'nombre_completo' => 'required|string|max:255',
            'numero_documento' => 'required|string|max:255|unique:matriculas,numero_documento,'.$cod_alumno.',cod_alumno',
            'tipo_documento' => 'required|in:CC,TI,PPT',
            'departamento' => 'nullable|string|max:255',
            'estado_civil' => 'nullable|string|max:255',
            'ocupacion' => 'nullable|string|max:255',
            'nivel_formacion' => 'nullable|string|max:255',
            'tiene_discapacidad' => 'nullable|in:No,Sí,Prefiero no decir',
            'programa' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($validPrograms) {
                if (!in_array($value, $validPrograms)) {
                    $fail('El programa seleccionado no es válido o no está activo.');
                }
            }],
            'sede' => 'required|in:Barrancabermeja,Aguachica,Virtual',
            'estado_estudiante' => 'required|in:Activo,Inactivo,Por Certificar,Certificado,Retirado,Suspendido,Todos',
            'horario' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($validSchedules) {
                if (!in_array($value, $validSchedules)) {
                    $fail('El horario seleccionado no es válido o no está activo.');
                }
            }],
            'talla_uniforme' => 'nullable|in:XS,S,M,L,XL,XXL,XXXL',
            'semestre_actual' => 'required|in:I,II,Ninguno (curso)',
            'anio' => 'required|string|max:255',
            'numero_grupo' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($validGroups) {
                if (!in_array($value, $validGroups)) {
                    $fail('El grupo seleccionado no es válido o no está activo.');
                }
            }],
            'contraseña_plataforma' => 'nullable|string|max:255',
            'tipo_discapacidad' => 'required_if:tiene_discapacidad,Sí|nullable|string|max:255',
        ];

        $messages = [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.unique' => 'Este número de documento ya está registrado.',
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento debe ser C.C, T.I o PPT.',
            'tiene_discapacidad.in' => 'La opción de discapacidad no es válida.',
            'tipo_discapacidad.required_if' => 'Debe seleccionar el tipo de discapacidad.',
            'programa.required' => 'El programa es obligatorio.',
            'sede.required' => 'La sede es obligatoria.',
            'sede.in' => 'La sede seleccionada no es válida.',
            'estado_estudiante.required' => 'El estado del estudiante es obligatorio.',
            'estado_estudiante.in' => 'El estado del estudiante no es válido.',
            'horario.required' => 'El horario es obligatorio.',
            'talla_uniforme.in' => 'La talla del uniforme no es válida.',
            'semestre_actual.required' => 'El semestre actual es obligatorio.',
            'semestre_actual.in' => 'El semestre actual debe ser I, II o Ninguno (curso).',
            'anio.required' => 'El año es obligatorio.',
            'numero_grupo.required' => 'El número de grupo es obligatorio.',
            'numero_grupo.in' => 'El número de grupo debe ser 1A, 1B, 2A, 2B, 3A o 3B.',
        ];

        $request->validate($rules, $messages);

        $matricula = Matricula::where('cod_alumno', $cod_alumno)->firstOrFail();
        $matricula->update($request->all());

        return redirect()->route('matricula.ficha', $cod_alumno)
            ->with('success', 'Ficha de matrícula actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $cod_alumno
     * @return \Illuminate\Http\Response
     */
    public function destroy($cod_alumno, Request $request)
    {
        $matricula = Matricula::where('cod_alumno', $cod_alumno)->firstOrFail();
        
        // Verificar si hay datos relacionados
        $cost = Cost::where('cod_alumno', $cod_alumno)->first();
        $entriesCount = 0;
        $otherEntriesCount = 0;
        $pursesCount = 0;
        
        if ($cost) {
            $entriesCount = Entry::where('id_cost', $cost->id)->count();
            $otherEntriesCount = OtherEntry::where('id_cost', $cost->id)->count();
            $pursesCount = Purse::where('id_cost', $cost->id)->count();
        }
        
        $totalRelaciones = $entriesCount + $otherEntriesCount + $pursesCount;
        
        // Si hay relaciones y no se confirmó la eliminación en cascada
        if ($totalRelaciones > 0 && !$request->has('confirmar_cascada')) {
            return redirect()->route('matricula.index')
                ->with('warning', "No se puede eliminar la matrícula porque tiene datos relacionados: {$entriesCount} abonos, {$otherEntriesCount} otros ingresos, {$pursesCount} cuotas. Use la opción de eliminación en cascada si desea eliminar todo.")
                ->with('cod_alumno_pendiente', $cod_alumno)
                ->with('relaciones', [
                    'entries' => $entriesCount,
                    'other_entries' => $otherEntriesCount,
                    'purses' => $pursesCount
                ]);
        }
        
        // Si se confirmó la eliminación en cascada o no hay relaciones
        if ($totalRelaciones > 0 && $request->has('confirmar_cascada')) {
            // Eliminar en cascada: entries, other_entries, purses, cost, matricula
            if ($cost) {
                // Eliminar entries
                $entries = Entry::where('id_cost', $cost->id)->get();
                foreach ($entries as $entry) {
                    \App\Http\Controllers\TableChangeController::StoreDelete('entries', $entry->id);
                    $entry->delete();
                }
                
                // Eliminar other_entries
                $otherEntries = OtherEntry::where('id_cost', $cost->id)->get();
                foreach ($otherEntries as $otherEntry) {
                    \App\Http\Controllers\TableChangeController::StoreDelete('other_entries', $otherEntry->id);
                    $otherEntry->delete();
                }
                
                // Obtener todos los purses que se van a eliminar
                $pursesToDelete = Purse::where('id_cost', $cost->id)->get();
                
                // Eliminar primero los history_purses relacionados para evitar violación de clave foránea
                if($pursesToDelete->count() > 0) {
                    $purseIds = $pursesToDelete->pluck('id')->toArray();
                    historyPurse::whereIn('id_purse', $purseIds)->delete();
                }
                
                // Ahora eliminar los purses
                Purse::where('id_cost', $cost->id)->delete();
                
                // Eliminar cost
                \App\Http\Controllers\TableChangeController::StoreDelete('costs', $cost->id);
                $cost->delete();
            }
        }
        
        // Eliminar matrícula
        $matricula->delete();

        $mensaje = $totalRelaciones > 0 
            ? "Matrícula y todos sus datos relacionados ({$entriesCount} abonos, {$otherEntriesCount} otros ingresos, {$pursesCount} cuotas) eliminados exitosamente."
            : "Ficha de matrícula eliminada exitosamente.";

        return redirect()->route('matricula.index')
            ->with('success', $mensaje);
    }

    /**
     * Show the student view using matricula data
     *
     * @param  string  $cod_alumno
     * @return \Illuminate\Http\Response
     */
    public function showMatricula($cod_alumno)
    {
        $matricula = Matricula::where('cod_alumno', $cod_alumno)->firstOrFail();
        
        // Obtener todos los registros de costos del estudiante
        $Costs = DB::table('costs')->where('cod_alumno', $cod_alumno)->orderBy('numero_semestre', 'asc')->get();

        if($Costs->isEmpty()){
            $emptyCost = [
                "id"=> '',
                "cod_alumno"=> $cod_alumno,
                "valor_semestre" => '',
                "numero_semestre"=> 1,
                "valor_total_semestre"=> '',
                "descuento"=> '',
                "valor_neto"=> '',
                "saldo_financiar"=> '',
                "periodo"=> 'Mensual',
                "numero_cuotas"=> '',
                "valor_cuotas"=> '',
                'fecha_pago'=> '',
                "created_at"=> '',
                "updated_at"=> ''
            ];
            $Costs = collect([json_decode(json_encode($emptyCost))]);
            $Cost = $Costs[0];
        }else{
            foreach($Costs as $c) {
                MoneyController::datas($c, ['valor_semestre','valor_total_semestre','descuento','valor_neto','saldo_financiar','valor_cuotas']);
            }
            $Cost = $Costs[0]; // Para compatibilidad con lógica existente
        }
        
        $con = consecutive::where('type','entry')->first();
        $Entries = EntryController::getEntry($cod_alumno,true);
        $OtherEntries = OtherEntryController::getOtherEntry($cod_alumno,true);

        if(empty($con) == true){
            return redirect()->route('matricula.index')->with('warning','No existe consecutivo disponible.');
        }else{
            $conceptos = concepto::all();
            $elaborado = elaborado::getUnique();
            $haber = haber::getUnique();
            $debe = debe::getUnique();
            $otrosConceptos = otrosConcepto::all();
            $sql_conseOcupados = 'SELECT entries.no_recibo FROM entries ORDER BY entries.no_recibo ASC';
            $ConsecutivosOcupados = DB::connection('mysql')->select($sql_conseOcupados);
            if($Cost->id != ""){
                $Purses = PurseController::getPurse($Cost->id);
            }else{
                $Purses = "";
            }
            
            // Preparar datos del estudiante desde matrícula
            $student = [
                (object)[
                    'cod_alumno' => $matricula->cod_alumno,
                    'nombre' => $matricula->nombre_completo,
                    'cedula' => $matricula->numero_documento,
                    'nombre_programa' => $matricula->programa ?? 'Sin programa',
                    'estado' => $matricula->estado_estudiante ?? 'Sin estado',
                    'foto' => '' // No hay foto en matrícula, usar placeholder
                ]
            ];
            
            return view('matricula.show',[
                'Purses' => $Purses,
                'otrosConceptos' => $otrosConceptos,
                'OtherEntries' => $OtherEntries,
                'ConsecutivosOcupados' => $ConsecutivosOcupados,
                'student' => $student,
                'cost' => $Cost,
                'costs' => $Costs, // Pasar todos los costos
                'con' => $con,
                'entry' => $Entries,
                'conceptos' => $conceptos,
                'elaborados'=> $elaborado,
                'haber' =>$haber,
                'debe' => $debe,
                'matricula' => $matricula
            ]);
        }
    }

    /**
     * Download the enrollment form as PDF
     *
     * @param  string  $cod_alumno
     * @return \Illuminate\Http\Response
     */
    public function downloadFicha($cod_alumno)
    {
        $matricula = Matricula::where('cod_alumno', $cod_alumno)->firstOrFail();
        
        // Obtener configuración de la institución
        $institucion = InstitutionSetting::getSettings();
        
        // Obtener datos financieros (costs)
        $cost = DB::table('costs')->where('cod_alumno', $cod_alumno)->first();
        if($cost) {
            $cost = MoneyController::datas($cost,['valor_semestre','valor_total_semestre','descuento','valor_neto','saldo_financiar','valor_cuotas']);
            
            // Obtener datos de cartera usando CarteraService
            $carteraData = null;
            try {
                $carteraData = \App\Services\CarteraService::calcularCartera($cost->id);
            } catch (\Exception $e) {
                \Log::error('Error calculando cartera para PDF: ' . $e->getMessage());
            }
        } else {
            $carteraData = null;
        }
        
        // Preparar ruta de foto (convertir a base64 para PDF)
        $photoBase64 = null;
        if ($matricula->photo_path && Storage::disk('public')->exists($matricula->photo_path)) {
            try {
                $photoData = Storage::disk('public')->get($matricula->photo_path);
                $extension = pathinfo($matricula->photo_path, PATHINFO_EXTENSION);
                $mimeType = 'image/' . ($extension == 'jpg' ? 'jpeg' : $extension);
                $photoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($photoData);
            } catch (\Exception $e) {
                \Log::error('Error cargando foto para PDF: ' . $e->getMessage());
            }
        }
        
        // Usar imagen estática del QR code
        $qrCodeBase64 = null;
        $qrImagePath = public_path('images/qr-code.png');
        
        // Verificar si existe la imagen del QR
        if (file_exists($qrImagePath)) {
            try {
                $qrImageData = file_get_contents($qrImagePath);
                $extension = pathinfo($qrImagePath, PATHINFO_EXTENSION);
                $mimeType = 'image/' . ($extension == 'jpg' ? 'jpeg' : $extension);
                $qrCodeBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($qrImageData);
            } catch (\Exception $e) {
                \Log::error('Error cargando imagen QR: ' . $e->getMessage());
            }
        } else {
            \Log::warning('Imagen QR no encontrada en: ' . $qrImagePath);
        }
        
        // Generar el PDF
        $dompdf = new Dompdf();
        $html = view('matricula.ficha-pdf', [
            'matricula' => $matricula,
            'institucion' => $institucion,
            'cost' => $cost,
            'carteraData' => $carteraData ?? null,
            'photoBase64' => $photoBase64,
            'qrCodeBase64' => $qrCodeBase64,
            'hideDefaultFooter' => true // Ocultar nota y footer por defecto
        ])->render();
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Nombre del archivo
        $nombreEstudiante = str_replace(' ', '-', $matricula->nombre_completo ?? 'estudiante');
        $nombreEstudiante = preg_replace('/[^A-Za-z0-9\-]/', '', $nombreEstudiante);
        
        // Enviar el PDF al navegador
        return $dompdf->stream('ficha-matricula-' . $nombreEstudiante . '.pdf', [
            'Attachment' => true
        ]);
    }

    /**
     * View the enrollment form as PDF in browser (without downloading)
     *
     * @param  string  $cod_alumno
     * @return \Illuminate\Http\Response
     */
    public function viewFicha($cod_alumno)
    {
        $matricula = Matricula::where('cod_alumno', $cod_alumno)->firstOrFail();
        
        // Obtener configuración de la institución
        $institucion = InstitutionSetting::getSettings();
        
        // Obtener datos financieros (costs)
        $cost = DB::table('costs')->where('cod_alumno', $cod_alumno)->first();
        if($cost) {
            $cost = MoneyController::datas($cost,['valor_semestre','valor_total_semestre','descuento','valor_neto','saldo_financiar','valor_cuotas']);
            
            // Obtener datos de cartera usando CarteraService
            $carteraData = null;
            try {
                $carteraData = \App\Services\CarteraService::calcularCartera($cost->id);
            } catch (\Exception $e) {
                \Log::error('Error calculando cartera para PDF: ' . $e->getMessage());
            }
        } else {
            $carteraData = null;
        }
        
        // Preparar ruta de foto (convertir a base64 para PDF)
        $photoBase64 = null;
        if ($matricula->photo_path && Storage::disk('public')->exists($matricula->photo_path)) {
            try {
                $photoData = Storage::disk('public')->get($matricula->photo_path);
                $extension = pathinfo($matricula->photo_path, PATHINFO_EXTENSION);
                $mimeType = 'image/' . ($extension == 'jpg' ? 'jpeg' : $extension);
                $photoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($photoData);
            } catch (\Exception $e) {
                \Log::error('Error cargando foto para PDF: ' . $e->getMessage());
            }
        }
        
        // Usar imagen estática del QR code
        $qrCodeBase64 = null;
        $qrImagePath = public_path('images/qr-code.png');
        
        // Verificar si existe la imagen del QR
        if (file_exists($qrImagePath)) {
            try {
                $qrImageData = file_get_contents($qrImagePath);
                $extension = pathinfo($qrImagePath, PATHINFO_EXTENSION);
                $mimeType = 'image/' . ($extension == 'jpg' ? 'jpeg' : $extension);
                $qrCodeBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($qrImageData);
            } catch (\Exception $e) {
                \Log::error('Error cargando imagen QR: ' . $e->getMessage());
            }
        } else {
            \Log::warning('Imagen QR no encontrada en: ' . $qrImagePath);
        }
        
        // Generar el PDF
        $dompdf = new Dompdf();
        $html = view('matricula.ficha-pdf', [
            'matricula' => $matricula,
            'institucion' => $institucion,
            'cost' => $cost,
            'carteraData' => $carteraData ?? null,
            'photoBase64' => $photoBase64,
            'qrCodeBase64' => $qrCodeBase64,
            'hideDefaultFooter' => true // Ocultar nota y footer por defecto
        ])->render();
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Nombre del archivo
        $nombreEstudiante = str_replace(' ', '-', $matricula->nombre_completo ?? 'estudiante');
        $nombreEstudiante = preg_replace('/[^A-Za-z0-9\-]/', '', $nombreEstudiante);
        
        // Mostrar el PDF en el navegador sin descargar (Attachment => false)
        return $dompdf->stream('ficha-matricula-' . $nombreEstudiante . '.pdf', [
            'Attachment' => false
        ]);
    }

    /**
     * Upload student photo
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $cod_alumno
     * @return \Illuminate\Http\Response
     */
    public function uploadPhoto(Request $request, $cod_alumno)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $matricula = Matricula::where('cod_alumno', $cod_alumno)->firstOrFail();

        // Eliminar foto anterior si existe
        if ($matricula->photo_path && Storage::disk('public')->exists($matricula->photo_path)) {
            Storage::disk('public')->delete($matricula->photo_path);
        }

        // Guardar nueva foto
        $photo = $request->file('photo');
        $filename = 'students/' . $cod_alumno . '_' . time() . '.' . $photo->getClientOriginalExtension();
        $photo->storeAs('public', $filename);

        $matricula->photo_path = $filename;
        $matricula->save();

        // Usar config('app.url') para asegurar URL absoluta correcta en producción
        $baseUrl = rtrim(config('app.url'), '/');
        $photoUrl = $baseUrl . '/api/v1/matriculas/' . $cod_alumno . '/foto';
        
        return response()->json([
            'success' => true,
            'message' => 'Foto subida exitosamente',
            'photo_url' => $photoUrl
        ]);
    }

    /**
     * Delete student photo
     *
     * @param  string  $cod_alumno
     * @return \Illuminate\Http\Response
     */
    public function deletePhoto($cod_alumno)
    {
        $matricula = Matricula::where('cod_alumno', $cod_alumno)->firstOrFail();

        if ($matricula->photo_path && Storage::disk('public')->exists($matricula->photo_path)) {
            Storage::disk('public')->delete($matricula->photo_path);
        }

        $matricula->photo_path = null;
        $matricula->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto eliminada exitosamente'
        ]);
    }
}
