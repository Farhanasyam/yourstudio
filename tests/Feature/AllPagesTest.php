<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Opens every GET route as every role and fails on any server error (5xx).
 */
class AllPagesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_no_page_returns_a_server_error_for_any_role(): void
    {
        $item = \App\Models\Item::first();
        $item->update(['selling_price' => 10000, 'stock_quantity' => 10]);
        $kasir = User::where('role', 'kasir')->where('approval_status', 'approved')->first();
        $admin = User::where('role', 'admin')->where('approval_status', 'approved')->first();
        $super = User::where('role', 'superadmin')->first();

        // Some data so detail pages have something to show
        $this->actingAs($kasir)->postJson('/kasir/transaction', ['client_uuid' => (string) Str::uuid(), 'items' => [['id' => $item->id, 'quantity' => 1]], 'paid_amount' => 10000]);
        $supplierId = DB::table('suppliers')->insertGetId(['name' => 'Supplier Tes', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($admin)->post('/stock-in', ['supplier_id' => $supplierId, 'transaction_date' => today()->toDateString(), 'items' => [['item_id' => $item->id, 'quantity' => 1, 'purchase_price' => 1000]]]);
        $this->post('/stock-adjustments', ['item_id' => $item->id, 'type' => 'increase', 'quantity' => 1, 'reason' => 'found', 'adjustment_date' => today()->toDateString()]);
        $this->actingAs($super)->post('/reports', ['type' => 'daily_sales', 'name' => 'Tes', 'start_date' => today()->subWeek()->toDateString(), 'end_date' => today()->toDateString()]);

        $params = [
            'item' => $item->id,
            'category' => DB::table('categories')->value('id'),
            'supplier' => $supplierId,
            'stockIn' => DB::table('stock_ins')->value('id'),
            'stockAdjustment' => DB::table('stock_adjustments')->value('id'),
            'barcode' => DB::table('barcodes')->value('id'),
            'sale' => DB::table('transactions')->value('id'),
            'report' => DB::table('reports')->value('id'),
            'transaction' => DB::table('transactions')->value('id'),
            'user' => $kasir->id,
            'id' => DB::table('notifications')->value('id') ?? 'none',
            'key' => 'store_name',
        ];

        foreach (['superadmin' => $super, 'admin' => $admin, 'kasir' => $kasir] as $role => $user) {
            foreach (Route::getRoutes() as $route) {
                if (!in_array('GET', $route->methods())) continue;
                $uri = $route->uri();
                if (str_starts_with($uri, '_') || str_starts_with($uri, 'sanctum')) continue;
                $path = preg_replace_callback('/\{(\w+)\??\}/', fn ($m) => $params[$m[1]] ?? 'missing', $uri);
                $this->app['auth']->forgetGuards();
                $response = $this->actingAs($user)->get('/' . ltrim($path, '/'));
                $this->check("[$role] GET /$uri", $response->getStatusCode() < 500, $this->describe($response));
            }
        }

        $this->assertChecksPassed();
    }
}
