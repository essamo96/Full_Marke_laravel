<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    protected $fillable = [
        'registration_number', 'student_id', 'subject_id', 'group_id',
        'total_fee', 'amount_paid', 'registration_status', 'payment_status',
        'activated_at', 'admin_notes',
    ];

    protected $casts = [
        'total_fee' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'activated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function paymentItems(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_fee - (float) $this->amount_paid);
    }

    public function hasResourceAccess(): bool
    {
        return $this->registration_status === 'active';
    }

    public static function generateNumber(): string
    {
        return 'REG-'.now()->format('Ymd').'-'.str_pad((string) (self::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
    }
}
