<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // -------------------------
    // Fillable fields
    // -------------------------
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // -------------------------
    // Hidden fields (for serialization)
    // -------------------------
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // -------------------------
    // Casts
    // -------------------------
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // -------------------------
    // Relationships
    // -------------------------

    /**
     * Projects assigned to this user.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user');
    }

    
    // -------------------------
    // Helper Methods
    // -------------------------

    /**
     * Check if the user is assigned to a given project
     */
    public function isAssignedToProject($projectId)
    {
        return $this->projects->contains('id', $projectId);
    }

    /**
     * Check if the user is assigned to a project by slug
     */
    public function isAssignedToProjectSlug($slug)
    {
        return $this->projects->contains('slug', $slug);
    }
}
