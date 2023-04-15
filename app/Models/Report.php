<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    protected $fillable
        = [
            'topic',
            'start_time',
            'end_time',
            'description',
            'presentation',
            'creator_id',
            'conference_id',
            'meeting_id',
        ];

    public static function getReportByConferenceAndCreator(
        $conferenceId,
        $userId
    ) {
        return self::all()->where('conference_id', $conferenceId)->where(
            'creator_id',
            $userId
        )->first();
    }

    public static function getReportById(int $id)
    {
        return self::all()->where('id', $id)->first();
    }

    public static function getReportsByConference(int $id): Collection
    {
        return self::all()->where('conference_id', $id);
    }

    public static function getReportsByUser(int $id): Collection
    {
        return self::all()->where('creator_id', $id);
    }

    public static function getReportByMeeting(?int $id)
    {
        return self::all()->where('meeting_id', $id)->first();
    }

    public static function searchByTopic(string $topic): Collection|array
    {
        return self::query()->where('topic', 'like', "${topic}%")->get();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function reportCategory(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_report',
            'report_id',
            'category_id'
        );
    }

    public function favoritesUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'favorites',
            'report_id',
            'user_id'
        );
    }

    public function isFavorite(int $id)
    {
        return $this->belongsToMany(
            User::class,
            'favorites',
            'report_id',
            'user_id'
        )->where('user_id', $id)->first();
    }

    public function reportComments(): HasMany
    {
        return $this->hasMany(
            Comment::class
        );
    }

    public function reportListeners(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'report_listener',
            'report_id',
            'listener_id'
        );
    }
}
