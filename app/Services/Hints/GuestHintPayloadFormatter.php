<?php

namespace App\Services\Hints;

use JsonException;

class GuestHintPayloadFormatter
{
    private const MESSAGE_KEY_PREFIX = 'guest_hints.messages.';

    /**
     * @param  list<array<string, mixed>>  $hints
     */
    public function encodeList(array $hints): string
    {
        $references = [];

        foreach (array_values($hints) as $hint) {
            $reference = $this->referenceFromHint($hint);

            if ($reference !== null) {
                $references[] = $reference;
            }
        }

        return $this->encode($references, '[]');
    }

    /**
     * @param  array<string, mixed>  $hint
     */
    public function encodeOne(array $hint): string
    {
        return $this->encode($this->referenceFromHint($hint) ?? [], '{}');
    }

    /**
     * @return list<array{text: string, category: ?string, source: ?string}>
     */
    public function displayList(string $payload): array
    {
        $decoded = $this->decode($payload);

        if (! is_array($decoded)) {
            return [];
        }

        $hints = [];

        foreach ($decoded as $reference) {
            if (! is_array($reference)) {
                continue;
            }

            $hint = $this->displayFromReference($reference);

            if ($hint !== null) {
                $hints[] = $hint;
            }
        }

        return $hints;
    }

    /**
     * @return array{text: string, category: ?string, source: ?string}
     */
    public function displayOne(string $payload): array
    {
        $decoded = $this->decode($payload);

        if (! is_array($decoded)) {
            return [];
        }

        return $this->displayFromReference($decoded) ?? [];
    }

    /**
     * @param  array<string, mixed>  $hint
     * @return array{key: string, category: ?string, source: ?string, message_params: array<string, scalar|null>}|null
     */
    private function referenceFromHint(array $hint): ?array
    {
        $key = $this->hintKey($hint);

        if ($key === null) {
            return null;
        }

        return [
            'key' => $key,
            'category' => $this->safeString($hint['category'] ?? null),
            'source' => $this->safeString($hint['source'] ?? null),
            'message_params' => $this->messageParams($hint['message_params'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $hint
     */
    private function hintKey(array $hint): ?string
    {
        $key = $this->safeString($hint['key'] ?? null);

        if ($key !== null) {
            return $this->safeKey($key);
        }

        $messageKey = $this->safeString($hint['message_key'] ?? null);

        if ($messageKey === null || ! str_starts_with($messageKey, self::MESSAGE_KEY_PREFIX)) {
            return null;
        }

        return $this->safeKey(substr($messageKey, strlen(self::MESSAGE_KEY_PREFIX)));
    }

    private function safeKey(string $key): ?string
    {
        return preg_match('/^[a-z0-9_.-]+$/', $key) === 1 ? $key : null;
    }

    private function safeString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, 120);
    }

    /**
     * @return array<string, scalar|null>
     */
    private function messageParams(mixed $params): array
    {
        if (! is_array($params)) {
            return [];
        }

        $normalized = [];

        foreach ($params as $key => $value) {
            if (count($normalized) >= 8 || ! is_string($key) || (! is_scalar($value) && $value !== null)) {
                continue;
            }

            $normalized[$key] = is_string($value) ? mb_substr($value, 0, 120) : $value;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $reference
     * @return array{text: string, category: ?string, source: ?string}|null
     */
    private function displayFromReference(array $reference): ?array
    {
        $key = $this->safeString($reference['key'] ?? null);
        $key = $key !== null ? $this->safeKey($key) : null;

        if ($key === null) {
            return null;
        }

        return [
            'text' => __(self::MESSAGE_KEY_PREFIX.$key, $this->messageParams($reference['message_params'] ?? [])),
            'category' => $this->safeString($reference['category'] ?? null),
            'source' => $this->safeString($reference['source'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>|list<array<string, mixed>>  $payload
     */
    private function encode(array $payload, string $fallback): string
    {
        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $fallback;
        }
    }

    private function decode(string $payload): mixed
    {
        try {
            return json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }
    }
}
