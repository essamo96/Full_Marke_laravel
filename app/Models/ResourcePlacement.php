<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * "Where in the curriculum is this resource shown?" - one row per
 * (resource, lesson | unit | general).
 *
 * The resource itself (file / link / video) lives once in subject_resources; any
 * number of placements point at it. That is what makes "upload once, assign anywhere"
 * possible: showing a video in a second lesson adds a placement, not a second upload.
 *
 *   target_key  L<id>  inside lesson <id>
 *               U<id>  directly under unit <id>
 *               G      general: subject level, outside any unit
 *
 * A placement has its own kill switch (is_active) and is soft-deleted when removed,
 * so detaching a resource from a lesson can be undone.
 */
class ResourcePlacement extends Model
{
    use SoftDeletes;

    public const GENERAL = 'G';

    protected $table = 'resource_placements';

    protected $fillable = [
        'subject_resource_id', 'educational_unit_id', 'educational_lesson_id',
        'target_key', 'sort_order', 'is_active', 'created_by_type', 'created_by_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        // target_key is derived, never trusted from the caller: it is what the
        // unique index uses to stop the same resource landing twice in one place.
        static::saving(function (self $placement) {
            $placement->target_key = self::keyFor(
                $placement->educational_unit_id ? (int) $placement->educational_unit_id : null,
                $placement->educational_lesson_id ? (int) $placement->educational_lesson_id : null,
            );
        });
    }

    public static function keyFor(?int $unitId, ?int $lessonId): string
    {
        if ($lessonId) {
            return 'L'.$lessonId;
        }

        return $unitId ? 'U'.$unitId : self::GENERAL;
    }

    /**
     * @return array{type: string, id: int|null}  type is lesson | unit | general
     */
    public static function parseKey(string $key): array
    {
        if ($key === self::GENERAL) {
            return ['type' => 'general', 'id' => null];
        }

        $id = (int) substr($key, 1);

        return match ($key[0] ?? '') {
            'L' => ['type' => 'lesson', 'id' => $id],
            'U' => ['type' => 'unit', 'id' => $id],
            default => throw new \InvalidArgumentException("Bad placement key [{$key}]"),
        };
    }

    public function isGeneral(): bool
    {
        return $this->target_key === self::GENERAL;
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(SubjectResource::class, 'subject_resource_id')->withTrashed();
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(EducationalLesson::class, 'educational_lesson_id')->withTrashed();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(EducationalUnit::class, 'educational_unit_id')->withTrashed();
    }

    /** The unit this placement ends up in, whether it points at a lesson or at the unit directly. */
    public function resolvedUnit(): ?EducationalUnit
    {
        return $this->lesson?->unit ?? $this->unit;
    }

    public function scopeActive($query)
    {
        return $query->where($this->qualifyColumn('is_active'), true);
    }
}
