<?php

namespace App\Services\ViewState;

class MobileViewStateService
{
    /**
     * @return array{per_page:int, uses_cursor_pagination:bool, defer_secondary_content:bool, first_breakpoint_px:int}
     */
    public function defaultListState(int $requestedPerPage = 12): array
    {
        return [
            'per_page' => min(max($requestedPerPage, 5), 20),
            'uses_cursor_pagination' => true,
            'defer_secondary_content' => true,
            'first_breakpoint_px' => 320,
        ];
    }

    /**
     * @return array{primary_action_sticky:bool, secondary_controls_surface:string, render_hidden_large_sections:bool}
     */
    public function formState(): array
    {
        return [
            'primary_action_sticky' => true,
            'secondary_controls_surface' => 'bottom_sheet',
            'render_hidden_large_sections' => false,
        ];
    }
}
