<?php

namespace App\Http\Livewire\Purses;


use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Cartera extends Component
{

    public $id_cost,$purses,$entries,$cost;

    public function mount(){
        // Obtener el cost para obtener el cod_alumno
        $cost = DB::table('costs')->where('id',$this->id_cost)->first();
        if ($cost) {
            // Obtener todos los costs del estudiante
            $costs = DB::table('costs')->where('cod_alumno', $cost->cod_alumno)->orderBy('numero_semestre', 'asc')->get();
            $this->cost = $costs;
            
            // Obtener todos los id_cost del estudiante
            $ids_cost = $costs->pluck('id')->toArray();
            
            // Obtener total de abonos de todos los semestres
            $this->entries = DB::connection('mysql')->select('SELECT SUM(valor) AS TotalAbono FROM entries WHERE id_cost IN ("'.implode('","', $ids_cost).'")');
            
            // Obtener todas las cuotas de todos los semestres con información del semestre
            $this->purses = DB::connection('mysql')->select('
                SELECT purses.*, costs.numero_semestre 
                FROM purses 
                INNER JOIN costs ON purses.id_cost = costs.id 
                WHERE purses.id_cost IN ("'.implode('","', $ids_cost).'")
                ORDER BY purses.fecha_pago ASC
            ');
        } else {
            $this->cost = collect();
            $this->entries = [];
            $this->purses = [];
        }
    }

    public function render()
    {
        return view('livewire.purses.cartera');
    }
}
