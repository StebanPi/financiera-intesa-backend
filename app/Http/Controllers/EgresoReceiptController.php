<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EgresoReceipt;
use App\Models\EgresoProvider;
use App\Models\EgresoConcept;
use App\Models\consecutive;
use App\Models\debe;
use App\Models\haber;
use App\Models\elaborado;

class EgresoReceiptController extends Controller
{
    public function index()
    {
        $receipts = EgresoReceipt::with('provider')->orderBy('fecha_recibo', 'desc')->get();
        $con = consecutive::where('type', 'discharge')->first();
        return view('egresos.receipts.index', [
            'receipts' => $receipts,
            'consecutive' => $con
        ]);
    }

    public function create()
    {
        $providers = EgresoProvider::orderBy('nombre')->get();
        $concepts = EgresoConcept::with('debeObject', 'haberObject')->orderBy('nombre')->get();
        // Obtener datos únicos para evitar duplicados
        $debe = debe::getUnique();
        $haber = haber::getUnique();
        $elaborados = elaborado::getUnique();
        $con = consecutive::where('type', 'discharge')->first();
        
        // Preparar conceptos con debe y haber para JavaScript
        $conceptsData = $concepts->map(function($concept) {
            return [
                'id' => $concept->id,
                'nombre' => $concept->nombre,
                'debe' => $concept->debe,
                'haber' => $concept->haber
            ];
        });
        
        return view('egresos.receipts.create', [
            'providers' => $providers,
            'concepts' => $concepts,
            'conceptsData' => $conceptsData,
            'debe' => $debe,
            'haber' => $haber,
            'elaborados' => $elaborados,
            'consecutive' => $con
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_recibo' => 'required|date',
            'proveedor_id' => 'required|exists:egreso_providers,id',
            'forma' => 'required|in:Efectivo,Bancos',
            'concepto' => 'required|exists:egreso_concepts,id', // Cambiar de concepto_id a concepto
            'descripcion' => 'nullable|string',
            'valor' => 'required|numeric|min:0',
            'debe' => 'required|exists:debes,id',
            'haber' => 'required|exists:habers,id',
            'elaborado_por' => 'required|exists:elaborados,id'
        ]);

        // Obtener el concepto con su debe y haber
        $concept = EgresoConcept::findOrFail($request->concepto);
        
        // Usar el debe y haber del concepto configurado
        if (!$concept->debe || !$concept->haber) {
            return redirect()->back()->with('error', 'El concepto seleccionado no tiene debe y haber configurados. Por favor, configure estos campos en el concepto.');
        }

        if (isset($request->id) && $request->id != null) {
            $receipt = EgresoReceipt::findOrFail($request->id);
            $receipt->update([
                'fecha_recibo' => $request->fecha_recibo,
                'proveedor_id' => $request->proveedor_id,
                'forma' => $request->forma,
                'concepto' => $concept->id, // Guardar ID del concepto
                'descripcion' => $request->descripcion,
                'valor' => str_replace(".", "", $request->valor),
                'debe' => $concept->debe, // Usar debe del concepto
                'haber' => $concept->haber, // Usar haber del concepto
                'elaborado_por' => $request->elaborado_por
            ]);
            return redirect()->route('egreso.receipts.edit', $receipt->no_recibo)->with('success', 'Recibo guardado correctamente');
        } else {
            // Obtener siguiente consecutivo
            $con = consecutive::where('type', 'discharge')->first();
            if (!$con) {
                return redirect()->back()->with('error', 'No hay consecutivo configurado para egresos');
            }

            $noRecibo = $con->num_current;
            $receipt = EgresoReceipt::create([
                'no_recibo' => $noRecibo,
                'fecha_recibo' => $request->fecha_recibo,
                'proveedor_id' => $request->proveedor_id,
                'forma' => $request->forma,
                'concepto' => $concept->id, // Guardar ID del concepto
                'descripcion' => $request->descripcion,
                'valor' => str_replace(".", "", $request->valor),
                'debe' => $concept->debe, // Usar debe del concepto
                'haber' => $concept->haber, // Usar haber del concepto
                'elaborado_por' => $request->elaborado_por
            ]);

            // Incrementar consecutivo
            $con->num_current = intval($con->num_current) + 1;
            $con->save();

            return redirect()->route('egreso.receipts.index')->with('success', 'Recibo de egreso realizado correctamente');
        }
    }

    public function edit($noRecibo)
    {
        $receipt = EgresoReceipt::where('no_recibo', $noRecibo)->with('provider')->firstOrFail();
        $providers = EgresoProvider::orderBy('nombre')->get();
        $concepts = EgresoConcept::with('debeObject', 'haberObject')->orderBy('nombre')->get();
        // Obtener datos únicos para evitar duplicados
        $debe = debe::getUnique();
        $haber = haber::getUnique();
        $elaborados = elaborado::getUnique();

        return view('egresos.receipts.edit', [
            'receipt' => $receipt,
            'providers' => $providers,
            'concepts' => $concepts,
            'debe' => $debe,
            'haber' => $haber,
            'elaborados' => $elaborados
        ]);
    }

    public function print($id)
    {
        $receipt = EgresoReceipt::where('id', $id)->with(['provider', 'conceptoObject'])->firstOrFail();
        
        // Obtener nombre del concepto
        $conceptoNombre = null;
        if ($receipt->conceptoObject) {
            $conceptoNombre = $receipt->conceptoObject->nombre ?? null;
        } elseif ($receipt->concepto) {
            // Fallback: obtener desde la base de datos directamente
            try {
                $concepto = EgresoConcept::find($receipt->concepto);
                $conceptoNombre = $concepto->nombre ?? null;
            } catch (\Exception $e) {
                \Log::error('Error al obtener concepto: ' . $e->getMessage());
            }
        }
        
        // Formatear fecha según el formato esperado (ej: 03-may.-25)
        $fechaFormateada = '';
        if ($receipt->fecha_recibo) {
            // Extraer solo la parte de la fecha (antes del espacio si existe hora)
            $fechaStr = explode(' ', $receipt->fecha_recibo)[0];
            $fechaParts = explode('-', $fechaStr);
            if (count($fechaParts) >= 3) {
                $meses = [
                    '01' => 'ene', '02' => 'feb', '03' => 'mar', '04' => 'abr',
                    '05' => 'may', '06' => 'jun', '07' => 'jul', '08' => 'ago',
                    '09' => 'sep', '10' => 'oct', '11' => 'nov', '12' => 'dic'
                ];
                $dia = $fechaParts[2] ?? '';
                $mes = $meses[$fechaParts[1] ?? ''] ?? $fechaParts[1] ?? '';
                $anio = substr($fechaParts[0] ?? '', -2);
                $fechaFormateada = $dia . '-' . $mes . '.-' . $anio;
            } else {
                $fechaFormateada = $fechaStr;
            }
        }
        
        return view('prints.egreso-pos', [
            'consecutivo' => $receipt->no_recibo ?? $receipt->id, // Usar no_recibo como consecutivo
            'proveedor_nombre' => $receipt->provider->nombre ?? null,
            'concepto' => $conceptoNombre,
            'descripcion' => $receipt->descripcion ?? null,
            'forma' => $receipt->forma ?? null,
            'valor' => $receipt->valor ?? null,
            'fecha' => $fechaFormateada,
        ]);
    }

    public function destroy($noRecibo)
    {
        // Solo permitir eliminación a admin y super-admin
        if (!auth()->check() || (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super-admin'))) {
            return redirect()->route('egreso.receipts.index')->with('error', 'No tienes permisos para eliminar recibos de egreso.');
        }
        
        try {
            $receipt = EgresoReceipt::where('no_recibo', $noRecibo)->firstOrFail();
            $receipt->delete();
            
            return redirect()->route('egreso.receipts.index')->with('success', 'Recibo de egreso eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('egreso.receipts.index')->with('error', 'Error al eliminar el recibo de egreso: ' . $e->getMessage());
        }
    }
}
