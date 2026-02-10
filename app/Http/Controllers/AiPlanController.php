<?php

namespace App\Http\Controllers;

use App\Models\AiGeneration;
use App\Models\Trip;
use App\Models\ItineraryDay;
use App\Models\ItineraryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiPlanController extends Controller
{
    public function generate(Request $request)
    {
        try {
            $data = $request->validate([
                'destination' => ['required','string','max:255'],
                'days' => ['required','integer','min:1','max:21'],
                'budget' => ['nullable','numeric','min:0'],
                'pace' => ['nullable','in:lagano,normalno,brzo'],
                'interests' => ['nullable','array'],
                'interests.*' => ['string','max:50'],
                'trip_id' => ['nullable','integer','exists:trips,id'],
            ]);

            $trip = null;
            if (!empty($data['trip_id'])) {
                $trip = Trip::find($data['trip_id']);

                // IMPORTANT: ako nema auth middleware, user može biti null
                $user = $request->user();
                if (!$user) {
                    abort(401, 'Niste prijavljeni.');
                }

                if ($trip && $trip->user_id !== $user->id) {
                    abort(403, 'Ne možeš generisati plan za tuđe putovanje.');
                }
            }

            $promptJson = [
                'destination' => $data['destination'],
                'days' => (int) $data['days'],
                'budget' => $data['budget'] ?? null,
                'pace' => $data['pace'] ?? 'normalno',
                'interests' => $data['interests'] ?? [],
            ];

            $plan = $this->generatePlanFromGemini($promptJson);

            $user = $request->user();
            if (!$user) {
                abort(401, 'Niste prijavljeni.');
            }

            $log = AiGeneration::create([
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'prompt_json' => $promptJson,
                'result_json' => $plan,
                'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
            ]);

            return response()->json([
                'message' => 'Plan generisan.',
                'ai_generation_id' => $log->id,
                'plan' => $plan,
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function planAndApply(Request $request)
    {
        try {
            $data = $request->validate([
                'trip_id' => ['required','integer','exists:trips,id'],

                'destination' => ['required','string','max:255'],
                'days' => ['required','integer','min:1','max:21'],
                'budget' => ['nullable','numeric','min:0'],
                'pace' => ['nullable','in:lagano,normalno,brzo'],
                'interests' => ['nullable','array'],
                'interests.*' => ['string','max:50'],

                'replace' => ['nullable','boolean'],
            ]);

            $user = $request->user();
            if (!$user) {
                abort(401, 'Niste prijavljeni.');
            }

            $trip = Trip::findOrFail($data['trip_id']);

            if ($trip->user_id !== $user->id) {
                abort(403, 'Ne možeš primijeniti AI plan na tuđe putovanje.');
            }

            $replace = $data['replace'] ?? true;

            $promptJson = [
                'destination' => $data['destination'],
                'days' => (int) $data['days'],
                'budget' => $data['budget'] ?? null,
                'pace' => $data['pace'] ?? 'normalno',
                'interests' => $data['interests'] ?? [],
            ];

            $plan = $this->generatePlanFromGemini($promptJson);

            $result = DB::transaction(function () use ($user, $trip, $replace, $promptJson, $plan) {

                $log = AiGeneration::create([
                    'user_id' => $user->id,
                    'trip_id' => $trip->id,
                    'prompt_json' => $promptJson,
                    'result_json' => $plan,
                    'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
                ]);

                if ($replace && isset($plan['description'])) {
                    $trip->update(['description' => $plan['description']]);
                }

                if ($replace) {
                    $trip->load('days.items');
                    foreach ($trip->days as $d) {
                        $d->items()->delete();
                    }
                    $trip->days()->delete();
                }

                foreach (($plan['days'] ?? []) as $dayData) {
                    $dayIndex = (int) ($dayData['day'] ?? 1);

                    $newDay = ItineraryDay::create([
                        'trip_id' => $trip->id,
                        'day_index' => $dayIndex,
                        'date' => null,
                        'title' => $dayData['title'] ?? ('Dan ' . $dayIndex),
                    ]);

                    $order = 1;
                    foreach (($dayData['items'] ?? []) as $itemData) {
                        ItineraryItem::create([
                            'itinerary_day_id' => $newDay->id,
                            'type' => $itemData['type'] ?? 'activity',
                            'title' => $itemData['title'] ?? 'Aktivnost',
                            'location' => $itemData['location'] ?? null,
                            'start_time' => $this->normalizeTime($itemData['time'] ?? null),
                            'end_time' => null,
                            'notes' => $itemData['notes'] ?? null,
                            'cost_estimate' => null,
                            'order' => $order++,
                        ]);
                    }
                }

                return ['ai_generation_id' => $log->id];
            });

            return response()->json([
                'message' => 'AI plan primijenjen na putovanje.',
                'ai_generation_id' => $result['ai_generation_id'],
                'trip' => $trip->fresh()->load('days.items'),
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    private function generatePlanFromGemini(array $promptJson): array
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            abort(500, 'GEMINI_API_KEY nije postavljen u .env');
        }

        $model = env('GEMINI_MODEL', 'gemini-flash-latest');
        $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}";


        $prompt = "Ti si travel planner. Vrati ISKLJUČIVO validan JSON bez dodatnog teksta.
Struktura JSON:
{
  \"title\": string,
  \"description\": string,
  \"summary\": string,
  \"tips\": string[],
  \"days\": [
    {
      \"day\": number,
      \"title\": string,
      \"items\": [
        {\"type\":\"activity|food|transport|hotel\",\"time\":\"HH:MM\",\"title\":string,\"location\":string|null,\"notes\":string|null}
      ]
    }
  ]
}
Vrati tačno {$promptJson['days']} dana.
Ulazni podaci: " . json_encode($promptJson, JSON_UNESCAPED_UNICODE);

        $res = Http::acceptJson()
            ->timeout(60)
            ->post($url, [
                "contents" => [[
                    "parts" => [[ "text" => $prompt ]]
                ]]
            ]);

        if (!$res->successful()) {
    abort(500, 'Gemini error: HTTP ' . $res->status() . ' | ' . $res->body());
}


        $text = trim((string) $res->json('candidates.0.content.parts.0.text'));

        $text = preg_replace('/^```json\s*/', '', $text);
        $text = preg_replace('/^```\s*/', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        $plan = json_decode($text, true);
        if (!is_array($plan)) {
            abort(422, 'Gemini nije vratio validan JSON.');
        }

        return $plan;
    }

    private function normalizeTime(?string $time): ?string
    {
        if (!$time) return null;

        $t = trim($time);

        if (preg_match('/^\d{1}:\d{2}$/', $t)) return '0' . $t;
        if (preg_match('/^\d{2}:\d{2}$/', $t)) return $t;

        return null;
    }
    public function listGeminiModels()
{
    $apiKey = env('GEMINI_API_KEY');
    $res = Http::acceptJson()->get(
        "https://generativelanguage.googleapis.com/v1/models?key={$apiKey}"
    );

    return response()->json($res->json(), $res->status());
}

}
