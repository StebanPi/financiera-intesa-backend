<?php

namespace App\Http\Livewire\Setting;
use App\Http\Controllers\DateController;
use Livewire\Component;

class Date extends Component
{
    public $date,$type;

    public function mount(){
        if(!empty($this->date)){
            $this->date = DateController::getMesSubtr($this->date);
        }
    }

    public function render()
    {
        return view('livewire.setting.date');
    }
}
