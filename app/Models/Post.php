<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'caption',
        'location',
        'rating',
        'user_id',
    ];

    protected $appends = ['creatorName'];

    protected $casts = [
        'rating' => 'decimal:1',
    ];


    public function getCreatorNameAttribute()
    {
        return $this->user->name;
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'post_tags');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ratings()
    {
        return $this->hasMany(PostRating::class);
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }
}
