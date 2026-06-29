<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'parent_id',
        'full_name_ar',
        'full_name_en',
        'national_id',
        'is_child',
        'guardian_id',
        'email',
        'phone',
        'image',
        'date_of_birth',
        'gender',
        'address',
        'branch_id',
        'major_profession',
        'health_information',
        'password',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'status' => 'integer',
            'is_child' => 'boolean',
        ];
    }

    protected static function newFactory()
    {
        return StudentFactory::new();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function cartItems()
    {
        return CartItem::query()->forUser($this->id, 'student');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
