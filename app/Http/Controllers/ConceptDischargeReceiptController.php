<?php

namespace App\Http\Controllers;

use App\Models\ConceptDischargeReceipt;
use App\Models\debe;
use App\Models\haber;
use Illuminate\Http\Request;

class ConceptDischargeReceiptController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'debe' => 'required|exists:debes,id',
            'haber' => 'required|exists:habers,id',
            'state' => 'nullable'
        ]);

        ConceptDischargeReceipt::create([
            'name' => $request->name,
            'debe' => $request->debe,
            'haber' => $request->haber,
            'state' => $request->has('state') ? true : false
        ]);

        return redirect()->route('third.entry')->with('success', 'Concepto de egreso agregado correctamente');
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
        $request->validate([
            'name' => 'required|string|max:255',
            'debe' => 'required|exists:debes,id',
            'haber' => 'required|exists:habers,id',
            'state' => 'nullable'
        ]);

        $concept = ConceptDischargeReceipt::findOrFail($id);
        $concept->update([
            'name' => $request->name,
            'debe' => $request->debe,
            'haber' => $request->haber,
            'state' => $request->has('state') ? true : false
        ]);

        return redirect()->route('third.entry')->with('success', 'Concepto de egreso actualizado correctamente');
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
            $concept = ConceptDischargeReceipt::findOrFail($id);
            $concept->delete();

            return redirect()->route('third.entry')->with('success', 'Concepto de egreso eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('third.entry')->with('error', 'Error al eliminar el concepto de egreso');
        }
    }
}
