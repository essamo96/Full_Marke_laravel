<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasRoles, Notifiable;

    protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'role',
        'photo',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function confirmedPayments()
    {
        return $this->hasMany(Payment::class, 'confirmed_by');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public static function applyFilters($query, ?array $filters = null)
    {
        if (!$filters) {
            return $query;
        }

        return $query->when(!empty($filters['search_value']), function ($q) use ($filters) {
            $searchValue = $filters['search_value'];
            $q->where(function ($q2) use ($searchValue) {
                $q2->where('name', 'like', "%{$searchValue}%")
                   ->orWhere('email', 'like', "%{$searchValue}%");
                
                if (\Illuminate\Support\Facades\Schema::hasColumn('admins', 'mobile')) {
                    $q2->orWhere('mobile', 'like', "%{$searchValue}%");
                } elseif (\Illuminate\Support\Facades\Schema::hasColumn('admins', 'phone')) {
                    $q2->orWhere('phone', 'like', "%{$searchValue}%");
                }
            });
        })
        ->when(isset($filters['status']) && $filters['status'] !== '', function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        })
        ->when(!empty($filters['role']), function ($q) use ($filters) {
            $q->whereHas('roles', function ($q2) use ($filters) {
                $q2->where('name', $filters['role']);
            });
        });
    }
}
