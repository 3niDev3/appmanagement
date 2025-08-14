<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApkDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'apk_id',
        'user_id',
        'device_name',
        'os_version',
        'location',
        'download_time'
    ];

    protected $dates = [
        'download_time',
    ];

    public function apk()
    {
        return $this->belongsTo(ProjectApk::class, 'apk_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
