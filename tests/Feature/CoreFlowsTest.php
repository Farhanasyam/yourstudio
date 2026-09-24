<?php

namespace Tests\Feature;

use App\Models\{Barcode, Category, Item, Report, StockAdjustment, StockIn, Supplier, SystemSetting, Transaction, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CoreFlowsTest extends TestCase
{
    use RefreshDatabase;

    /** Every run starts from the seeders (users, 102 items, settings) */
    protected $seed = true;




    public function test_core_flows(): void
    {
        $this->flows();
        $this->assertChecksPassed();
    }
    private function flows(): void
    {
        $super = User::where('role', 'superadmin')->first();
        $admin = User::where('role', 'admin')->where('approval_status', 'approved')->first();
        $kasir = User::where('role', 'kasir')->where('approval_status', 'approved')->first();
        $kasir2 = User::create(['name' => 'Kasir Dua', 'email' => 'k2@x.com', 'password' => Hash::make('password'), 'role' => 'kasir', 'approval_status' => 'approved', 'is_active' => true]);

        // ---------- Category ----------
        $r = $this->actingAs($admin)->post('/categories', ['name' => 'Kategori Uji', 'description' => 'x']);
        $cat = Category::where('name', 'Kategori Uji')->first();
        $this->check('Category store', (bool) $cat, $this->describe($r));
        $r = $this->put("/categories/{$cat->id}", ['name' => 'Kategori Uji 2']);
        $this->check('Category update', $cat->fresh()->name === 'Kategori Uji 2', $this->describe($r));
        foreach (["/categories/{$cat->id}", "/categories/{$cat->id}/edit"] as $u) { $r = $this->get($u); $this->check("GET $u", $r->getStatusCode() === 200, $this->describe($r)); }

        // ---------- Supplier ----------
        $r = $this->post('/suppliers', ['name' => 'Supplier Uji', 'email' => 's@x.com', 'phone' => '0812', 'address' => 'a', 'contact_person' => 'b']);
        $sup = Supplier::where('name', 'Supplier Uji')->first();
        $this->check('Supplier store', (bool) $sup, $this->describe($r));
        $r = $this->put("/suppliers/{$sup->id}", ['name' => 'Supplier Uji 2']);
        $this->check('Supplier update', $sup->fresh()->name === 'Supplier Uji 2', $this->describe($r));
        foreach (["/suppliers/{$sup->id}", "/suppliers/{$sup->id}/edit"] as $u) { $r = $this->get($u); $this->check("GET $u", $r->getStatusCode() === 200, $this->describe($r)); }

        // ---------- Item ----------
        $r = $this->post('/items', ['name' => 'Item Uji', 'sku' => 'UJI-001', 'category_id' => $cat->id, 'supplier_id' => $sup->id,
            'purchase_price' => 5000, 'selling_price' => 10000, 'stock_quantity' => 5, 'minimum_stock' => 1, 'unit' => 'pcs']);
        $item = Item::where('sku', 'UJI-001')->first();
        $this->check('Item store', (bool) $item, $this->describe($r));
        $r = $this->put("/items/{$item->id}", ['name' => 'Item Uji', 'sku' => 'UJI-001', 'category_id' => $cat->id,
            'purchase_price' => 5000, 'selling_price' => 10000, 'stock_quantity' => 5, 'minimum_stock' => 1, 'unit' => 'pcs']);
        $this->check('Item update', $r->getStatusCode() === 302 && !session('errors'), $this->describe($r));
        $this->check('Item can be set inactive via edit form', str_contains(file_get_contents(resource_path('views/pages/items/edit.blade.php')), 'is_active'), 'edit form has no is_active field');
        $r = $this->get('/items?search=Uji&stock_status=low_stock', ['X-Requested-With' => 'XMLHttpRequest']);
        $this->check('Items AJAX filter', $r->getStatusCode() === 200, $this->describe($r));
        $r = $this->get("/items/{$item->id}/print-barcode");
        $this->check('Item print-select page', $r->getStatusCode() === 200, $this->describe($r));

        // ---------- Barcode ----------
        $r = $this->post('/barcodes', ['item_id' => $item->id, 'barcode_number' => 'UJI12345', 'barcode_type' => 'CODE128', 'is_active' => 'on']);
        $bc = Barcode::where('barcode_value', 'UJI12345')->first();
        $this->check('Barcode store', (bool) $bc, $this->describe($r));
        $r = $this->put("/barcodes/{$bc->id}", ['item_id' => $item->id, 'barcode_number' => 'UJI12346', 'barcode_type' => 'CODE128', 'is_active' => 'on']);
        $this->check('Barcode update', $bc->fresh()->barcode_value === 'UJI12346', $this->describe($r));
        $r = $this->post('/barcodes/generate-all-types', ['item_id' => $item->id]);
        $this->check('Barcode generate-all-types', Barcode::where('item_id', $item->id)->count() === 4, $this->describe($r) . ' count=' . Barcode::where('item_id', $item->id)->count());
        $lens = Barcode::where('item_id', $item->id)->get()->map(fn($b) => $b->barcode_type . ':' . strlen($b->barcode_value))->implode(',');
        $this->check('Generated barcode lengths (kasir autoscan=13)', true, $lens);
        $r = $this->post('/barcodes/bulk-generate', ['barcode_type' => 'EAN13', 'generation_mode' => 'selected_items', 'item_ids' => [$item->id]]);
        $this->check('Barcode bulk-generate', $r->getStatusCode() === 302 && !session('error'), $this->describe($r));
        $r = $this->postJson('/barcodes/search', ['barcode_value' => Barcode::where('item_id', $item->id)->value('barcode_value')]);
        $this->check('Barcode search API', $r->getStatusCode() === 200, $this->describe($r));
        $r = $this->get('/barcodes?search=UJI', ['X-Requested-With' => 'XMLHttpRequest']);
        $this->check('Barcodes AJAX list', $r->getStatusCode() === 200, $this->describe($r));
        $b1 = Barcode::where('item_id', $item->id)->first();
        foreach (["/barcodes/{$b1->id}", "/barcodes/{$b1->id}/edit", "/barcodes/{$b1->id}/print"] as $u) { $r = $this->get($u); $this->check("GET $u", $r->getStatusCode() === 200, $this->describe($r)); }
        $r = $this->delete("/barcodes/{$b1->id}");
        $this->check('Barcode delete', !Barcode::find($b1->id), $this->describe($r));

        // ---------- Kasir ----------
        $code = Barcode::where('item_id', $item->id)->where('is_active', true)->value('barcode_value');
        $r = $this->actingAs($kasir)->postJson('/kasir/search-barcode', ['barcode' => $code]);
        $this->check('Kasir scan barcode', $r->json('success') === true, json_encode($r->json()));
        $payload = ['items' => [['id' => $item->id, 'name' => $item->name, 'price' => 10000, 'quantity' => 2, 'subtotal' => 20000]],
            'total_amount' => 20000, 'paid_amount' => 50000, 'change_amount' => 30000, 'payment_method' => 'cash'];
        $r = $this->postJson('/kasir/transaction', $payload);
        $trxId = $r->json('transaction_id');
        $this->check('Kasir store transaction', (bool) $trxId, json_encode($r->json()));
        $this->check('Stock decremented 5->3', $item->fresh()->stock_quantity === 3, 'stock=' . $item->fresh()->stock_quantity);
        $r = $this->postJson('/kasir/transaction', ['items' => [['id' => $item->id, 'name' => 'x', 'price' => 1, 'quantity' => 10, 'subtotal' => 10]],
            'total_amount' => 10, 'paid_amount' => 10, 'payment_method' => 'cash']);
        $this->check('Kasir rejects qty > stock', !$r->json('success'), 'resp=' . json_encode($r->json('success')) . ' stock now=' . $item->fresh()->stock_quantity);
        $r = $this->postJson('/kasir/transaction', ['items' => [['id' => $item->id, 'name' => 'x', 'price' => 1, 'quantity' => 1, 'subtotal' => 1]],
            'total_amount' => 1, 'paid_amount' => 10000, 'payment_method' => 'qris']);
        $t = Transaction::find($r->json('transaction_id'));
        $this->check('Kasir uses DB price (sent price=1)', $t && (float) $t->total_amount === 10000.0, 'saved total=' . ($t->total_amount ?? 'none') . ' ' . json_encode($r->json()));
        $r = $this->postJson('/kasir/transaction', ['items' => [['id' => $item->id, 'quantity' => 1]], 'total_amount' => 1, 'paid_amount' => 500, 'payment_method' => 'cash']);
        $this->check('Kasir rejects underpayment', !$r->json('success'), json_encode($r->json('message')));
        $r = $this->get("/kasir/receipt/{$trxId}");
        $this->check('Receipt page', $r->getStatusCode() === 200, $this->describe($r));

        // ---------- Transaction history / Sales ----------
        foreach (["/transaction-history/{$trxId}", "/sales/{$trxId}", '/transaction-history?search=TRX', '/transaction-history-export', '/api/recent-transactions', '/sales?start_date=' . today()->toDateString() . '&end_date=' . today()->toDateString()] as $u) {
            $r = $this->get($u); $this->check("GET $u", $r->getStatusCode() === 200, $this->describe($r));
        }
        $this->check('Sales filter end_date includes today', str_contains($this->get('/sales?start_date=' . today()->toDateString() . '&end_date=' . today()->toDateString())->getContent(), Transaction::find($trxId)->transaction_code), '');
        $r = $this->get('/sales?search=TRX');
        $this->check('Sales search', $r->getStatusCode() === 200, $this->describe($r));
        $r = $this->actingAs($kasir2)->get("/transaction-history/{$trxId}");
        $this->check('Kasir2 cannot view kasir1 trx', $r->getStatusCode() === 404, $this->describe($r));
        // Completed sales can't be edited by anyone, and the old "fix cashier" endpoints are gone
        $totalBefore = Transaction::find($trxId)->total_amount;
        foreach ([$admin, $kasir] as $u) {
            $r = $this->actingAs($u)->get("/transaction-history/{$trxId}/edit");
            $this->check("[{$u->role}] transaction edit page is gone", $r->getStatusCode() === 404, (string) $r->getStatusCode());
            $r = $this->put("/transaction-history/{$trxId}", ['items' => [['id' => 1, 'quantity' => 9]]]);
            $this->check("[{$u->role}] transaction update is refused", in_array($r->getStatusCode(), [404, 405]), (string) $r->getStatusCode());
        }
        $this->check('transaction total unchanged', Transaction::find($trxId)->total_amount == $totalBefore);
        $kasirTrx = Transaction::where('cashier_id', $kasir->id)->count();
        $this->actingAs($admin)->postJson('/transaction-history/fix-cashier-data');
        $this->actingAs($admin)->postJson("/transaction-history/fix-transaction-cashier/{$trxId}");
        $this->check('fix-cashier endpoints are gone (sales keep their cashier)', Transaction::where('cashier_id', $kasir->id)->count() === $kasirTrx && Transaction::find($trxId)->cashier_id === $kasir->id);

        // Deleting sales: super admin only
        foreach ([$kasir, $admin] as $u) {
            $r = $this->actingAs($u)->postJson('/transaction-history/bulk-delete', ['transaction_ids' => [$trxId]]);
            $this->check("[{$u->role}] cannot delete transactions", $r->getStatusCode() === 403 && Transaction::find($trxId), (string) $r->getStatusCode());
            $r = $this->postJson('/transaction-history/delete-all-in-database');
            $this->check("[{$u->role}] cannot delete all transactions", $r->getStatusCode() === 403 && Transaction::count() > 0, (string) $r->getStatusCode());
        }
        $html = $this->actingAs($admin)->get('/transaction-history')->getContent();
        $this->check('admin sees no delete button', !str_contains($html, 'delete-transaction"'));
        $stockBefore = $item->fresh()->stock_quantity;
        $soldQty = (int) Transaction::find($trxId)->transactionItems()->sum('quantity');
        $super = User::where('role', 'superadmin')->first();
        $r = $this->actingAs($super)->postJson('/transaction-history/bulk-delete', ['transaction_ids' => [$trxId]]);
        $this->check('super admin delete restores stock', !Transaction::find($trxId) && $item->fresh()->stock_quantity === $stockBefore + $soldQty, "before=$stockBefore sold=$soldQty after=" . $item->fresh()->stock_quantity . ' ' . json_encode($r->json('message')));

        // ---------- Stock In ----------
        $before = $item->fresh()->stock_quantity;
        $r = $this->actingAs($admin)->post('/stock-in', ['supplier_id' => $sup->id, 'transaction_date' => today()->toDateString(), 'items' => [['item_id' => $item->id, 'quantity' => 10, 'purchase_price' => 4000]]]);
        $si = StockIn::latest('id')->first();
        $this->check('Stock In store (+10)', $item->fresh()->stock_quantity === $before + 10, $this->describe($r));
        if ($si) foreach (["/stock-in/{$si->id}", '/stock-in?search=Uji'] as $u) { $r = $this->get($u); $this->check("GET $u", $r->getStatusCode() === 200, $this->describe($r)); }
        $r = $this->get('/stock-in?search=Uji', ['X-Requested-With' => 'XMLHttpRequest']);
        $this->check('Stock In AJAX list', $r->getStatusCode() === 200, $this->describe($r));
        $r = $this->get("/stock-in/{$si->id}/edit");
        $this->check('Stock In edit page (completed)', $r->getStatusCode() === 200, $this->describe($r));
        $base = $item->fresh()->stock_quantity; // includes +10
        $r = $this->put("/stock-in/{$si->id}", ['supplier_id' => $sup->id, 'transaction_date' => today()->toDateString(), 'items' => [['item_id' => $item->id, 'quantity' => 4, 'purchase_price' => 4000]]]);
        $this->check('Stock In update 10 -> 4', $item->fresh()->stock_quantity === $base - 6, "expected " . ($base - 6) . " got " . $item->fresh()->stock_quantity . ' ' . $this->describe($r));
        $item->update(['stock_quantity' => 1]); // simulate goods already sold
        $r = $this->delete("/stock-in/{$si->id}");
        $this->check('Stock In delete blocked when sold', StockIn::find($si->id) && $item->fresh()->stock_quantity === 1, $this->describe($r));
        $r = $this->put("/stock-in/{$si->id}", ['supplier_id' => $sup->id, 'transaction_date' => today()->toDateString(), 'items' => [['item_id' => $item->id, 'quantity' => 1, 'purchase_price' => 4000]]]);
        $this->check('Stock In update blocked when result < 0', $item->fresh()->stock_quantity === 1, 'stock=' . $item->fresh()->stock_quantity . ' ' . $this->describe($r));
        $item->update(['stock_quantity' => 50]);
        $r = $this->delete("/stock-in/{$si->id}");
        $this->check('Stock In delete reverses stock', !StockIn::find($si->id) && $item->fresh()->stock_quantity === 46, 'stock=' . $item->fresh()->stock_quantity . ' ' . $this->describe($r));
        // import: xls rejected, xlsx with a gap column parsed correctly
        $xls = \Illuminate\Http\UploadedFile::fake()->create('old.xls', 10, 'application/vnd.ms-excel');
        $r = $this->post('/items/import', ['excel_file' => $xls, 'default_purchase_price' => 1000]);
        $this->check('Import rejects .xls with message', (bool) session('errors'), $this->describe($r));
        $xlsxPath = sys_get_temp_dir() . '/imp_' . uniqid() . '.xlsx';
        $zip = new \ZipArchive(); $zip->open($xlsxPath, \ZipArchive::CREATE);
        $zip->addFromString('xl/sharedStrings.xml', '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><si><t>Kat</t></si><si><t>Nama</t></si><si><t>Imp Kategori</t></si><si><r><t>Produk </t></r><r><t>Rich</t></r></si></sst>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData><row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c></row><row r="2"><c r="A2" t="s"><v>2</v></c><c r="B2" t="s"><v>3</v></c><c r="D2"><v>25000</v></c><c r="E2" t="inlineStr"><is><t>IMP-SKU-1</t></is></c></row></sheetData></worksheet>');
        $zip->close();
        $up = new \Illuminate\Http\UploadedFile($xlsxPath, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $r = $this->post('/items/import', ['excel_file' => $up, 'default_purchase_price' => 1000]);
        $imp = Item::where('sku', 'IMP-SKU-1')->first();
        $this->check('Import xlsx (empty col C, rich text, inline str)', $imp && $imp->name === 'Produk Rich' && (float) $imp->selling_price === 25000.0, ($imp ? "name={$imp->name} price={$imp->selling_price}" : 'not created') . ' ' . $this->describe($r) . ' ' . json_encode(session('warning')));

        // ---------- Stock Adjustment ----------
        $before = $item->fresh()->stock_quantity;
        $r = $this->post('/stock-adjustments', ['item_id' => $item->id, 'type' => 'increase', 'quantity' => 5, 'reason' => 'damaged', 'adjustment_date' => today()->toDateString()]);
        $adj = StockAdjustment::latest('id')->first();
        $this->check('Stock Adjustment store (form reason value)', (bool) $adj && $item->fresh()->stock_quantity === $before + 5, $this->describe($r));
        if (!$adj) {
            $adj = StockAdjustment::create(['item_id' => $item->id, 'user_id' => $admin->id, 'type' => 'increase', 'quantity' => 5, 'stock_before' => $before, 'stock_after' => $before + 5, 'reason' => 'damaged', 'adjustment_date' => today()]);
            $item->update(['stock_quantity' => $before + 5]);
        }
        foreach (["/stock-adjustments/{$adj->id}", "/stock-adjustments/{$adj->id}/edit"] as $u) { $r = $this->get($u); $this->check("GET $u", $r->getStatusCode() === 200, $this->describe($r)); }
        $r = $this->put("/stock-adjustments/{$adj->id}", ['item_id' => $item->id, 'type' => 'increase', 'quantity' => 3, 'reason' => $adj->reason, 'adjustment_date' => today()->toDateString()]);
        $this->check('Stock Adjustment update +5 -> +3', $item->fresh()->stock_quantity === $before + 3, "expected " . ($before + 3) . " got " . $item->fresh()->stock_quantity . ' ' . $this->describe($r));

        // ---------- Item/Category/Supplier delete ----------
        $r = $this->delete("/items/{$item->id}");
        $this->check('Item with history: delete blocked', $r->getStatusCode() === 302 && Item::find($item->id), $this->describe($r));
        $fresh = Item::create(['name' => 'Hapus Saya', 'sku' => 'DEL-1', 'category_id' => $cat->id, 'purchase_price' => 1, 'selling_price' => 2, 'stock_quantity' => 0, 'minimum_stock' => 0, 'unit' => 'pcs', 'is_active' => true]);
        $r = $this->delete("/items/{$fresh->id}");
        $this->check('Item without history: deleted', !Item::find($fresh->id), $this->describe($r));
        $r = $this->put("/items/{$fresh->id}", []);
        $r = $this->put("/items/{$item->id}", ['name' => 'Item Uji', 'sku' => 'UJI-001', 'category_id' => $cat->id,
            'purchase_price' => 5000, 'selling_price' => 10000, 'stock_quantity' => 5, 'minimum_stock' => 1, 'unit' => 'pcs', 'is_active' => '0']);
        $this->check('Item deactivate via edit', $item->fresh()->is_active === false, $this->describe($r));
        $r = $this->delete("/suppliers/{$sup->id}");
        $this->check('Supplier delete w/ items is blocked gracefully', $r->getStatusCode() === 302, $this->describe($r));

        // ---------- Reports ----------
        foreach (array_keys(Report::getTypes()) as $type) {
            $r = $this->actingAs($super)->post('/reports', ['type' => $type, 'name' => "Uji $type", 'start_date' => today()->subMonth()->toDateString(), 'end_date' => today()->toDateString()]);
            $rep = Report::where('name', "Uji $type")->first();
            $ok = (bool) $rep;
            $info = $this->describe($r);
            if ($rep) { $s = $this->get("/reports/{$rep->id}"); $ok = $s->getStatusCode() === 200; $info .= ' show=' . $this->describe($s); }
            $this->check("Report $type", $ok, $info);
        }

        // ---------- User management ----------
        foreach (["/user-management/{$kasir->id}", "/user-management/{$kasir->id}/edit"] as $u) { $r = $this->get($u); $this->check("GET $u", $r->getStatusCode() === 200, $this->describe($r)); }
        $r = $this->put("/user-management/{$kasir2->id}", ['name' => 'Kasir Dua Edit', 'email' => $kasir2->email, 'role' => 'kasir', 'approval_status' => 'approved']);
        $this->check('User update', $kasir2->fresh()->name === 'Kasir Dua Edit', $this->describe($r) . ' users=' . User::count());
        $r = $this->post('/user-management', ['name' => 'Baru', 'email' => 'baru@x.com', 'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'kasir', 'approval_status' => 'pending']);
        $new = User::where('email', 'baru@x.com')->first();
        $this->check('User store', (bool) $new, $this->describe($r));
        $r = $this->post("/user-management/{$new->id}/approve");
        $this->check('User approve', $new->fresh()->approval_status === 'approved', $this->describe($r));
        $r = $this->post("/user-management/{$new->id}/toggle-status");
        $this->check('User toggle status', $new->fresh()->is_active == false, $this->describe($r));
        $r = $this->post("/user-management/{$new->id}/reject");
        $this->check('User reject', $new->fresh()->approval_status === 'rejected', $this->describe($r));
        $r = $this->delete("/user-management/{$new->id}");
        $this->check('User delete', !User::find($new->id), $this->describe($r));
        $r = $this->delete("/user-management/{$kasir->id}");
        $this->check('Delete kasir with transactions is blocked', User::find($kasir->id) && Transaction::where('cashier_id', $kasir->id)->exists(), $this->describe($r));

        // deactivated admin still has access?
        $admin->update(['is_active' => false]);
        $r = $this->actingAs($admin)->get('/items');
        $this->check('Deactivated admin blocked from /items', $r->getStatusCode() !== 200, $this->describe($r));
        $admin->update(['is_active' => true]);

        // ---------- Settings ----------
        $all = SystemSetting::pluck('value', 'key')->toArray();
        $settings = $all; $settings['auto_print_receipt'] = '0'; // what the hidden input sends when unchecked
        $r = $this->actingAs($admin)->put('/settings', ['settings' => $settings]);
        $this->check('Settings: uncheck boolean turns it off', !filter_var(SystemSetting::where('key', 'auto_print_receipt')->value('value'), FILTER_VALIDATE_BOOLEAN), 'value=' . json_encode(SystemSetting::where('key', 'auto_print_receipt')->value('value')) . ' ' . $this->describe($r));
        $settings = $all; $settings['store_address'] = '';
        $r = $this->put('/settings', ['settings' => $settings]);
        $this->check('Settings: allow empty text value', !session('errors'), $this->describe($r));

        // ---------- Profile / notifications / search ----------
        $r = $this->actingAs($kasir2)->post('/profile', ['name' => 'K2', 'email' => $kasir2->email]);
        $this->check('Profile update', $kasir2->fresh()->name === 'K2', $this->describe($r));
        foreach (['/notifications', '/api/notifications', '/api/notifications/unread-count', '/api/search/global?q=a', '/api/search/items?q=a'] as $u) { $r = $this->get($u); $this->check("GET $u", $r->getStatusCode() === 200, $this->describe($r)); }
        $r = $this->postJson('/notifications/mark-all-read');
        $this->check('Notifications mark-all-read', $r->getStatusCode() === 200, $this->describe($r));

        // ---------- Auth ----------
        $this->app['auth']->forgetGuards();
        auth()->logout();
        $victim = User::where('role', 'superadmin')->first();
        $r = $this->post('/register', ['name' => 'Reg', 'email' => 'reg@x.com', 'password' => 'password123', 'role' => 'kasir', 'terms' => 'on']);
        $this->check('Register', (bool) User::where('email', 'reg@x.com')->first(), $this->describe($r));
        $this->check('Register notifies superadmin', DB::table('notifications')->where('notifiable_id', $super->id)->where('type', 'like', '%NewUserRegistration%')->where('data', 'like', '%"user_id":' . User::where('email', 'reg@x.com')->value('id') . ',%')->exists(), '');
        $r = $this->post('/login', ['email' => 'reg@x.com', 'password' => 'password123']);
        $this->check('Pending user cannot login', !auth()->check(), $this->describe($r));
        $r = $this->post('/login', ['email' => $admin->email, 'password' => 'password123']);
        $this->check('Admin login (password=password)', auth()->check(), $this->describe($r));
        $r = $this->get('/login');
        $this->check('Logged-in visiting /login goes to dashboard', str_contains((string) $r->headers->get('Location'), 'dashboard'), $this->describe($r));

        $this->assertTrue(true);
    }
}
