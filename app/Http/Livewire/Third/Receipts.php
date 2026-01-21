<?php

namespace App\Http\Livewire\Third;

use App\Models\thirdEntry;
use App\Models\haber;
use App\Models\debe;
use App\Models\consecutive;
use Livewire\Component;

class Receipts extends Component
{

    public $type, $thirds, $debe, $haber, $consecutive;

    public function mount($type = "discharge"): void
    {
        $this->type = $type;
        $this->thirds = thirdEntry::orderBy('created_at', 'desc')->get();
        $this->haber = haber::all();
        $this->debe = debe::all();
        $this->consecutive = consecutive::where('type',$type)->first();
    }

    public function render()
    {
        return view('livewire.third.receipts');
    }
}
