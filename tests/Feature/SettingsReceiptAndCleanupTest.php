<?php

namespace Tests\Feature;

use App\Models\{Item, SystemSetting, Transaction, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingsReceiptAndCleanupTest extends TestCase
{
    use RefreshDatabase;

    /** Every run starts from the seeders (users, 102 items, settings) */
    protected $seed = true;



    public function test_settings_receipt_low_stock_and_removed_pages(): void
    {
        $this->run_all();
        $this->assertChecksPassed();
    }

    private function sell(User $kasir, int $itemId, int $qty)
    {
        return $this->actingAs($kasir)->postJson('/kasir/transaction', ['items' => [['id' => $itemId, 'quantity' => $qty]], 'paid_amount' => 10000000, 'payment_method' => 'cash']);
    }

    private function lowStockCount(int $itemId): int
    {
        return DB::table('notifications')->where('type', 'like', '%LowStockNotification')->where('data', 'like', '%"item_id":' . $itemId . ',%')->count();
    }

    private function run_all(): void
    {
        $kasir = User::where('role', 'kasir')->where('approval_status', 'approved')->first();
        $admins = User::whereIn('role', ['admin', 'superadmin'])->where('is_active', true)->where('approval_status', 'approved')->count();

        // ---------- 1. reset password removed ----------
        foreach (['/reset-password', '/change-password'] as $u) {
            $r = $this->get($u);
            $this->check("GET $u is gone (404)", $r->getStatusCode() === 404, (string) $r->getStatusCode());
        }
        $r = $this->post('/reset-password', ['email' => 'superadmin@yourstudio.com']);
        $this->check('POST /reset-password is gone', in_array($r->getStatusCode(), [404, 405]), (string) $r->getStatusCode());
        $login = $this->get('/login');
        $this->check('login page has no reset link', $login->getStatusCode() === 200 && !str_contains($login->getContent(), 'reset-password') && str_contains($login->getContent(), 'Hubungi Super Admin'));

        // ---------- 4. template pages closed ----------
        foreach (['/billing', '/tables', '/virtual-reality', '/rtl', '/profile-static', '/sign-in-static', '/sign-up-static', '/user-management-old', '/dashboard-template', '/anything'] as $u) {
            $r = $this->actingAs($kasir)->get($u);
            $this->check("GET $u -> 404", $r->getStatusCode() === 404, (string) $r->getStatusCode());
        }
        foreach (['/dashboard', '/profile', '/kasir', '/notifications'] as $u) {
            $r = $this->actingAs($kasir)->get($u);
            $this->check("real page $u still works", $r->getStatusCode() === 200, (string) $r->getStatusCode());
        }

        // ---------- 2. settings -> receipt ----------
        $item = Item::create(['name' => 'Kuas Uji', 'sku' => 'NF-1', 'category_id' => DB::table('categories')->value('id'), 'purchase_price' => 1000, 'selling_price' => 5000, 'stock_quantity' => 20, 'minimum_stock' => 5, 'unit' => 'pcs', 'is_active' => true]);
        $trxId = $this->sell($kasir, $item->id, 1)->json('transaction_id');
        $this->check('sale for receipt', (bool) $trxId);
        SystemSetting::where('key', 'store_name')->update(['value' => 'TOKO UJI SETTING']);
        SystemSetting::where('key', 'store_address')->update(['value' => 'Jalan Uji 99']);
        SystemSetting::where('key', 'store_phone')->update(['value' => '0341-555']);
        SystemSetting::where('key', 'store_instagram')->update(['value' => '@uji_ig']);
        SystemSetting::where('key', 'receipt_header')->update(['value' => 'Header Uji']);
        SystemSetting::where('key', 'receipt_footer')->update(['value' => 'Footer Uji']);
        SystemSetting::where('key', 'auto_print_receipt')->update(['value' => '1']);
        $html = $this->actingAs($kasir)->get("/kasir/receipt/$trxId")->getContent();
        foreach (['TOKO UJI SETTING', 'Jalan Uji 99', 'Telp. 0341-555', '@uji_ig', 'Header Uji', 'Footer Uji'] as $txt) {
            $this->check("receipt shows setting '$txt'", str_contains($html, $txt));
        }
        $this->check('receipt no longer hardcodes old address', !str_contains($html, 'Sawojajar'));
        $this->check('auto print ON -> AUTO_PRINT = true', str_contains($html, 'const AUTO_PRINT = true'));
        SystemSetting::where('key', 'auto_print_receipt')->update(['value' => '0']);
        $html = $this->get("/kasir/receipt/$trxId")->getContent();
        $this->check('auto print OFF -> AUTO_PRINT = false', str_contains($html, 'const AUTO_PRINT = false'));
        $html = $this->get("/kasir/receipt/$trxId?copy=1")->getContent();
        $this->check('reprint (copy) always prints', str_contains($html, 'const AUTO_PRINT = true') && str_contains($html, 'COPY'));
        SystemSetting::where('key', 'store_phone')->update(['value' => '']);
        SystemSetting::where('key', 'store_instagram')->update(['value' => '']);
        $html = $this->get("/kasir/receipt/$trxId")->getContent();
        $this->check('empty phone/instagram hidden', !str_contains($html, 'Telp.') && !str_contains($html, 'fa-instagram'));
        $r = $this->actingAs(User::where('role', 'admin')->where('approval_status', 'approved')->first())->get('/settings');
        $this->check('settings page lists Instagram setting', str_contains($r->getContent(), 'settings[store_instagram]'));

        // ---------- 3. low stock notification ----------
        // item: stock 19 now, minimum 5
        SystemSetting::where('key', 'enable_low_stock_notification')->update(['value' => '1']);
        $this->sell($kasir, $item->id, 10); // 19 -> 9 (above 5)
        $this->check('no notification while above minimum', $this->lowStockCount($item->id) === 0, 'count=' . $this->lowStockCount($item->id));
        $this->sell($kasir, $item->id, 4); // 9 -> 5 (crosses)
        $this->check("crossing minimum notifies every admin ($admins)", $this->lowStockCount($item->id) === $admins, 'count=' . $this->lowStockCount($item->id));
        $n = DB::table('notifications')->where('type', 'like', '%LowStockNotification')->latest('created_at')->value('data');
        $this->check('message mentions item and remaining stock', str_contains($n, 'Kuas Uji') && str_contains($n, 'tersisa 5 pcs'), $n);
        $this->sell($kasir, $item->id, 1); // 5 -> 4 (already low)
        $this->check('no repeat notification on later sales', $this->lowStockCount($item->id) === $admins, 'count=' . $this->lowStockCount($item->id));
        $kasirNotif = DB::table('notifications')->where('notifiable_id', $kasir->id)->where('type', 'like', '%LowStock%')->count();
        $this->check('kasir does not receive it', $kasirNotif === 0);

        // default threshold when item has no minimum
        $item2 = Item::create(['name' => 'Cat Uji', 'sku' => 'NF-2', 'category_id' => DB::table('categories')->value('id'), 'purchase_price' => 1000, 'selling_price' => 5000, 'stock_quantity' => 12, 'minimum_stock' => 0, 'unit' => 'botol', 'is_active' => true]);
        SystemSetting::where('key', 'low_stock_threshold')->update(['value' => '10']);
        $this->sell($kasir, $item2->id, 2); // 12 -> 10 (crosses global threshold 10)
        $this->check('item without minimum uses global threshold', $this->lowStockCount($item2->id) === $admins, 'count=' . $this->lowStockCount($item2->id));

        // sold out -> "habis"
        $this->sell($kasir, $item2->id, 10); // 10 -> 0, already low, no new notif
        $item3 = Item::create(['name' => 'Kanvas Uji', 'sku' => 'NF-3', 'category_id' => DB::table('categories')->value('id'), 'purchase_price' => 1000, 'selling_price' => 5000, 'stock_quantity' => 8, 'minimum_stock' => 3, 'unit' => 'pcs', 'is_active' => true]);
        $this->sell($kasir, $item3->id, 8); // 8 -> 0 crosses
        $d = DB::table('notifications')->where('data', 'like', '%"item_id":' . $item3->id . ',%')->value('data');
        $this->check('sold out says "habis"', $d && str_contains($d, 'Stok Habis') && str_contains($d, 'habis'), (string) $d);

        // setting off -> nothing
        SystemSetting::where('key', 'enable_low_stock_notification')->update(['value' => '0']);
        $item4 = Item::create(['name' => 'Palet Uji', 'sku' => 'NF-4', 'category_id' => DB::table('categories')->value('id'), 'purchase_price' => 1000, 'selling_price' => 5000, 'stock_quantity' => 6, 'minimum_stock' => 5, 'unit' => 'pcs', 'is_active' => true]);
        $r = $this->sell($kasir, $item4->id, 2);
        $this->check('setting OFF -> no notification, sale still OK', $r->json('success') === true && $this->lowStockCount($item4->id) === 0);

        // appears in the admin notification list
        SystemSetting::where('key', 'enable_low_stock_notification')->update(['value' => '1']);
        $admin = User::where('role', 'admin')->where('approval_status', 'approved')->first();
        $r = $this->actingAs($admin)->getJson('/api/notifications');
        $this->check('admin sees it in notification bell API', str_contains($r->getContent(), 'Stok'), substr($r->getContent(), 0, 120));
        $r = $this->get('/notifications');
        $this->check('admin notifications page lists it', str_contains($r->getContent(), 'Kuas Uji'));
    }
}
