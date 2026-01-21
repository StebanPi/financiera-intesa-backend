<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\DB;

class cartera extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

    public $id_cost,$purses,$entries,$cost, $student;


    public function __construct($id_cost = 0, $student = "", $entries = "" , $cost = "", $purses = "")
    {
        $this->id_cost = $id_cost; // Corregido: asignar el parámetro
        if($this->id_cost != 0){
            $this->cost = $cost;
            $this->entries = $entries;
            $this->purses = $purses;
            $this->student = $student;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.cartera');
    }
}
