<?php

namespace Tests\Feature\Api\V1;

use App\Models\ConceptEntryReceipt;
use App\Models\debe;
use App\Models\elaborado;
use App\Models\haber;
use App\Models\thirdEntry;
use App\Models\ThirdReceipts;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThirdReceiptUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_update_third_receipt()
    {
        $third = thirdEntry::create([
            'nombre' => 'Test Third',
            'actividad' => 1,
            'cedula' => '12345'
        ]);
        $concept = ConceptEntryReceipt::create([
            'name' => 'Test Concept',
            'debe' => 1,
            'haber' => 1,
            'state' => true
        ]);
        $debe = debe::create(['cuenta' => '110505', 'nombre' => 'Caja']);
        $haber = haber::create(['cuenta' => '130505', 'nombre' => 'Clientes']);
        $elaborado = elaborado::create(['nombre' => 'Admin']);

        $receipt = ThirdReceipts::create([
            'no_recibo' => 100,
            'type' => 'entry',
            'third' => $third->id,
            'concepto' => $concept->id,
            'valor' => 50000,
            'debe' => $debe->id,
            'haber' => $haber->id,
            'elaborado_por' => $elaborado->id,
            'forma' => 'Efectivo',
            'fecha_recibo' => '2024-01-15',
        ]);

        $newData = [
            'detalles' => 'Updated details',
            'valor' => '60.000',
            'forma' => 'Consignación',
        ];

        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/v1/third-receipts/{$receipt->id}", $newData);

        $response->assertStatus(200)
            ->assertJsonPath('data.detalles', 'Updated details')
            ->assertJsonPath('data.valor', 60000)
            ->assertJsonPath('data.forma', 'Bancos');

        $this->assertDatabaseHas('third_receipts', [
            'id' => $receipt->id,
            'detalles' => 'Updated details',
            'valor' => 60000,
            'forma' => 'Bancos',
        ]);
    }

    public function test_cannot_update_non_existent_receipt()
    {
        $response = $this->actingAs($this->user, 'api')
            ->putJson("/api/v1/third-receipts/9999", ['detalles' => 'Fail']);

        $response->assertStatus(404);
    }
}
