<?php

namespace App\Http\Controllers;
use App\Models\ThirdReceipts;
use App\Models\consecutive;
use Illuminate\Http\Request;

class ThirdReceiptsController extends Controller
{
    /**
     * Display a listing of third party entry receipts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Solo obtener recibos de tipo 'entry' (ingreso) para la sección de terceros
        $receipts = ThirdReceipts::where('type', 'entry')
            ->with(['thirdObject', 'debeObject', 'haberObject', 'elaboradoObject', 'conceptoObject'])
            ->orderByRaw('COALESCE(fecha_recibo, created_at) DESC')
            ->orderBy('no_recibo', 'desc')
            ->get();
        
        return view('third.receipts.index', compact('receipts'));
    }

    public function store(Request $request){

        if(isset($request->id) && $request->id != null){

            $receipt = ThirdReceipts::where('id', $request->id)->first();
            $receipt->third = $request->third;
            $receipt->concepto = $request->concepto;
            $receipt->detalles = $request->detalles;
            $receipt->valor = str_replace(".","",$request->valor);
            $receipt->debe = $request->debe;
            $receipt->haber = $request->haber;
            $receipt->elaborado_por = $request->elaborado_por;
            $receipt->forma = $request->forma ?? 'Efectivo';
            if (isset($request->fecha_recibo)) {
                $receipt->fecha_recibo = $request->fecha_recibo;
            }
            $receipt->save();
            return redirect()->route('third.receipts.'.$request->type.'.edit', $receipt->no_recibo)->with('success','Recibo guardado Correctamente');

        }else{
            // Normalizar forma de pago: Consignación -> Bancos
            $forma = $request->forma ?? 'Efectivo';
            if ($forma === 'Consignación') {
                $forma = 'Bancos';
            }

            $receipt = ThirdReceipts::create([
                'no_recibo' => $request->no_recibo,
                'type' => $request->type,
                'third' => $request->third,
                'concepto' => $request->concepto,
                'detalles' => $request->detalles,
                'valor' => str_replace(".","",$request->valor),
                'debe' => $request->debe,
                'haber' => $request->haber,
                'elaborado_por' => $request->elaborado_por,
                'forma' => $forma,
                'fecha_recibo' => $request->fecha_recibo ?? now()->format('Y-m-d')
            ]);
    
            // Si es tipo 'entry', usa el consecutivo compartido de ingresos
            // Si es tipo 'discharge', usa su propio consecutivo (pero no se usa para reportes de egresos)
            $conType = $request->type === 'entry' ? 'entry' : 'discharge';
            $con = consecutive::where('type', $conType)->first();
            if ($con) {
                $con->num_current = intval($con->num_current) + 1;
                $con->save();
            }
    
            // Redirigir según el tipo, pero si es discharge, redirigir a home (ruta deshabilitada)
            if($request->type === 'discharge'){
                return redirect()->route('home')->with('warning','La ruta de recibos de egreso está deshabilitada');
            }
            return redirect()->route('third.receipts.'.$request->type)->with('success','Recibo realizado Correctamente');
        }
    }

    public function redireccionarEntry($id){
        $count = ThirdReceipts::where('no_recibo', $id)->count();
        if($count == 0){
            return redirect()->route('third.receipts.entry');
        }else{
            $content = ThirdReceipts::where('no_recibo', $id)->where('type', 'entry')->first()->load('thirdObject','debeObject', 'haberObject', 'elaboradoObject');
            return view('third.thirdEntryReceiptsEdit', ['id' => $id , 'content' => $content]);
        }
    }

    public function redireccionarDischarge($id){
        // Ruta deshabilitada - redirigir a home
        return redirect()->route('home');
        
        // Código original comentado
        // $count = ThirdReceipts::where('no_recibo', $id)->count();
        // 
        // if($count == 0){
        //     return redirect()->route('third.receipts.discharge');
        // }else{
        //     $content = ThirdReceipts::where('no_recibo', $id)->where('type', 'discharge')->first()->load('thirdObject','debeObject', 'haberObject', 'elaboradoObject');
        //     return view('third.thirdDischargeReceiptsEdit', ['id' => $id , 'content' => $content]);
        // }
    }

    /**
     * Remove the specified receipt from storage.
     * Solo permite eliminar recibos de tipo 'entry' (ingreso) en la sección de terceros.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function print($id)
    {
        $receipt = ThirdReceipts::where('id', $id)
            ->with(['thirdObject', 'conceptoObject'])
            ->firstOrFail();
        
        // Obtener configuración de institución
        $institution = \App\Models\InstitutionSetting::getSettings();
        
        // Determinar el tipo de recibo
        $tipoRecibo = $receipt->type === 'entry' ? 'REGISTRO DE INGRESO DE TERCEROS' : 'REGISTRO DE EGRESO DE TERCEROS';
        
        // Obtener nombre del concepto
        $conceptoNombre = null;
        if ($receipt->conceptoObject) {
            $conceptoNombre = $receipt->conceptoObject->name ?? null;
        } elseif ($receipt->concepto) {
            // Fallback: obtener desde la base de datos directamente
            try {
                $concepto = \App\Models\ConceptEntryReceipt::find($receipt->concepto);
                $conceptoNombre = $concepto->name ?? null;
            } catch (\Exception $e) {
                \Log::error('Error al obtener concepto: ' . $e->getMessage());
            }
        }
        
        // Obtener nombre del tercero
        $terceroNombre = null;
        if ($receipt->thirdObject) {
            $terceroNombre = $receipt->thirdObject->nombre ?? null;
        } elseif ($receipt->third) {
            try {
                $tercero = \App\Models\thirdEntry::find($receipt->third);
                $terceroNombre = $tercero->nombre ?? null;
            } catch (\Exception $e) {
                \Log::error('Error al obtener tercero: ' . $e->getMessage());
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
        
        return view('prints.third-receipt-pos', [
            'tipo_recibo' => $tipoRecibo,
            'consecutivo' => $receipt->no_recibo ?? $receipt->id, // Usar no_recibo como consecutivo
            'tercero_nombre' => $terceroNombre,
            'concepto' => $conceptoNombre,
            'detalles' => $receipt->detalles ?? null,
            'forma' => $receipt->forma ?? null,
            'valor' => $receipt->valor ?? null,
            'fecha' => $fechaFormateada,
        ]);
    }

    public function destroy($id)
    {
        // Solo permitir eliminación a admin y super-admin
        if (!auth()->check() || (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super-admin'))) {
            return redirect()->route('third.receipts.index')->with('error', 'No tienes permisos para eliminar recibos de terceros.');
        }
        
        try {
            // Solo permitir eliminar recibos de tipo 'entry' (ingreso) en la sección de terceros
            $receipt = ThirdReceipts::where('id', $id)
                ->where('type', 'entry')
                ->first();
            
            if ($receipt) {
                $receipt->delete();
                return redirect()->route('third.receipts.index')->with('success','Recibo eliminado correctamente');
            }
            return redirect()->route('third.receipts.index')->with('error','Recibo no encontrado o no es un recibo de ingreso');
        } catch (\Exception $e) {
            return redirect()->route('third.receipts.index')->with('error','Error al eliminar el recibo: ' . $e->getMessage());
        }
    }
}
