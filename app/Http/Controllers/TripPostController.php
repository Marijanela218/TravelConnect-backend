<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripPost;
use Illuminate\Http\Request;

class TripPostController extends Controller
{

    public function index()
    {
        $posts = TripPost::query()
            ->with([
                'user:id,username',
                'trip:id,title,destination,is_public,user_id',
            ])
            ->withCount(['likes', 'comments'])
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

        $trip = Trip::findOrFail($data['trip_id']);

        if (!$trip->is_public && $trip->user_id !== $request->user()->id) {
            abort(403, 'Ne možeš objaviti privatno putovanje drugog korisnika.');
        }

        $post = TripPost::create([
            'user_id' => $request->user()->id,
            'trip_id' => $trip->id,
            'caption' => $data['caption'] ?? null,
        ]);

        return response()->json([
            'message' => 'Objava kreirana.',
            'post' => $post->load(['user:id,username', 'trip:id,title,destination,is_public,user_id']),
        ], 201);
    }

    public function show(TripPost $post)
    {
        $post->load([
            'user:id,username',
            'trip.user:id,username',
            'trip.days.items',
            'comments.user:id,username',
        ]);

        return response()->json(['post' => $post]);
    }

    public function destroy(Request $request, TripPost $post)
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403, 'Nemaš pravo obrisati ovu objavu.');
        }

        $post->delete();

        return response()->json(['message' => 'Objava obrisana.']);
    }
}
