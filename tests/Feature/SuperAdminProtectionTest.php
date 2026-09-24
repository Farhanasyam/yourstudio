<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminProtectionTest extends TestCase
{
    use RefreshDatabase;

    /** Every run starts from the seeders (users, 102 items, settings) */
    protected $seed = true;



    public function test_superadmin_cannot_be_deactivated_or_deleted(): void
    {
        $this->run_all();
        $this->assertChecksPassed();
    }

    private function run_all(): void
    {
        $super = User::where('role', 'superadmin')->first();
        // a second super admin to act on (the acting one can't target itself anyway)
        $other = User::create(['name' => 'Super Dua', 'email' => 'super2@x.com', 'password' => Hash::make('password123'), 'role' => 'superadmin', 'approval_status' => 'approved', 'is_active' => true]);
        $alive = fn () => ($u = User::find($other->id)) && $u->is_active && $u->approval_status === 'approved' && $u->role === 'superadmin';

        // --- through the UI routes ---
        $r = $this->actingAs($super)->post("/user-management/{$other->id}/toggle-status");
        $this->check('toggle-status on super admin refused', $alive() && session('error') === 'Super Admin tidak dapat dinonaktifkan.', (string) session('error'));
        $r = $this->delete("/user-management/{$other->id}");
        $this->check('delete super admin refused', $alive() && session('error') === 'Super Admin tidak dapat dihapus.', (string) session('error'));
        $r = $this->post("/user-management/{$other->id}/reject");
        $this->check('reject super admin refused', $alive() && session('error') === 'Super Admin tidak dapat ditolak.', (string) session('error'));
        $r = $this->put("/user-management/{$other->id}", ['name' => 'x', 'email' => 'super2@x.com', 'role' => 'kasir', 'approval_status' => 'rejected', 'is_active' => 0]);
        $this->check('update/demote super admin refused', $alive(), (string) $r->getStatusCode());
        $this->check('super admin not in user list', !str_contains($this->get('/user-management')->getContent(), 'super2@x.com'));

        // --- any other code path (model level) ---
        User::find($other->id)->update(['is_active' => false, 'approval_status' => 'rejected', 'role' => 'kasir']);
        $this->check('model update cannot deactivate/demote', $alive());
        $threw = false;
        try { User::find($other->id)->delete(); } catch (\RuntimeException $e) { $threw = true; }
        $this->check('model delete throws and keeps the row', $threw && $alive());
        User::find($other->id)->update(['name' => 'Nama Baru']);
        $this->check('other edits (name) still allowed', User::find($other->id)->name === 'Nama Baru');
        $this->check('super admin can still log in', $alive() && User::find($other->id)->canLogin());

        // --- regular users unaffected ---
        $kasir = User::create(['name' => 'K Tmp', 'email' => 'ktmp@x.com', 'password' => Hash::make('password123'), 'role' => 'kasir', 'approval_status' => 'approved', 'is_active' => true]);
        $this->actingAs($super)->post("/user-management/{$kasir->id}/toggle-status");
        $this->check('kasir can still be deactivated', User::find($kasir->id)->is_active === false);
        $this->delete("/user-management/{$kasir->id}");
        $this->check('kasir without history can still be deleted', !User::find($kasir->id));
    }
}
