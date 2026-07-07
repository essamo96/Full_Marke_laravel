<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamTranslation extends Model
{
    protected $fillable = ['team_id', 'locale', 'name', 'address1', 'address2', 'description', 'board_description'];
}
