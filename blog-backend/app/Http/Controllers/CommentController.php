<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Create a Comment (or Reply)
    public function store(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id' // If replying to another comment
        ]);

        $post = Post::findOrFail($postId);

        $comment = $post->comments()->create([
            'user_id' => auth()->id(),
            'post_id' => $postId,
            'content' => $request->content,
            'parent_id' => $request->parent_id // NULL if it's a main comment, ID if it's a reply
        ]);

        // Return the comment with the user info (so frontend can display name immediately)
        return response()->json($comment->load('user'), 201);
    }

    // Delete a Comment
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Security Check: Only the owner can delete
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}