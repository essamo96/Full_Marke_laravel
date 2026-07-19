<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    use HasFactory, \App\Traits\EncryptsRouteKey;

    protected $fillable = [
        'registration_number', 'student_id', 'subject_id', 'group_id',
        'fee_snapshot', 'amount_paid', 'status',
        'activated_at', 'admin_notes',
    ];

    protected $casts = [
        'fee_snapshot' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'activated_at' => 'datetime',
    ];

    /**
     * Get the remaining amount (fee_snapshot - amount_paid).
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float)($this->fee_snapshot ?? 0) - (float)($this->amount_paid ?? 0));
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function paymentItems(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public static function generateNumber(): string
    {
        return 'REG-' . date('Ym') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
    }
}
