<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Row of the resource <-> group pivot (group_subject_resource).
 *
 * The same table also backs SubjectResource::groups() (belongsToMany); this model
 * exists for what a plain pivot cannot do - reading *soft-deleted* links so a
 * detached group can be re-attached or restored by undo without ever creating a
 * duplicate row (there is a unique key on resource + group).
 *
 *   is_active = true   the group sees the resource (explicit grant)
 *   is_active = false  PAUSED: the link is kept, the group does not see the resource,
 *                      and it overrides "shared with all groups" for that group.
 */
class ResourceGroupLink extends Model
{
    use SoftDeletes;

    protected $table = 'group_subject_resource';

    protected $fillable = [
        'subject_resource_id', 'group_id', 'is_active', 'created_by_type', 'created_by_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(SubjectResource::class, 'subject_resource_id')->withTrashed();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
