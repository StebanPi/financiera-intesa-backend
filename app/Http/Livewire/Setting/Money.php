<?php

namespace App\Http\Livewire\Setting;
use App\Http\Controllers\MoneyController;
use Livewire\Component;

class Money extends Component
{
    public $money;

    public function mount(){
        $this->money = MoneyController::main($this->money);
    }

    public function render()
    {
        return view('livewire.setting.money');
    }
}
