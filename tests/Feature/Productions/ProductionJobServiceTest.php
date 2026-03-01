<?php

namespace Tests\Feature\Productions;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductionJob;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Services\ProductionJobService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionJobServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_jobs_are_created_per_item_only_when_order_status_is_produksi(): void
    {
        [$orderDraft] = $this->createOrderWithItems('draft', 2);
        [$orderProduksi] = $this->createOrderWithItems('produksi', 2);

        $service = app(ProductionJobService::class);

        $service->syncByOrderStatus($orderDraft);
        $this->assertSame(0, ProductionJob::query()->where('order_id', $orderDraft->id)->count());

        $service->syncByOrderStatus($orderProduksi);
        $this->assertSame(2, ProductionJob::query()->where('order_id', $orderProduksi->id)->count());

        // idempotent: sync ulang tidak boleh duplikasi
        $service->syncByOrderStatus($orderProduksi);
        $this->assertSame(2, ProductionJob::query()->where('order_id', $orderProduksi->id)->count());
    }

    public function test_jobs_auto_assign_role_by_stage_desain_and_produksi(): void
    {
        Role::query()->updateOrCreate(['name' => 'Desainer'], ['name' => 'Desainer']);
        Role::query()->updateOrCreate(['name' => 'Operator'], ['name' => 'Operator']);

        [$order] = $this->createOrderWithItems('desain', 2);
        $service = app(ProductionJobService::class);

        $service->syncByOrderStatus($order);

        $desainJobs = ProductionJob::query()->where('order_id', $order->id)->get();
        $this->assertCount(2, $desainJobs);
        $this->assertTrue($desainJobs->every(fn ($job) => $job->stage === ProductionJob::STAGE_DESAIN));
        $this->assertTrue($desainJobs->every(fn ($job) => $job->assignedRole?->slug === 'desainer'));

        $order->update(['status' => 'produksi']);
        $service->syncByOrderStatus($order->fresh());

        $produksiJobs = ProductionJob::query()->where('order_id', $order->id)->get();
        $this->assertCount(2, $produksiJobs);
        $this->assertTrue($produksiJobs->every(fn ($job) => $job->stage === ProductionJob::STAGE_PRODUKSI));
        $this->assertTrue($produksiJobs->every(fn ($job) => $job->status === ProductionJob::STATUS_ANTRIAN));
        $this->assertTrue($produksiJobs->every(fn ($job) => $job->assignedRole?->slug === 'operator'));
    }

    public function test_qc_fail_returns_job_and_order_to_produksi(): void
    {
        [$order] = $this->createOrderWithItems('produksi', 1);
        $service = app(ProductionJobService::class);

        $service->syncByOrderStatus($order);
        $job = ProductionJob::query()->where('order_id', $order->id)->firstOrFail();

        $service->markInProgress($job->id);
        $service->moveToQc($job->id);

        $order->refresh();
        $this->assertSame('qc', (string) $order->status);

        $service->qcFail($job->id, 'Warna tidak sesuai proof.');

        $job->refresh();
        $order->refresh();

        $this->assertSame(ProductionJob::STATUS_IN_PROGRESS, (string) $job->status);
        $this->assertSame('produksi', (string) $order->status);
        $this->assertDatabaseHas('production_job_logs', [
            'production_job_id' => $job->id,
            'event' => 'qc_fail',
            'to_status' => ProductionJob::STATUS_IN_PROGRESS,
        ]);
    }

    public function test_all_jobs_qc_pass_will_sync_order_to_siap(): void
    {
        [$order] = $this->createOrderWithItems('produksi', 2);
        $service = app(ProductionJobService::class);

        $service->syncByOrderStatus($order);

        $jobs = ProductionJob::query()->where('order_id', $order->id)->get();
        foreach ($jobs as $job) {
            $service->markInProgress($job->id);
            $service->moveToQc($job->id);
        }

        $order->refresh();
        $this->assertSame('qc', (string) $order->status);

        foreach ($jobs as $job) {
            $service->qcPass($job->id);
        }

        $order->refresh();
        $this->assertSame('siap', (string) $order->status);
    }

    public function test_order_status_stays_desain_until_all_items_leave_desain_stage(): void
    {
        Role::query()->updateOrCreate(['name' => 'Desainer'], ['name' => 'Desainer']);
        Role::query()->updateOrCreate(['name' => 'Operator'], ['name' => 'Operator']);

        [$order] = $this->createOrderWithItems('desain', 2);
        $service = app(ProductionJobService::class);
        $service->syncByOrderStatus($order);

        $jobs = ProductionJob::query()
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get();

        $job1 = $jobs->get(0);
        $job2 = $jobs->get(1);

        // Item 1 selesai produksi, item 2 masih di tahap desain.
        $service->switchStage($job1->id, ProductionJob::STAGE_PRODUKSI, 'Desain item 1 selesai.');
        $service->markInProgress($job1->id);

        $order->refresh();
        $this->assertSame('desain', (string) $order->status);

        // Setelah item terakhir keluar dari tahap desain, order pindah ke produksi.
        $service->switchStage($job2->id, ProductionJob::STAGE_PRODUKSI, 'Desain item 2 selesai.');
        $service->markInProgress($job2->id);

        $order->refresh();
        $this->assertSame('produksi', (string) $order->status);
    }

    public function test_moving_card_to_produksi_triggers_stock_out_sync(): void
    {
        [$order] = $this->createOrderWithItems('desain', 1, true);
        $service = app(ProductionJobService::class);
        $service->syncByOrderStatus($order);

        $job = ProductionJob::query()->where('order_id', $order->id)->firstOrFail();

        $this->assertDatabaseMissing('stock_movements', [
            'ref_type' => 'order',
            'ref_id' => $order->id,
            'type' => 'out',
        ]);

        // Item keluar dari tahap desain lalu mulai produksi.
        $service->switchStage($job->id, ProductionJob::STAGE_PRODUKSI, 'Desain selesai.');
        $service->markInProgress($job->id);

        $order->refresh();
        $this->assertSame('produksi', (string) $order->status);

        $this->assertDatabaseHas('stock_movements', [
            'ref_type' => 'order',
            'ref_id' => $order->id,
            'type' => 'out',
            'material_id' => $job->orderItem->material_id,
        ]);

        $this->assertGreaterThan(
            0,
            (float) StockMovement::query()
                ->where('ref_type', 'order')
                ->where('ref_id', $order->id)
                ->where('type', 'out')
                ->sum('qty')
        );
    }

    protected function createOrderWithItems(string $status, int $itemCount, bool $withMaterial = false): array
    {
        Branch::query()->updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Headquarter',
                'is_main' => true,
            ]
        );

        $unit = Unit::query()->firstOrCreate(
            ['name' => 'Pcs'],
            [
                'is_dimension' => false,
                'branch_id' => 1,
            ]
        );

        $category = Category::query()->firstOrCreate(
            ['name' => 'Cetak'],
            ['branch_id' => 1]
        );

        $material = null;
        if ($withMaterial) {
            $material = Material::query()->create([
                'name' => 'Flexi Test ' . now()->format('Hisv') . '-' . rand(100, 999),
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'cost_price' => 10000,
                'conversion_qty' => 1,
                'roll_width_cm' => 320,
                'roll_waste_percent' => 3,
                'branch_id' => 1,
            ]);
        }

        $order = Order::query()->create([
            'order_no' => 'ORD-TST-' . now()->format('YmdHisv') . '-' . rand(10, 99),
            'branch_id' => 1,
            'status' => $status,
            'order_date' => now()->toDateString(),
            'notes' => 'test',
        ]);

        $items = [];
        for ($i = 1; $i <= $itemCount; $i++) {
            $product = Product::query()->create([
                'sku' => 'SKU-TST-' . now()->format('Hisv') . '-' . $i . '-' . rand(10, 99),
                'name' => 'Produk Test ' . $i,
                'product_type' => 'goods',
                'unit_id' => $unit->id,
                'category_id' => $category->id,
                'base_price' => 1000,
                'sale_price' => 1500,
                'branch_id' => 1,
            ]);

            $items[] = OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'material_id' => $material?->id,
                'unit_id' => $unit->id,
                'qty' => 1,
                'length_cm' => $withMaterial ? 100 : null,
                'width_cm' => $withMaterial ? 100 : null,
                'price' => 1500,
                'total' => 1500,
            ]);
        }

        return [$order, $items];
    }
}
