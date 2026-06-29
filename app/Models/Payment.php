<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'payment_number', 'student_id', 'payment_method_id', 'total_amount',
        'receipt_image', 'notes', 'status', 'confirmed_by', 'confirmed_at',
        'rejection_reason', 'admin_notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'confirmed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public static function generateNumber(): string
    {
        return 'PAY-'.now()->format('Ymd').'-'.str_pad((string) (self::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
    }
}
