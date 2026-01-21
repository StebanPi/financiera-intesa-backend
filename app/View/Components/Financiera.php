<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\haber;
use App\Models\debe;
use App\Models\concepto;
use App\Models\consecutive;
use App\Models\elaborado;
use App\Models\otrosConcepto;

class Financiera extends Component
{

    public $title, $type, $content, $alumno;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($type = "abono", $content = null, $alumno = null)
    {
        //
        $this->title = "Matricula Financiera";
        $this->content = $content;
        $this->alumno = $alumno;

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.financiera');
    }
}
