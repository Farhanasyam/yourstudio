<?php

namespace Tests\Feature;

use App\Models\{Item, Transaction, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    /** Every run starts from the seeders (users, 102 items, settings) */
    protected $seed = true;



    public function test_catalog_idempotent_sales_and_offline_sync(): void
    {
        $this->run_all();
        $this->assertChecksPassed();
    }

    private function item(array $attrs = []): Item
    {
        return Item::create(array_merge(['name' => 'Kuas ' . Str::random(4), 'sku' => 'OF-' . Str::random(6), 'category_id' => DB::table('categories')->value('id'),
            'purchase_price' => 1000, 'selling_price' => 5000, 'stock_quantity' => 10, 'minimum_stock' => 0, 'unit' => 'pcs', 'is_active' => true], $attrs));
    }

    private function run_all(): void
    {
        $kasir = User::where('role', 'kasir')->where('approval_status', 'approved')->first();
        $kasir2 = User::create(['name' => 'Kasir Lain', 'email' => 'kl@x.com', 'password' => bcrypt('password123'), 'role' => 'kasir', 'approval_status' => 'approved', 'is_active' => true]);
        $admins = User::whereIn('role', ['admin', 'superadmin'])->where('is_active', true)->where('approval_status', 'approved')->count();

        // ---------- catalog ----------
        $item = $this->item(['barcode' => 'CAT-111']);
        DB::table('barcodes')->insert(['item_id' => $item->id, 'barcode_type' => 'CODE128', 'barcode_value' => 'CAT-222', 'is_active' => true, 'created_by' => $kasir->id, 'created_at' => now(), 'updated_at' => now()]);
        $r = $this->actingAs($kasir)->getJson('/kasir/catalog');
        $row = collect($r->json('items'))->firstWhere('id', $item->id);
        $this->check('catalog 200 with items/settings/csrf', $r->status() === 200 && $r->json('settings.store_name') !== null && strlen((string) $r->json('csrf_token')) > 10);
        $this->check('catalog item has price, stock and every barcode', $row && $row['price'] == 5000 && $row['stock'] === 10 && in_array('CAT-111', $row['codes']) && in_array('CAT-222', $row['codes']), json_encode($row));
        $inactive = $this->item(['is_active' => false]);
        $this->check('catalog excludes inactive items', !collect($r->json('items'))->firstWhere('id', $inactive->id));

        // ---------- idempotent online sale ----------
        $uuid = (string) Str::uuid();
        $payload = ['client_uuid' => $uuid, 'items' => [['id' => $item->id, 'quantity' => 2, 'price' => 1]], 'paid_amount' => 20000, 'payment_method' => 'cash'];
        $a = $this->postJson('/kasir/transaction', $payload);
        $b = $this->postJson('/kasir/transaction', $payload);
        $this->check('online sale saved', $a->json('success') === true && $a->json('duplicate') === false, json_encode($a->json()));
        $this->check('same client_uuid again -> same sale, duplicate=true', $b->json('success') === true && $b->json('duplicate') === true && $b->json('transaction_id') === $a->json('transaction_id'));
        $this->check('only one transaction and stock decremented once (10->8)', Transaction::where('client_uuid', $uuid)->count() === 1 && $item->fresh()->stock_quantity === 8, 'stock=' . $item->fresh()->stock_quantity);
        $this->check('online ignores browser price (uses Rp 5.000)', (float) Transaction::find($a->json('transaction_id'))->total_amount === 10000.0);
        $r = $this->actingAs($kasir2)->postJson('/kasir/transaction', $payload);
        $this->check('same uuid from another cashier -> 409', $r->status() === 409, (string) $r->status());

        // online still strict
        $r = $this->actingAs($kasir)->postJson('/kasir/transaction', ['client_uuid' => (string) Str::uuid(), 'items' => [['id' => $item->id, 'quantity' => 99]], 'paid_amount' => 999999]);
        $this->check('online: insufficient stock still refused (422)', $r->status() === 422 && !$r->json('success'), $r->json('message'));
        $r = $this->postJson('/kasir/transaction', ['client_uuid' => 'not-a-uuid', 'items' => [['id' => $item->id, 'quantity' => 1]]]);
        $this->check('invalid payload -> 422 JSON (not 500)', $r->status() === 422 && str_contains($r->json('message'), 'tidak valid'), $r->status() . ' ' . $r->json('message'));

        // ---------- offline sync ----------
        $soldAt = now()->subHours(3)->startOfMinute();
        $code = 'OFF-' . $soldAt->format('YmdHis') . '-1234';
        $u2 = (string) Str::uuid();
        $r = $this->postJson('/kasir/transaction', ['client_uuid' => $u2, 'offline' => true, 'transaction_code' => $code, 'created_at' => $soldAt->toIso8601String(),
            'items' => [['id' => $item->id, 'quantity' => 1, 'price' => 5000]], 'paid_amount' => 5000, 'payment_method' => 'qris']);
        $t = Transaction::where('client_uuid', $u2)->first();
        $this->check('offline sale synced', $r->json('success') === true && $t, json_encode($r->json()));
        $this->check('keeps offline receipt code + sale time', $t && $t->transaction_code === $code && $t->transaction_date->equalTo($soldAt), $t ? $t->transaction_code . ' ' . $t->transaction_date : '');
        $this->check('marked offline, not needing review, has note', $t && $t->is_offline && !$t->needs_review && str_contains($t->notes, 'Transaksi offline'), $t->notes ?? '');
        $this->check('stock 8 -> 7', $item->fresh()->stock_quantity === 7);

        // offline oversell + price changed meanwhile -> accepted, flagged, admins notified
        $item->update(['selling_price' => 6000]);
        $before = DB::table('notifications')->where('type', 'like', '%OfflineSaleReview%')->count();
        $u3 = (string) Str::uuid();
        $r = $this->postJson('/kasir/transaction', ['client_uuid' => $u3, 'offline' => true, 'transaction_code' => 'OFF-20260101000000-0001', 'created_at' => now()->subHour()->toIso8601String(),
            'items' => [['id' => $item->id, 'quantity' => 9, 'price' => 5000]], 'paid_amount' => 45000, 'payment_method' => 'cash']);
        $t = Transaction::where('client_uuid', $u3)->first();
        $this->check('offline oversell accepted (policy)', $r->json('success') === true && $t, json_encode($r->json()));
        $this->check('stock goes negative 7 -> -2', $item->fresh()->stock_quantity === -2, 'stock=' . $item->fresh()->stock_quantity);
        $this->check('recorded with the price the customer paid (9 x 5.000)', $t && (float) $t->total_amount === 45000.0);
        $this->check('needs_review + note lists both problems', $t && $t->needs_review && str_contains($t->notes, 'Stok') && str_contains($t->notes, 'harga sekarang'), $t->notes ?? '');
        $after = DB::table('notifications')->where('type', 'like', '%OfflineSaleReview%')->count();
        $this->check("all $admins admins notified", $after - $before === $admins, 'new=' . ($after - $before));
        $this->check('response says needs_review', $r->json('needs_review') === true);

        // offline: item deactivated meanwhile is still recorded
        $gone = $this->item(['stock_quantity' => 5]);
        $gone->update(['is_active' => false]);
        $r = $this->postJson('/kasir/transaction', ['client_uuid' => (string) Str::uuid(), 'offline' => true, 'items' => [['id' => $gone->id, 'quantity' => 1, 'price' => 5000]], 'paid_amount' => 5000]);
        $this->check('offline: deactivated item still recorded', $r->json('success') === true);

        // offline: item deleted -> 422 (client marks it failed for an admin)
        $r = $this->postJson('/kasir/transaction', ['client_uuid' => (string) Str::uuid(), 'offline' => true, 'items' => [['id' => 999999, 'quantity' => 1, 'price' => 5000]], 'paid_amount' => 5000]);
        $this->check('offline: unknown item -> 422', $r->status() === 422, (string) $r->status());

        // offline code already used -> server gives a new TRX code instead of failing
        $r = $this->postJson('/kasir/transaction', ['client_uuid' => (string) Str::uuid(), 'offline' => true, 'transaction_code' => $code, 'items' => [['id' => $gone->id, 'quantity' => 1, 'price' => 5000]], 'paid_amount' => 5000]);
        $this->check('duplicate offline code -> new TRX code', $r->json('success') === true && str_starts_with($r->json('transaction_code'), 'TRX-'), (string) $r->json('transaction_code'));

        // absurd offline date is replaced by now
        $u4 = (string) Str::uuid();
        $this->postJson('/kasir/transaction', ['client_uuid' => $u4, 'offline' => true, 'created_at' => '2001-01-01T00:00:00Z', 'items' => [['id' => $gone->id, 'quantity' => 1, 'price' => 5000]], 'paid_amount' => 5000]);
        $this->check('offline date older than 30 days -> now', Transaction::where('client_uuid', $u4)->first()->transaction_date->isToday());

        // ---------- history shows it ----------
        $admin = User::where('role', 'admin')->where('approval_status', 'approved')->first();
        $html = $this->actingAs($admin)->get('/transaction-history')->getContent();
        $this->check('history shows Offline + Perlu dicek badges', str_contains($html, '>Offline</span>') && str_contains($html, '>Perlu dicek</span>'));
        $html = $this->get('/transaction-history/' . Transaction::where('client_uuid', $u3)->value('id'))->getContent();
        $this->check('detail shows review note', str_contains($html, 'Perlu dicek:'));
        $n = DB::table('notifications')->where('notifiable_id', $admin->id)->where('type', 'like', '%OfflineSaleReview%')->latest('created_at')->value('data');
        $this->check('notification links to the transaction', $n && str_contains($n, 'transaction-history'), (string) $n);
    }
}
