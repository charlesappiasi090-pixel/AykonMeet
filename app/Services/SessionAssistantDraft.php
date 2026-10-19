<?php

namespace App\Services;

use Illuminate\Support\Collection;

class SessionAssistantDraft
{
    public function generate(Collection $segments): array
    {
        $minutes = $segments->map(fn ($segment) => [
            'speaker' => $segment->participant?->display_name ?? 'Participant',
            'text' => $segment->text,
            'time' => $segment->spoken_at,
        ])->all();

        $decisions = $segments
            ->filter(fn ($segment) => preg_match('/\b(decided|decision|agreed|agreement|approved|resolved)\b/i', $segment->text) === 1)
            ->map(fn ($segment) => ['speaker' => $segment->participant?->display_name ?? 'Participant', 'text' => $segment->text])
            ->values()
            ->all();

        $actionItems = $segments
            ->filter(fn ($segment) => preg_match('/\b(action item|follow[- ]?up|next step|to[- ]?do|will)\b/i', $segment->text) === 1)
            ->map(fn ($segment) => ['speaker' => $segment->participant?->display_name ?? 'Participant', 'text' => $segment->text])
            ->values()
            ->all();

        return [
            'summary' => $segments
                ->filter(fn ($segment) => strlen(trim($segment->text)) >= 20)
                ->take(5)
                ->map(fn ($segment) => ['speaker' => $segment->participant?->display_name ?? 'Participant', 'text' => $segment->text])
                ->values()
                ->all(),
            'minutes' => $minutes,
            'decisions' => $decisions,
            'action_items' => $actionItems,
        ];
    }
}
