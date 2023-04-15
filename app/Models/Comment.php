<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable
        = [
            'firstname',
            'lastname',
            'date',
            'text',
            'report_id',
            'user_id',
        ];

    protected $casts
        = [
            'date' => 'datetime',
        ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public static function getCommentById($id)
    {
        return self::all()->where('id', $id)->first();
    }
}
