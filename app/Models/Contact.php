<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'subject', 'message', 'is_read'];

    public function getSearch($name, $is_read = null)
    {
        $query = $this->query();

        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->where('name', 'LIKE', '%' . $name . '%')
                  ->orWhere('email', 'LIKE', '%' . $name . '%')
                  ->orWhere('phone', 'LIKE', '%' . $name . '%')
                  ->orWhere('subject', 'LIKE', '%' . $name . '%');
            });
        }

        if ($is_read !== null && $is_read !== '') {
            $query->where('contacts.is_read', $is_read);
        }

        return $query->latest();
    }
}
