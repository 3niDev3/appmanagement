<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectApk extends Model
{
    protected $fillable = [
        'project_id', 'filename', 'filepath', 'description', 'uploaded_by', 'download_count'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->filepath);
    }


    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Download history
    public function downloads()
    {
        return $this->hasMany(ApkDownload::class, 'apk_id');
    }

    
}
