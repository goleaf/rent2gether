<?php

namespace App\Services\HostHints\Concerns;

trait BuildsHostHints
{
    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function hint(
        string $key,
        string $category,
        string $type = 'suggestion',
        string $importance = 'medium',
        int $priority = 50,
        ?string $actionKey = null,
        bool $showInWizard = true,
        bool $showInDashboard = true,
        bool $showBeforePublish = false,
        bool $showOnListingCard = false,
        array $params = [],
        ?string $source = null,
    ): array {
        return [
            'hint_key' => $key,
            'category' => $category,
            'type' => $type,
            'importance' => $importance,
            'priority' => $priority,
            'message_key' => 'host_hints.messages.'.$key,
            'message_params_json' => $params,
            'action_key' => $actionKey,
            'action_url' => null,
            'status' => 'active',
            'source' => $source ?? $category,
            'show_in_wizard' => $showInWizard,
            'show_in_dashboard' => $showInDashboard,
            'show_before_publish' => $showBeforePublish,
            'show_on_listing_card' => $showOnListingCard,
            'calculated_at' => now(),
            'expires_at' => null,
        ];
    }
}
