<?php

namespace App\Http\Controllers;
use App\Models\thirdEntry;
use App\Models\haber;
use App\Models\debe;
use App\Models\consecutive;
use App\Models\thirdActivity;
use App\Models\ConceptEntryReceipt;
use Illuminate\Http\Request;

class thirdEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $listActivity = thirdActivity::orderBy('created_at', 'desc')->get();
        $list = thirdEntry::orderBy('created_at', 'desc')->get();
        $haber = haber::getUnique();
        $debe = debe::getUnique();
        $con = consecutive::where('type','entry')->first();
        $conceptsEntry = ConceptEntryReceipt::with('debeObject', 'haberObject')->orderBy('created_at', 'desc')->get();
        
        return view('third.home', ['thirdEntry' => $list->load('thirdActivity'),        
        'haber' => $haber, 
        'debe' => $debe, 
        'thirdActivity' => $listActivity,
        'consecutive' => $con,
        'conceptsEntry' => $conceptsEntry ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $third = thirdEntry::create($request->all());
        return redirect()->route('third.entry')->with('success','Agregado Correctamente');
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
    public function edit($id)
    {
        $listActivity = thirdActivity::orderBy('created_at', 'desc')->get();
        $list = thirdEntry::orderBy('created_at', 'desc')->get();
        $item = thirdEntry::where('id', $id)->first();

        return view('third.edit', ['thirdEntry' => $list->load('thirdActivity'), 
        'third' => $item, 
        'thirdActivity' => $listActivity]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $item = thirdEntry::where('id', $id)->first();
        $item->cedula = $request->cedula;
        $item->nombre = $request->nombre;
        $item->direccion = $request->direccion;
        $item->telefono = $request->telefono;
        $item->actividad = $request->actividad;
        $item->mas = $request->mas;
        $item->save();
        return redirect()->route('third.entry')->with('success','Editado Correctamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $item = thirdEntry::where('id', $id)->first();
            if ($item) {
                // Verificar si hay recibos asociados a este tercero
                $tieneRecibos = \App\Models\ThirdReceipts::where('third', $id)->exists();
                if ($tieneRecibos) {
                    return redirect()->route('third.entry')->with('error','No se puede eliminar el tercero porque tiene recibos asociados.');
                }
                $item->delete();
                return redirect()->route('third.entry')->with('success','Tercero eliminado correctamente');
            }
            return redirect()->route('third.entry')->with('error','Tercero no encontrado');
        } catch (\Exception $e) {
            return redirect()->route('third.entry')->with('error','Error al eliminar el tercero: ' . $e->getMessage());
        }
    }

    public function search($name){
        $list = thirdEntry::where('nombre', 'like', '%'.$name.'%')->get();
        return json_encode($list);
    }
}
