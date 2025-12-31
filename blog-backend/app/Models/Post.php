<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'image', 'visibility', 'user_id'];

    // Eager load these relationships by default to avoid N+1 queries
    protected $with = ['user', 'tags', 'likes']; 
    
    // Add these custom attributes to the JSON output
    protected $appends = ['is_liked', 'likes_count', 'is_bookmarked'];

    // --- RELATIONSHIPS ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    
    // THIS WAS LIKELY MISSING:
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    // --- CUSTOM ATTRIBUTES (ACCESSORS) ---

    public function getIsLikedAttribute()
    {
        if (!auth()->check()) return false;
        return $this->likes()->where('user_id', auth()->id())->exists();
    }

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function getIsBookmarkedAttribute()
    {
        if (!auth()->check()) return false;
        // This requires the bookmarks() relationship above to exist!
        return $this->bookmarks()->where('user_id', auth()->id())->exists();
    }
}