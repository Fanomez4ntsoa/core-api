<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'user_id', 'selfie_url', 'id_document_url', 'id_document_type',
    'status', 'ai_confidence_score', 'ai_analysis',
    'manual_reviewer_id', 'manual_review_notes', 'manual_review_at',
    'rejection_reason', 'submitted_at', 'processed_at', 'verified_at',
])]
class IdentityVerification extends Model
{
    protected function casts(): array
    {
        return [
            'ai_confidence_score' => 'float',
            'submitted_at'        => 'datetime',
            'processed_at'        => 'datetime',
            'verified_at'         => 'datetime',
            'manual_review_at'    => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
