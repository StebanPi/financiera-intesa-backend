<?php

namespace App\Http\Livewire\Purses;
use App\Models\Entry;
use App\Models\Cost;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Abonos extends Component
{
    public $id_cost,$entries,$cost;

    public function mount(){
        $this->cost = DB::table('costs')->where('id',$this->id_cost)->get();
        $this->entries = DB::connection('mysql')->select('SELECT entries.id, entries.id_cost, conceptos.nombre AS concepto, entries.descripcion, entries.no_recibo, entries.fecha_recibo, entries.valor,elaborados.nombre AS elaborado_por, CONCAT(debes.cuenta, " - ", debes.nombre) AS debe , CONCAT(habers.cuenta, " - ", habers.nombre) AS haber, entries.created_at FROM entries INNER JOIN conceptos ON conceptos.id = entries.concepto INNER JOIN elaborados ON elaborados.id = entries.elaborado_por INNER JOIN debes ON debes.id = entries.debe INNER JOIN habers ON habers.id = entries.haber WHERE entries.id_cost ="'.$this->id_cost.'" ORDER BY entries.no_recibo ASC');
    }

    public function render()
    {
        return view('livewire.purses.abonos');
    }
}
