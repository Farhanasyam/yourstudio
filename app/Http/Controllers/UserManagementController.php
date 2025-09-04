<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Notifications\UserApprovedNotification;
use App\Notifications\UserRejectedNotification;
use App\Notifications\NewUserRegistrationNotification;

class UserManagementController extends Controller
{
    public function __construct()
    {
        // Middleware handled by routes - no need for constructor middleware
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query()->where('role', '!=', 'superadmin');

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by approval status
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->with('approvedBy')->latest()->paginate(10);

        return view('pages.user-management.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('pages.user-management.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'kasir'])],
            'is_active' => 'boolean',
            'approval_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        if ($validated['approval_status'] === 'approved') {
            $validated['approved_at'] = now();
            $validated['approved_by'] = Auth::id();
        }

        $user = User::create($validated);

        // Kirim notifikasi ke super admin jika user pending
        if ($validated['approval_status'] === 'pending') {
            $superAdmins = User::where('role', 'superadmin')->get();
            foreach ($superAdmins as $superAdmin) {
                $superAdmin->notify(new NewUserRegistrationNotification($user));
            }
        }

        return redirect()->route('user-management.index')
                        ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        if ($user->isSuperAdmin()) {
            abort(404);
        }

        return view('pages.user-management.show', compact('user'));
    }

    /**
     * Show the form for editing user
     */
    public function edit(User $user)
    {
        if ($user->isSuperAdmin()) {
            abort(404);
        }

        return view('pages.user-management.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        if ($user->isSuperAdmin()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'kasir'])],
            'is_active' => 'boolean',
            'approval_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle approval status change
        $oldStatus = $user->approval_status;
        if ($validated['approval_status'] !== $oldStatus) {
            if ($validated['approval_status'] === 'approved') {
                $validated['approved_at'] = now();
                $validated['approved_by'] = Auth::id();
            } else {
                $validated['approved_at'] = null;
                $validated['approved_by'] = null;
            }
        }

        $user->update($validated);

        return redirect()->route('user-management.index')
                        ->with('success', 'User berhasil diupdate.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        if ($user->isSuperAdmin()) {
            abort(404);
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('user-management.index')
                            ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('user-management.index')
                        ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Approve user
     */
    public function approve(User $user)
    {
        if ($user->isSuperAdmin()) {
            abort(404);
        }

        $user->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'is_active' => true,
        ]);

        // Kirim notifikasi ke user yang disetujui
        $user->notify(new UserApprovedNotification($user, Auth::user()));

        return redirect()->back()
                        ->with('success', 'User berhasil disetujui.');
    }

    /**
     * Reject user
     */
    public function reject(User $user)
    {
        if ($user->isSuperAdmin()) {
            abort(404);
        }

        $user->update([
            'approval_status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
            'is_active' => false,
        ]);

        // Kirim notifikasi ke user yang ditolak
        $user->notify(new UserRejectedNotification($user, Auth::user()));

        return redirect()->back()
                        ->with('success', 'User berhasil ditolak.');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        if ($user->isSuperAdmin()) {
            abort(404);
        }

        if ($user->id === Auth::id()) {
            return redirect()->back()
                            ->with('error', 'Anda tidak dapat mengubah status akun sendiri.');
        }

        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
                        ->with('success', "User berhasil {$status}.");
    }

    /**
     * Get pending users count for dashboard
     */
    public function getPendingCount()
    {
        return User::where('approval_status', 'pending')->count();
    }
}