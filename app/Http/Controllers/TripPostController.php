<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripPost;
use Illuminate\Http\Request;

class TripPostController extends Controller
{
    public function index(Request $request)
{
    $userId = optional($request->user())->id;

    $posts = TripPost::query()
        ->with([
            'user:id,username',
            'trip:id,title,destination,is_public,user_id,image', // ✅ DODANO image
        ])
        ->withCount('likes')
        ->when($userId, fn ($q) =>
            $q->withExists(['likes as liked' => fn ($qq) => $qq->where('user_id', $userId)])
        )
        ->orderByDesc('id')
        ->get();

    return response()->json(['posts' => $posts]);
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'trip_id' => ['required', 'exists:trips,id'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $trip = Trip::select(['id','is_public','user_id'])->findOrFail($data['trip_id']);

        if (!$trip->is_public && $trip->user_id !== $request->user()->id) {
            abort(403, 'Ne možeš objaviti privatno putovanje drugog korisnika.');
        }

        $post = TripPost::create([
            'user_id' => $request->user()->id,
            'trip_id' => $trip->id,
            'caption' => $data['caption'] ?? null,
        ]);

        // Vrati sve što treba frontu + likes_count (0)
        $post->load([
            'user:id,username',
            'trip:id,title,destination,is_public,user_id,image',
        ])->loadCount('likes');

        return response()->json([
            'message' => 'Objava kreirana.',
            'post' => $post,
        ], 201);
    }

    public function show(TripPost $post)
    {
        $post->load([
            'user:id,username',
            'trip.user:id,username',
            'trip.days.items',
            'comments.user:id,username',
        ])->loadCount('likes');

        return response()->json(['post' => $post]);
    }

    public function destroy(Request $request, TripPost $post)
    {
        abort_unless($post->user_id === $request->user()->id, 403, 'Nemaš pravo obrisati ovu objavu.');

        $post->delete();

        return response()->json(['message' => 'Objava obrisana.']);
    }
}
