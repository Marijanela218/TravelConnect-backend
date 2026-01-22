<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\TripPost;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, TripPost $post)
    {
        $data = $request->validate([
            'body' => ['required','string','min:1','max:2000'],
        ]);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'trip_post_id' => $post->id,
            'body' => $data['body'],
        ]);

        return response()->json([
            'message' => 'Komentar dodan.',
            'comment' => $comment->load('user:id,username'),
        ], 201);
    }

    public function destroy(Request $request, Comment $comment)
    {
        if ($comment->user_id !== $request->user()->id) {
            abort(403, 'Nemaš pravo obrisati ovaj komentar.');
        }

        $comment->delete();

        return response()->json(['message' => 'Komentar obrisan.']);
    }
}
