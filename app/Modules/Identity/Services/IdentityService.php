<?php

namespace App\Modules\Identity\Services;

use App\Models\IdentityVerification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IdentityService
{
    public function getStatus(User $user): array
    {
        $verification = IdentityVerification::where('user_id', $user->id)
                                                ->latest()
                                                ->first();

        return [
            'identity_status' => $user->identity_status,
            'is_verified'     => $user->is_verified,
            'verification'    => $verification,
        ];
    }

    public function submit(User $user, array $data): array
    {
        // Déjà vérifié
        if ($user->is_verified) {
            return ['error' => 'Compte déjà vérifié', 'code' => 400];
        }

        // Vérification déjà en cours
        $existing = IdentityVerification::where('user_id', $user->id)
                                            ->whereIn('status', ['pending', 'processing'])
                                            ->first();

        if ($existing) {
            return ['error' => 'Une vérification est déjà en cours', 'code' => 400];
        }

        // Créer le record — basé sur Emergent
        $verification = IdentityVerification::create([
            'user_id'           => $user->id,
            'selfie_url'        => 'base64:' . substr($data['selfie_base64'], 0, 100) . '...',
            'id_document_url'   => 'base64:' . substr($data['id_document_base64'], 0, 100) . '...',
            'id_document_type'  => $data['id_document_type'],
            'status'            => 'processing',
            'submitted_at'      => now(),
        ]);

        // Tenter l'analyse IA
        $aiResult = $this->analyzeWithAI(
            $data['selfie_base64'],
            $data['id_document_base64'],
            $data['id_document_type']
        );

        if (!$aiResult) {
            // Pas de clé IA → manual_review (même comportement qu'Emergent)
            $verification->update(['status' => 'manual_review']);
            $user->update(['identity_status' => 'manual_review']);

            return [
                'status'  => 'manual_review',
                'message' => 'Vérification soumise pour examen manuel',
            ];
        }

        // Traiter le résultat IA
        $status = $this->resolveStatus($aiResult['confidence']);

        $verification->update([
            'status'              => $status,
            'ai_confidence_score' => $aiResult['confidence'],
            'ai_analysis'         => $aiResult['analysis'],
            'processed_at'        => now(),
            'verified_at'         => $status === 'verified' ? now() : null,
            'rejection_reason'    => $status === 'rejected' ? ($aiResult['issues'][0] ?? null) : null,
        ]);

        $user->update([
            'identity_status' => $status,
            'is_verified'     => $status === 'verified',
            'verified_at'     => $status === 'verified' ? now() : null,
        ]);

        return [
            'status'     => $status,
            'confidence' => $aiResult['confidence'],
            'message'    => $this->statusMessage($status),
        ];
    }

    // ============ PRIVÉ ============

    private function analyzeWithAI(string $selfie, string $idDocument, string $docType): ?array
    {
        $apiKey = config('services.openai.key');

        if (!$apiKey) {
            Log::info('KYC: Pas de clé OpenAI — manual_review');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model'    => 'gpt-4o',
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => 'Tu es un expert en vérification d\'identité. Analyse le selfie et le document d\'identité et réponds UNIQUEMENT avec un JSON valide : {"match": true/false, "confidence": 0-100, "analysis": "explication", "issues": []}. Score > 70 = vérifié, < 50 = rejeté, 50-70 = revue manuelle.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => "Vérifie l'identité. Selfie vs {$docType}."],
                            ['type' => 'image_url', 'image_url' => ['url' => "data:image/jpeg;base64,{$selfie}"]],
                            ['type' => 'image_url', 'image_url' => ['url' => "data:image/jpeg;base64,{$idDocument}"]],
                        ],
                    ],
                ],
            ]);

            $content = $response->json('choices.0.message.content');
            return json_decode($content, true);

        } catch (\Exception $e) {
            Log::error('KYC AI Error: ' . $e->getMessage());
            return null;
        }
    }

    private function resolveStatus(float $confidence): string
    {
        if ($confidence >= 70) return 'verified';
        if ($confidence >= 50) return 'manual_review';
        return 'rejected';
    }

    private function statusMessage(string $status): string
    {
        return match($status) {
            'verified'      => 'Identité vérifiée avec succès',
            'manual_review' => 'Vérification soumise pour examen manuel',
            'rejected'      => 'Vérification échouée — documents non conformes',
            default         => 'Vérification en cours',
        };
    }
}