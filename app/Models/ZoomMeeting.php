<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoomMeeting extends Model
{
    use HasFactory;

    protected $table = 'zoom_meetings';

    protected $fillable
        = [
            'id',
            'topic',
            'agenda',
            'type',
            'start_time',
            'duration',
            'start_url',
            'join_url',
        ];

    protected $casts
        = [
            'start_time' => 'datetime',
        ];

    public static function getMeetingById($id)
    {
        return self::all()->where('id', '=', $id)->first();
    }
}
