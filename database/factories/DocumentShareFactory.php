<?php

namespace Database\Factories;

use App\Models\DocumentShare;
use App\Models\Meeting;
use App\Models\OnlineSessionParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DocumentShare>
 */
class DocumentShareFactory extends Factory
{
    protected $model = DocumentShare::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'uploader_id' => OnlineSessionParticipant::factory(),
            'original_name' => 'document-'.Str::random(8).'.pdf',
            'stored_name' => Str::random(40).'.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'file_size' => $this->faker->numberBetween(1000, 10_000_000),
            'storage_path' => 'meeting-documents/'.Str::random(10).'/document.pdf',
            'storage_disk' => 'local',
            'share_scope' => DocumentShare::SCOPE_ALL,
            'shared_with_companies' => null,
            'shared_with_participants' => null,
            'allow_download' => true,
            'allow_preview' => true,
            'status' => DocumentShare::STATUS_ACTIVE,
            'revoked_at' => null,
            'revoked_by' => null,
            'expires_at' => null,
            'retention_policy' => DocumentShare::RETENTION_KEEP,
            'encrypted_e2e' => false,
            'encryption_key_id' => null,
        ];
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentShare::STATUS_REVOKED,
            'revoked_at' => now(),
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentShare::STATUS_DELETED,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subHour(),
        ]);
    }

    public function withExpiration(\DateTimeInterface $date): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $date,
        ]);
    }

    public function withRetentionPolicy(string $policy): static
    {
        return $this->state(fn (array $attributes) => [
            'retention_policy' => $policy,
        ]);
    }

    public function withScope(string $scope): static
    {
        return $this->state(fn (array $attributes) => [
            'share_scope' => $scope,
        ]);
    }

    public function noDownload(): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_download' => false,
        ]);
    }

    public function noPreview(): static
    {
        return $this->state(fn (array $attributes) => [
            'allow_preview' => false,
        ]);
    }

    public function e2eEncrypted(): static
    {
        return $this->state(fn (array $attributes) => [
            'encrypted_e2e' => true,
        ]);
    }
}
