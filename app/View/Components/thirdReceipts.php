<?php

namespace App\View\Components;
use App\Models\thirdEntry;
use App\Models\haber;
use App\Models\debe;
use App\Models\consecutive;
use App\Models\elaborado;
use App\Models\ConceptDischargeReceipt;
use App\Models\ConceptEntryReceipt;
use App\Models\ThirdReceipts as ThirdReceiptsModel;
use Illuminate\View\Component;

class thirdReceipts extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

    public $types, $thirds, $debe, $haber, $consecutive, $concepts, $title, $no_recibo, $content, $elaborados;


    public function __construct($types = "discharge", $no_recibo = null, $content = null)
    {
        $this->types = $types;
        $this->no_recibo = $no_recibo;
        $this->content = $content;
        $this->thirds = thirdEntry::orderBy('created_at', 'desc')->get();
        $this->haber = haber::getUnique();
        $this->debe = debe::getUnique();
        $this->consecutive = consecutive::where('type',$types)->first();
        $this->elaborados = elaborado::getUnique();
        if($types == "discharge"){
            $this->title = "Recibo de Egreso de Terceros";
            $this->concepts = ConceptDischargeReceipt::where('state', true)->orderBy('name')->get();
        }else{
            $this->title = "Recibo de Ingreso de Terceros";
            $this->concepts = ConceptEntryReceipt::where('state', true)->orderBy('name')->get();
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.third-receipts');
    }
}
