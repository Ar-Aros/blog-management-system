<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    // GET /posts (The Feed)
    public function index()
    {
        return Post::with(['user', 'tags', 'likes'])
            ->withCount('likes', 'comments') // specific counts
            ->where('visibility', 'public')
            ->orWhere(function($query) {
                // Show private posts ONLY if they belong to the logged-in user
                if(auth()->check()){
                    $query->where('user_id', auth()->id());
                }
            })
            ->latest() // Sort by latest first
            ->get();
    }

    // POST /posts (Create)
   public function store(Request $request)
    {
        // 1. Validate
        $attrs = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'visibility' => 'required|in:public,private',
            // Allow tags to be nullable, string or array
            'tags' => 'nullable', 
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('uploads', 'public');
        }

        // 2. Create Post
        $post = $request->user()->posts()->create([
            'title' => $attrs['title'],
            'content' => $attrs['content'],
            'visibility' => $attrs['visibility'],
            'image' => $imagePath
        ]);

        // 3. Handle Tags
        // The frontend sends tags as a string "tech, life". We need to convert that.
        if ($request->filled('tags')) {
            $tagNames = explode(',', $request->tags); // Split by comma
            $tagIds = [];
            
            foreach ($tagNames as $tagName) {
                $tagName = trim($tagName);
                if($tagName) {
                    // Find or create the tag
                    $tag = \App\Models\Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
            }
            // Attach tags to post
            $post->tags()->sync($tagIds);
        }

        // 4. Return the post with relationships loaded
        return response()->json($post->load(['user', 'tags', 'likes']), 201);
    }


    // GET /posts/{id} (Single Post)
    public function show($id)
    {
        return Post::with(['user', 'tags', 'comments.user', 'comments.replies.user'])->findOrFail($id);
    }

    // POST /posts/{id}/like (Toggle Like)
    public function likeOrUnlike($id)
    {
        $post = Post::findOrFail($id);
        $like = $post->likes()->where('user_id', auth()->id())->first();

        if ($like) {
            $like->delete();
            return response()->json(['message' => 'Unliked']);
        } else {
            $post->likes()->create(['user_id' => auth()->id()]);
            return response()->json(['message' => 'Liked']);
        }
    }

    // Toggle Bookmark
    public function bookmarkOrUnbookmark($id)
    {
        $post = Post::findOrFail($id);
        
        // check if user already bookmarked it
        $bookmark = $post->bookmarks()->where('user_id', auth()->id())->first();

        if ($bookmark) {
            $bookmark->delete();
            return response()->json(['message' => 'Unbookmarked']);
        } else {
            $post->bookmarks()->create(['user_id' => auth()->id()]);
            return response()->json(['message' => 'Bookmarked']);
        }
    }

    // UPDATE POST (Edit)
    // Update a Post
   public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // 1. Check Ownership
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. Validate
        $attrs = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'visibility' => 'required|in:public,private',
            'tags' => 'nullable', 
            'image' => 'nullable|image|max:2048'
        ]);

        // 3. Handle Image (Only if a NEW file is sent)
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads', 'public');
            $post->image = $path; // Replace old image path
        }

        // 4. Update Text
        $post->update([
            'title' => $attrs['title'],
            'content' => $attrs['content'],
            'visibility' => $attrs['visibility']
        ]);

        // 5. Handle Tags (Sync)
        if ($request->filled('tags')) {
            // Convert string "tech, life" back to array
            $tagNames = explode(',', $request->tags);
            $tagIds = [];
            
            foreach ($tagNames as $tagName) {
                $tagName = trim($tagName);
                if($tagName) {
                    $tag = \App\Models\Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
            }
            $post->tags()->sync($tagIds);
        }

        // 6. Return fresh data
        return response()->json($post->load(['user', 'tags', 'likes']));
    }

    // DELETE POST
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // 1. Check Ownership
        if ($post->user_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. Delete
        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }
}