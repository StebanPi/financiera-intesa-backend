<?php

namespace App\Http\Livewire\Purses;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OtrosIngresos extends Component
{
    public $id_cost,$entries,$cost;

    public function mount(){
        $this->cost = DB::table('costs')->where('id',$this->id_cost)->get();
        $this->entries = DB::connection('mysql')->select('SELECT other_entries.id, other_entries.id_cost, otros_conceptos.nombre AS concepto, other_entries.descripcion, other_entries.no_recibo, other_entries.fecha_recibo, other_entries.valor,elaborados.nombre AS elaborado_por, CONCAT(debes.cuenta, " - ", debes.nombre) AS debe , CONCAT(habers.cuenta, " - ", habers.nombre) AS haber, other_entries.created_at FROM other_entries INNER JOIN otros_conceptos ON otros_conceptos.id = other_entries.concepto INNER JOIN elaborados ON elaborados.id = other_entries.elaborado_por INNER JOIN debes ON debes.id = other_entries.debe INNER JOIN habers ON habers.id = other_entries.haber WHERE other_entries.id_cost ="'.$this->id_cost.'" ORDER BY other_entries.no_recibo ASC');
    }

    public function render()
    {
        return view('livewire.purses.otros-ingresos');
    }
}
