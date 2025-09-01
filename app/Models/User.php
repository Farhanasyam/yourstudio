<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'approval_status',
        'approved_at',
        'approved_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // Role check methods
    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isKasir()
    {
        return $this->role === 'kasir';
    }

    public function canManageItems()
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    public function canViewReports()
    {
        return $this->role === 'superadmin';
    }

    // Approval status methods
    public function isPending()
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected()
    {
        return $this->approval_status === 'rejected';
    }

    public function canLogin()
    {
        return $this->is_active && $this->isApproved();
    }

    // Relationships
    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_id');
    }



    public function createdBarcodes()
    {
        return $this->hasMany(Barcode::class, 'created_by');
    }

    // Approval relationship
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvedUsers()
    {
        return $this->hasMany(User::class, 'approved_by');
    }
}
