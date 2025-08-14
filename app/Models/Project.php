<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'slug'];

    public function apks()
    {
        return $this->hasMany(ProjectApk::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

}


