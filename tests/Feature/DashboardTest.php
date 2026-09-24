<?php

namespace Tests\Feature;

use App\Models\{Item, StockIn, Supplier, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** Every run starts from the seeders (users, 102 items, settings) */
    protected $seed = true;



    public function test_dashboard_cards_links_and_counts(): void
    {
        $this->run_all();
        $this->assertChecksPassed();
    }

    private function hrefs(string $html): array
    {
        preg_match_all('/href="([^"]+)"/', $html, $m);
        return array_map('html_entity_decode', $m[1]);
    }

    private function run_all(): void
    {
        // known data: 3 low (1 of them out of stock), 1 inactive supplier
        $cat = DB::table('categories')->value('id');
        Item::query()->update(['stock_quantity' => 100, 'minimum_stock' => 5]);
        Item::create(['name' => 'ZZ Habis', 'sku' => 'D-1', 'category_id' => $cat, 'purchase_price' => 1, 'selling_price' => 2, 'stock_quantity' => 0, 'minimum_stock' => 5, 'unit' => 'pcs', 'is_active' => true]);
        Item::create(['name' => 'ZZ Tipis', 'sku' => 'D-2', 'category_id' => $cat, 'purchase_price' => 1, 'selling_price' => 2, 'stock_quantity' => 3, 'minimum_stock' => 5, 'unit' => 'pcs', 'is_active' => true]);
        Item::create(['name' => 'ZZ Pas', 'sku' => 'D-3', 'category_id' => $cat, 'purchase_price' => 1, 'selling_price' => 2, 'stock_quantity' => 5, 'minimum_stock' => 5, 'unit' => 'pcs', 'is_active' => true]);
        Supplier::query()->update(['is_active' => true]);
        Supplier::create(['name' => 'ZZ Nonaktif', 'is_active' => false]);
        $activeSuppliers = Supplier::where('is_active', true)->count();
        $recent = StockIn::where('transaction_date', '>=', now()->subDays(30)->toDateString())->count();

        $roles = [
            'superadmin' => User::where('role', 'superadmin')->first(),
            'admin' => User::where('role', 'admin')->where('approval_status', 'approved')->first(),
            'kasir' => User::where('role', 'kasir')->where('approval_status', 'approved')->first(),
        ];
        $today = now()->toDateString();

        foreach ($roles as $role => $u) {
            $r = $this->actingAs($u)->get('/dashboard');
            $html = $r->getContent();
            $links = $this->hrefs($html);
            $has = fn ($needle) => (bool) array_filter($links, fn ($l) => str_contains($l, $needle));
            $this->check("[$role] dashboard 200", $r->getStatusCode() === 200, (string) $r->getStatusCode());
            $this->check("[$role] card counts: 3 low stock, 1 habis", (bool) preg_match('/>3<\/span> low stock/', $html) && (bool) preg_match('/>1<\/span> habis/', $html));
            $this->check("[$role] active suppliers = $activeSuppliers", (bool) preg_match('/Active Suppliers<\/p>\s*(<\/a>)?\s*<h5 class="font-weight-bolder">' . $activeSuppliers . '</', $html));
            $this->check("[$role] deliveries (30 hari) = $recent", str_contains($html, '>' . $recent . '</span> deliveries (30 hari)'));
            $this->check("[$role] low stock list shows habis first", strpos($html, 'ZZ Habis') !== false && strpos($html, 'ZZ Habis') < strpos($html, 'ZZ Tipis') && str_contains($html, '>Habis</span>'));
            $this->check("[$role] Today's Sales card -> sales (today)", $has("/sales?end_date=$today&start_date=$today") || $has("/sales?start_date=$today&end_date=$today"));
            $admin = $role !== 'kasir';
            $this->check("[$role] Items card link " . ($admin ? 'present' : 'hidden'), $has('/items') === $admin);
            $this->check("[$role] low_stock / out_of_stock links " . ($admin ? 'present' : 'hidden'), ($has('stock_status=low_stock') && $has('stock_status=out_of_stock')) === $admin);
            $this->check("[$role] Suppliers + stock-in links " . ($admin ? 'present' : 'hidden'), ($has('/suppliers?is_active=1') && $has('/stock-in?start_date=')) === $admin);
            $isSuper = $role === 'superadmin';
            $this->check("[$role] Users card link " . ($isSuper ? 'present' : 'hidden'), ($has('/user-management') && $has('approval_status=pending')) === $isSuper);
            $this->check("[$role] Reports quick action " . ($isSuper ? 'present' : 'hidden'), $has('/reports') === $isSuper);
            $this->check("[$role] New Sale goes straight to kasir", $has('/kasir'));
            // every link on the dashboard must be openable by this role
            $bad = [];
            foreach (array_unique($links) as $l) {
                if (!str_starts_with($l, 'http://localhost/') || str_contains($l, '/logout')) continue;
                $path = substr($l, strlen('http://localhost'));
                $s = $this->actingAs($u)->get($path)->getStatusCode();
                if ($s !== 200) $bad[] = "$path=$s";
            }
            $this->check("[$role] every dashboard link opens (200)", !$bad, implode(', ', $bad));
        }

        // targets actually filter
        $admin = $roles['admin'];
        $r = $this->actingAs($admin)->get('/items?stock_status=out_of_stock')->getContent();
        $this->check('items?out_of_stock lists only habis item', str_contains($r, 'ZZ Habis') && !str_contains($r, 'ZZ Tipis'));
        $r = $this->get('/items?stock_status=low_stock')->getContent();
        $this->check('items?low_stock lists all 3', str_contains($r, 'ZZ Habis') && str_contains($r, 'ZZ Tipis') && str_contains($r, 'ZZ Pas'));
        $r = $this->get('/suppliers?is_active=1')->getContent();
        $this->check('suppliers?is_active=1 hides inactive', !str_contains($r, 'ZZ Nonaktif'));
        $r = $this->actingAs($roles['superadmin'])->get('/user-management?approval_status=pending')->getContent();
        $this->check('user-management?pending lists pending user', str_contains($r, 'admin.pending@yourstudio.com'));
    }
}
