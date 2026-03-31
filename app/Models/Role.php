<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'slug', 'description'])]
class Role extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
                        ->withPivot('universe_slug')
                        ->withTimestamps();
    }
}
