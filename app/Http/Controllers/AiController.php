<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiController extends Controller
{
    private array $models = [
        'gemini-3.1-flash-lite',
        'gemini-3.1-flash-lite-preview',
        'gemini-3-flash-preview',
        'gemini-3.5-flash',
        'gemini-2.0-flash',
    ];

    public function checkTypo(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:5000',
        ]);

        $apiKey = config('services.gemini.key');
        if (empty($apiKey)) {
            return response()->json(['error' => 'GEMINI_API_KEY belum dikonfigurasi di .env'], 500);
        }

        $text = $request->text;

        $defaultPrompt = "Koreksi typo, kesalahan penulisan, DAN penomoran yang salah pada teks berikut dalam Bahasa Indonesia. "
            . "Jika ada nomor yang loncat (misal 1, 2, 5 → harusnya 1, 2, 3), perbaiki penomorannya agar berurutan. "
            . "Jangan mengubah makna atau struktur kalimat, hanya koreksi ejaan, ketik yang salah, dan nomor. "
            . "Jika tidak ada yang perlu diperbaiki, kembalikan teks yang sama persis. "
            . "Kembalikan hasilnya dalam format JSON murni: {\"corrected\": \"teks yang sudah dikoreksi\"}. "
            . "Jangan tambahkan penjelasan apapun selain JSON.";

        $promptTemplate = Setting::getValue('ai_typo_prompt', $defaultPrompt);
        $prompt = $promptTemplate . "\n\nTeks yang perlu dikoreksi:\n" . $text;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 2048,
            ],
        ];

        $lastError = null;

        foreach ($this->models as $model) {
            try {
                $response = Http::timeout(30)->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                    $payload
                );

                if ($response->failed()) {
                    $code = $response->status();
                    $lastError = "HTTP {$code} on {$model}";
                    continue;
                }

                $result = $response->json();

                $content = '';
                if (isset($result['candidates'][0]['content']['parts'])) {
                    foreach ($result['candidates'][0]['content']['parts'] as $part) {
                        if (!empty($part['thought'])) {
                            continue;
                        }
                        if (!empty($part['text'])) {
                            $content = $part['text'];
                            break;
                        }
                    }
                }

                if (empty($content)) {
                    $lastError = "Empty response from {$model}";
                    continue;
                }

                $content = trim($content);
                $content = preg_replace('/^```json\s*/i', '', $content);
                $content = preg_replace('/```\s*$/', '', $content);
                $content = trim($content);

                $decoded = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $jsonMatch = preg_match('/\{.*\}/s', $content, $m);
                    if ($jsonMatch) {
                        $fixed = str_replace(["\n", "\r"], ["\\n", "\\r"], $m[0]);
                        $decoded = json_decode($fixed, true);
                    }
                }

                if (json_last_error() !== JSON_ERROR_NONE || !$decoded) {
                    $lastError = "JSON parse failed on {$model}";
                    continue;
                }

                $corrected = $decoded['corrected'] ?? $text;

                return response()->json([
                    'original' => $text,
                    'corrected' => $corrected,
                    'changed' => $text !== $corrected,
                    'model' => $model,
                ]);
            } catch (\Exception $e) {
                $lastError = "Exception on {$model}: " . $e->getMessage();
                continue;
            }
        }

        return response()->json(['error' => 'Semua model gagal. Terakhir: ' . $lastError], 502);
    }
}
