<?php

namespace App\Services\Messaging;

use App\Models\Conversation;
use App\Models\ConversationMessage;

class ConversationNumberService
{
    public function generateConversationNumber(): string
    {
        return $this->generate('CONV', Conversation::query()->count() + 1);
    }

    public function generateMessageNumber(): string
    {
        return $this->generate('MSG', ConversationMessage::query()->count() + 1);
    }

    public function ensureUnique(string $number): string
    {
        $candidate = $number;
        $suffix = 1;

        while (
            Conversation::query()->where('conversation_number', $candidate)->exists()
            || ConversationMessage::query()->where('message_number', $candidate)->exists()
        ) {
            $candidate = $number.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function generate(string $prefix, int $sequence): string
    {
        return $this->ensureUnique(sprintf('%s-%s-%06d', $prefix, now()->format('Y'), $sequence));
    }
}
