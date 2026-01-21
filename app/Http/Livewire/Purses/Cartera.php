<?php

namespace App\Http\Livewire\Purses;


use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Cartera extends Component
{

    public $id_cost,$purses,$entries,$cost;

    public function mount(){
        $this->cost = DB::table('costs')->where('id',$this->id_cost)->get();
        $this->entries = DB::connection('mysql')->select('SELECT SUM(valor) AS TotalAbono FROM entries WHERE id_cost ="'.$this->id_cost.'"');
        $this->purses = DB::connection('mysql')->select('SELECT * FROM purses WHERE id_cost = "'.$this->id_cost.'"');
    }

    public function render()
    {
        return view('livewire.purses.cartera');
    }
}
