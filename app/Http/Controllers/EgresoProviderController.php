<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EgresoProvider;
use App\Models\EgresoConcept;
use App\Models\debe;
use App\Models\haber;

class EgresoProviderController extends Controller
{
    public function index()
    {
        $providers = EgresoProvider::orderBy('nombre')->get();
        $concepts = EgresoConcept::with('debeObject', 'haberObject')->orderBy('nombre')->get();
        $debe = debe::getUnique();
        $haber = haber::getUnique();
        return view('egresos.providers.index', compact('providers', 'concepts', 'debe', 'haber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cedula' => 'nullable|string',
            'nombre' => 'required|string',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string'
        ]);

        EgresoProvider::create($request->all());
        return redirect()->route('egreso.providers.index')->with('success', 'Proveedor agregado correctamente');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cedula' => 'nullable|string',
            'nombre' => 'required|string',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string'
        ]);

        $provider = EgresoProvider::findOrFail($id);
        $provider->update($request->all());
        return redirect()->route('egreso.providers.index')->with('success', 'Proveedor actualizado correctamente');
    }

    public function destroy($id)
    {
        $provider = EgresoProvider::findOrFail($id);
        $provider->delete();
        return redirect()->route('egreso.providers.index')->with('success', 'Proveedor eliminado correctamente');
    }
}
