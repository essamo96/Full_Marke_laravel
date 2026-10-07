<?php

namespace App\Models;

use App\Support\Library\StudentVisibility;
use App\Traits\EncryptsRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The central repository of the resource library: one row per physical file or link,
 * however many groups / units / lessons it is shown in ("upload once, assign anywhere").
 *
 *  - WHERE it appears        -> placements()  (lesson, unit or general)
 *  - WHO sees it             -> is_shared + groupLinks()  (active link = granted, inactive = paused)
 *  - kill switch             -> is_active  (off = hidden from every student, nothing else is touched)
 *  - single student blocked  -> contentExclusions()
 */
class SubjectResource extends Model
{
    use HasFactory, SoftDeletes, EncryptsRouteKey;
    use Concerns\HasEducationalContentVisibility;

    protected $table = 'subject_resources';

    protected $fillable = [
        'subject_id', 'educational_lesson_id', 'category', 'title', 'type', 'url', 'description', 'is_active', 'sort_order',
        'processing_status', 'hls_path', 'encryption_key_path', 'duration_seconds', 'original_filename', 'processing_error',
        'allow_download', 'is_shared',
        'size_bytes', 'mime_type', 'content_hash',
        'created_by_type', 'created_by_id',
        'deactivated_at', 'deactivated_by_type', 'deactivated_by_id',
        'deleted_by', 'deleted_by_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
        'allow_download' => 'boolean',
        'size_bytes' => 'integer',
        'deactivated_at' => 'datetime',
    ];

    // ---------------------------------------------------------------- relations

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * The "primary" lesson, kept for legacy readers. The real answer to
     * "where is this resource shown?" is placements().
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(EducationalLesson::class, 'educational_lesson_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(VideoAccessLog::class);
    }

    /**
     * Who deleted the resource: an admin or a teacher (deleted_by holds the id, deleted_by_type says
     * which table). Older rows have no type and were always deleted by an admin.
     */
    public function getDeleterAttribute(): Admin|Teacher|null
    {
        if (! $this->deleted_by) {
            return null;
        }

        return $this->deleted_by_type === 'teacher'
            ? Teacher::find($this->deleted_by)
            : Admin::find($this->deleted_by);
    }

    /** Where the resource appears in the curriculum (live rows only, in display order). */
    public function placements(): HasMany
    {
        return $this->hasMany(ResourcePlacement::class, 'subject_resource_id')->orderBy('sort_order')->orderBy('id');
    }

    /** Lessons the resource is placed in. */
    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(EducationalLesson::class, 'resource_placements', 'subject_resource_id', 'educational_lesson_id')
            ->wherePivotNull('deleted_at')
            ->withPivot(['id', 'sort_order', 'is_active']);
    }

    /** Units the resource is placed in directly (without a lesson). */
    public function units(): BelongsToMany
    {
        return $this->belongsToMany(EducationalUnit::class, 'resource_placements', 'subject_resource_id', 'educational_unit_id')
            ->wherePivotNull('deleted_at')
            ->withPivot(['id', 'sort_order', 'is_active']);
    }

    /** Every pivot row of the group audience, active and paused alike. */
    public function groupLinks(): HasMany
    {
        return $this->hasMany(ResourceGroupLink::class, 'subject_resource_id');
    }

    /** Groups the resource is linked to (active and paused). Use syncWithoutDetaching() to add without duplicating. */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_subject_resource')
            ->wherePivotNull('deleted_at')
            ->withPivot(['is_active', 'created_by_type', 'created_by_id'])
            ->withTimestamps();
    }

    /** Groups that currently see the resource through an explicit link. */
    public function activeGroups(): BelongsToMany
    {
        return $this->groups()->wherePivot('is_active', true);
    }

    /** Groups linked but paused (kill switch for one group). */
    public function pausedGroups(): BelongsToMany
    {
        return $this->groups()->wherePivot('is_active', false);
    }

    // ------------------------------------------------------------------ scopes

    public function scopeActive($query)
    {
        return $query->where($this->qualifyColumn('is_active'), true);
    }

    /**
     * Resources a student of the given group(s) may see right now: kill switch on,
     * not excluded, audience matches, and at least one open placement.
     * This is the single query every student-facing surface goes through.
     *
     * @param  int|array<int>|null  $groupIds  the student's group(s) in this subject
     */
    public function scopeVisibleToStudent(Builder $query, int $studentId, int|array|null $groupIds): Builder
    {
        StudentVisibility::constrainResources(
            $query,
            $this->getTable(),
            $studentId,
            array_values(array_filter((array) $groupIds)),
        );

        return $query;
    }

    /**
     * What a whole group sees - the student scope without any per-student rows.
     * Powers the "what does group X see?" views of admins and teachers.
     */
    public function scopeVisibleToGroup(Builder $query, int $groupId): Builder
    {
        return $this->scopeVisibleToStudent($query, 0, [$groupId]);
    }

    /** Resources that have no live audience at all: nobody sees them yet. */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_shared'), false)
            ->whereDoesntHave('groupLinks', fn ($links) => $links->where('is_active', true));
    }

    /** Resources placed nowhere: invisible until someone places them. */
    public function scopeUnplaced(Builder $query): Builder
    {
        return $query->whereDoesntHave('placements');
    }

    // ----------------------------------------------------------------- helpers

    public function isVideo(): bool
    {
        return $this->type === 'video' && ! preg_match('#^https?://#i', (string) $this->url);
    }

    public function isReady(): bool
    {
        return $this->processing_status === 'ready';
    }

    public function isExternalLink(): bool
    {
        return (bool) preg_match('#^https?://#i', (string) $this->url);
    }

    public function isImage(): bool
    {
        return $this->type === 'image' && ! $this->isExternalLink();
    }
}
