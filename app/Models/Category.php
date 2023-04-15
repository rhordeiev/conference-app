<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable
        = [
            'name',
            'conference_count',
            'report_count',
            'parent_id',
        ];

    protected $hidden
        = [
            'created_at',
            'updated_at',
        ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'parent_id', 'id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public static function getCategoryById($id)
    {
        return self::all()->where('id', $id)->first();
    }

    public function categoryConferences()
    {
        return $this->belongsToMany(
            Conference::class,
            'category_conference',
            'category_id',
            'conference_id'
        );
    }

    public function getCategoryConferences()
    {
        return $this->belongsToMany(
            Conference::class,
            'category_conference',
            'category_id',
            'conference_id'
        )->get(['conferences.*']);
    }

    public static function getChildCategories($parentId
    ): \Illuminate\Database\Eloquent\Collection {
        return self::all()->where('parent_id', $parentId);
    }

    public function categoryReports()
    {
        return $this->belongsToMany(
            Report::class,
            'category_report',
            'category_id',
            'report_id'
        );
    }

    public function getCategoryReports()
    {
        return $this->belongsToMany(
            Report::class,
            'category_report',
            'category_id',
            'report_id'
        )->get(['reports.*']);
    }
}
