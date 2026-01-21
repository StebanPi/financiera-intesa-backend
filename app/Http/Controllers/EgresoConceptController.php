<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EgresoConcept;

class EgresoConceptController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'state' => 'boolean',
            'debe' => 'required|exists:debes,id',
            'haber' => 'required|exists:habers,id'
        ]);

        $data = $request->all();
        $data['state'] = $request->has('state') ? true : false;
        EgresoConcept::create($data);
        return redirect()->route('egreso.providers.index')->with('success', 'Concepto agregado correctamente');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'state' => 'boolean',
            'debe' => 'required|exists:debes,id',
            'haber' => 'required|exists:habers,id'
        ]);

        $concept = EgresoConcept::findOrFail($id);
        $data = $request->all();
        $data['state'] = $request->has('state') ? true : false;
        $concept->update($data);
        return redirect()->route('egreso.providers.index')->with('success', 'Concepto actualizado correctamente');
    }

    public function destroy($id)
    {
        $concept = EgresoConcept::findOrFail($id);
        $concept->delete();
        return redirect()->route('egreso.providers.index')->with('success', 'Concepto eliminado correctamente');
    }
}
