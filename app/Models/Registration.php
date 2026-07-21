<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    use HasFactory, \App\Traits\EncryptsRouteKey;

    public const GROUP_STATUS_ACTIVE = 'active';
    public const GROUP_STATUS_SUSPENDED = 'suspended';
    public const GROUP_STATUS_DEFERRED = 'deferred';

    public const GROUP_STATUSES = [
        self::GROUP_STATUS_ACTIVE,
        self::GROUP_STATUS_SUSPENDED,
        self::GROUP_STATUS_DEFERRED,
    ];

    /**
     * Translated label for a group_status value, e.g. __('app.group_status_active').
     */
    public static function groupStatusLabel(?string $value): string
    {
        return $value ? __('app.group_status_' . $value) : '-';
    }

    protected $fillable = [
        'registration_number', 'student_id', 'subject_id', 'group_id',
        'fee_snapshot', 'amount_paid', 'status', 'group_status',
        'activated_at', 'admin_notes',
    ];

    protected $casts = [
        'fee_snapshot' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'activated_at' => 'datetime',
    ];

    public function getGroupStatusLabelAttribute(): string
    {
        return self::groupStatusLabel($this->group_status);
    }

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
