<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    // 1. THIS IS LIKELY THE MISSING PART:
    protected $fillable = ['content', 'user_id', 'post_id', 'parent_id'];

    // 2. Eager load user to prevent frontend "undefined" errors
    protected $with = ['user', 'replies'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Relationship for Nested Replies
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}