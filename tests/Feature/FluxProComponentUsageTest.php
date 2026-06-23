<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FluxProComponentUsageTest extends TestCase
{
    public function test_known_typeahead_views_use_flux_autocomplete(): void
    {
        $views = [
            resource_path('views/livewire/catalog/amenity-picker.blade.php'),
            resource_path('views/livewire/catalog/rule-picker.blade.php'),
            resource_path('views/livewire/geo/city-autocomplete.blade.php'),
            resource_path('views/livewire/geo/partials/country-city-autocomplete.blade.php'),
            resource_path('views/livewire/search/sleeping-place-search.blade.php'),
        ];

        foreach ($views as $view) {
            $this->assertStringContainsString('<flux:autocomplete', File::get($view), $view);
        }

        $this->assertStringContainsString(
            'livewire.geo.partials.country-city-autocomplete',
            File::get(resource_path('views/livewire/host/property-form.blade.php')),
        );
    }

    public function test_collapsible_blade_sections_use_flux_accordion(): void
    {
        $offenders = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($file): bool => str_ends_with($file->getFilename(), '.blade.php'))
            ->flatMap(function ($file): array {
                $relativePath = str($file->getPathname())
                    ->after(base_path().DIRECTORY_SEPARATOR)
                    ->toString();

                return collect(file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [])
                    ->filter(fn (string $line): bool => preg_match('/<\s*\/?\s*(details|summary)\b/i', $line) === 1)
                    ->map(fn (string $line, int $index): string => $relativePath.':'.($index + 1).' '.trim($line))
                    ->all();
            })
            ->values()
            ->all();

        $this->assertSame([], $offenders);
    }

    public function test_blade_action_and_form_controls_use_flux_components(): void
    {
        $offenders = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($file): bool => str_ends_with($file->getFilename(), '.blade.php'))
            ->flatMap(function ($file): array {
                $relativePath = str($file->getPathname())
                    ->after(base_path().DIRECTORY_SEPARATOR)
                    ->toString();

                return collect(file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [])
                    ->filter(fn (string $line): bool => preg_match('/<\s*\/?\s*(button|input|select|textarea|option|table)\b/i', $line) === 1)
                    ->map(fn (string $line, int $index): string => $relativePath.':'.($index + 1).' '.trim($line))
                    ->all();
            })
            ->values()
            ->all();

        $this->assertSame([], $offenders);
    }

    public function test_listing_chip_and_loading_surfaces_use_flux_components(): void
    {
        $this->assertStringContainsString(
            '<flux:badge',
            File::get(resource_path('views/components/listings/card-amenities.blade.php')),
        );

        $this->assertStringContainsString(
            '<flux:badge',
            File::get(resource_path('views/components/listings/card-rules.blade.php')),
        );

        $skeleton = File::get(resource_path('views/components/listings/card-skeleton.blade.php'));

        $this->assertStringContainsString('<flux:skeleton.group', $skeleton);
        $this->assertStringContainsString('<flux:skeleton.line', $skeleton);
        $this->assertStringNotContainsString('animate-pulse', $skeleton);
    }

    public function test_media_manager_uses_flux_pro_file_upload_components(): void
    {
        $view = File::get(resource_path('views/livewire/media/manage-media.blade.php'));

        $this->assertStringContainsString('<flux:file-upload', $view);
        $this->assertStringContainsString('<flux:file-upload.dropzone', $view);
        $this->assertStringContainsString('<flux:file-item', $view);
        $this->assertStringContainsString('<flux:file-item.remove', $view);
        $this->assertStringNotContainsString('type="file"', $view);
    }

    public function test_livewire_upload_surfaces_use_flux_pro_file_upload_components(): void
    {
        $views = [
            resource_path('views/livewire/account/profile-setup-page.blade.php'),
            resource_path('views/livewire/checkin/problem-report.blade.php'),
            resource_path('views/livewire/complaints/create-complaint.blade.php'),
            resource_path('views/livewire/host/partials/host-profile-section.blade.php'),
            resource_path('views/livewire/host/property-form.blade.php'),
            resource_path('views/livewire/host/room-form.blade.php'),
            resource_path('views/livewire/host/sleeping-place-form.blade.php'),
            resource_path('views/livewire/messages/chat-window.blade.php'),
            resource_path('views/livewire/reviews/create-review.blade.php'),
        ];

        foreach ($views as $viewPath) {
            $view = File::get($viewPath);

            $this->assertStringContainsString('<flux:file-upload', $view, $viewPath);
            $this->assertStringContainsString('<flux:file-upload.dropzone', $view, $viewPath);
            $this->assertStringNotContainsString('type="file"', $view, $viewPath);
        }
    }

    public function test_frontend_assets_use_scss_and_flux_pro_styles(): void
    {
        $this->assertFileExists(resource_path('css/app.scss'));
        $this->assertFileDoesNotExist(resource_path('css/app.css'));
        $this->assertDirectoryDoesNotExist(resource_path('js'));

        $stylesheet = File::get(resource_path('css/app.scss'));

        $this->assertStringContainsString("@use 'tailwindcss';", $stylesheet);
        $this->assertStringContainsString("@import '../../vendor/livewire/flux/dist/flux.css';", $stylesheet);
        $this->assertStringContainsString("@import '../../vendor/livewire/flux-pro/dist/editor.css';", $stylesheet);

        $this->assertStringContainsString(
            "input: ['resources/css/app.scss']",
            File::get(base_path('vite.config.js')),
        );

        $this->assertStringContainsString(
            "'@tailwindcss/postcss': {}",
            File::get(base_path('postcss.config.mjs')),
        );

        foreach ([
            resource_path('views/components/layouts/app.blade.php'),
            resource_path('views/components/layouts/guest.blade.php'),
        ] as $layout) {
            $layoutContents = File::get($layout);

            $this->assertStringContainsString("@vite('resources/css/app.scss')", $layoutContents);
            $this->assertStringNotContainsString('resources/css/app.css', $layoutContents);
            $this->assertStringNotContainsString('resources/js/app.js', $layoutContents);
        }
    }

    public function test_flux_ghost_buttons_use_visible_secondary_surface(): void
    {
        $stylesheet = File::get(resource_path('css/app.scss'));

        $this->assertStringContainsString('[data-flux-button].bg-transparent.text-zinc-800:not(.absolute):not(.fixed)', $stylesheet);
        $this->assertStringContainsString('background-color: #f0f9ff;', $stylesheet);
        $this->assertStringContainsString('color: #075985;', $stylesheet);
        $this->assertStringContainsString('background-color: rgba(56, 189, 248, 0.12);', $stylesheet);
        $this->assertFileDoesNotExist(resource_path('views/flux/button/index.blade.php'));
    }

    public function test_interactive_flux_surfaces_have_icons(): void
    {
        $components = [
            'flux:button',
            'flux:tab',
            'flux:menu.item',
            'flux:navlist.item',
            'flux:navbar.item',
        ];
        $missingIcons = [];
        $invalidIcons = [];
        $invalidIconVariants = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = File::get($file->getPathname());
            $relativePath = str($file->getPathname())
                ->after(base_path().DIRECTORY_SEPARATOR)
                ->toString();

            foreach ($components as $component) {
                foreach (self::findFluxComponentOccurrences($contents, $component) as $occurrence) {
                    if (self::isFluxIconException($relativePath, $occurrence)) {
                        continue;
                    }

                    if (! self::fluxOpeningTagHasIcon($occurrence['tag'])) {
                        $missingIcons[] = self::formatFluxComponentOffender($relativePath, $contents, $occurrence, $component);
                    }

                    self::collectInvalidFluxIcons($relativePath, $contents, $occurrence, $component, $invalidIcons, $invalidIconVariants);
                }
            }
        }

        $this->assertSame([], $missingIcons);
        $this->assertSame([], $invalidIcons);
        $this->assertSame([], $invalidIconVariants);
    }

    public function test_status_flux_surfaces_have_icons(): void
    {
        $components = [
            'flux:badge',
            'flux:callout',
        ];
        $missingIcons = [];
        $invalidIcons = [];
        $invalidIconVariants = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = File::get($file->getPathname());
            $relativePath = str($file->getPathname())
                ->after(base_path().DIRECTORY_SEPARATOR)
                ->toString();

            foreach ($components as $component) {
                foreach (self::findFluxComponentOccurrences($contents, $component) as $occurrence) {
                    if (! self::fluxOpeningTagHasIcon($occurrence['tag'])) {
                        $missingIcons[] = self::formatFluxComponentOffender($relativePath, $contents, $occurrence, $component);
                    }

                    self::collectInvalidFluxIcons($relativePath, $contents, $occurrence, $component, $invalidIcons, $invalidIconVariants);
                }
            }
        }

        $this->assertSame([], $missingIcons);
        $this->assertSame([], $invalidIcons);
        $this->assertSame([], $invalidIconVariants);
    }

    public function test_text_entry_flux_surfaces_have_icons(): void
    {
        $components = [
            'flux:input',
            'flux:autocomplete',
        ];
        $missingIcons = [];
        $invalidIcons = [];
        $invalidIconVariants = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = File::get($file->getPathname());
            $relativePath = str($file->getPathname())
                ->after(base_path().DIRECTORY_SEPARATOR)
                ->toString();

            foreach ($components as $component) {
                foreach (self::findFluxComponentOccurrences($contents, $component) as $occurrence) {
                    if (str_contains(strtolower($occurrence['tag']), 'type="hidden"')) {
                        continue;
                    }

                    if (! self::fluxOpeningTagHasInputIcon($occurrence['tag'])) {
                        $missingIcons[] = self::formatFluxComponentOffender($relativePath, $contents, $occurrence, $component);
                    }

                    self::collectInvalidFluxIcons($relativePath, $contents, $occurrence, $component, $invalidIcons, $invalidIconVariants);
                }
            }
        }

        $this->assertSame([], $missingIcons);
        $this->assertSame([], $invalidIcons);
        $this->assertSame([], $invalidIconVariants);
    }

    public function test_structural_flux_surfaces_have_icons(): void
    {
        $missingIcons = [];
        $invalidIcons = [];
        $invalidIconVariants = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = File::get($file->getPathname());
            $relativePath = str($file->getPathname())
                ->after(base_path().DIRECTORY_SEPARATOR)
                ->toString();

            foreach (self::findFluxComponentOccurrences($contents, 'flux:heading') as $occurrence) {
                if (self::isFluxHeadingIconException($occurrence)) {
                    continue;
                }

                if (! str_contains($occurrence['body'], '<flux:icon')) {
                    $missingIcons[] = self::formatFluxComponentOffender($relativePath, $contents, $occurrence, 'flux:heading');
                }
            }

            foreach (self::findFluxComponentOccurrences($contents, 'flux:breadcrumbs.item') as $occurrence) {
                if (! self::fluxOpeningTagHasIcon($occurrence['tag']) && ! str_contains($occurrence['body'], '<flux:icon')) {
                    $missingIcons[] = self::formatFluxComponentOffender($relativePath, $contents, $occurrence, 'flux:breadcrumbs.item');
                }

                self::collectInvalidFluxIcons($relativePath, $contents, $occurrence, 'flux:breadcrumbs.item', $invalidIcons, $invalidIconVariants);
            }

            foreach (self::findFluxComponentOccurrences($contents, 'flux:callout.heading') as $occurrence) {
                if (! self::fluxOpeningTagHasIcon($occurrence['tag']) && ! str_contains($occurrence['body'], '<flux:icon')) {
                    $missingIcons[] = self::formatFluxComponentOffender($relativePath, $contents, $occurrence, 'flux:callout.heading');
                }

                self::collectInvalidFluxIcons($relativePath, $contents, $occurrence, 'flux:callout.heading', $invalidIcons, $invalidIconVariants);
            }

            foreach (['flux:label', 'flux:accordion.heading', 'flux:description'] as $component) {
                foreach (self::findFluxComponentOccurrences($contents, $component) as $occurrence) {
                    if (! str_contains($occurrence['body'], '<flux:icon')) {
                        $missingIcons[] = self::formatFluxComponentOffender($relativePath, $contents, $occurrence, $component);
                    }
                }
            }
        }

        $this->assertSame([], $missingIcons);
        $this->assertSame([], $invalidIcons);
        $this->assertSame([], $invalidIconVariants);
    }

    public function test_form_controls_do_not_use_non_icon_label_shorthand(): void
    {
        $offenders = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = File::get($file->getPathname());
            $relativePath = str($file->getPathname())
                ->after(base_path().DIRECTORY_SEPARATOR)
                ->toString();

            foreach (['flux:autocomplete', 'flux:checkbox', 'flux:input', 'flux:select', 'flux:switch', 'flux:textarea'] as $component) {
                foreach (self::findFluxComponentOccurrences($contents, $component) as $occurrence) {
                    if (preg_match('/(?:^|\s):?label\s*=/i', $occurrence['tag']) === 1) {
                        $offenders[] = self::formatFluxComponentOffender($relativePath, $contents, $occurrence, $component);
                    }
                }
            }
        }

        $this->assertSame([], $offenders);
    }

    public function test_inline_flux_icons_use_valid_names_and_variants(): void
    {
        $invalidIcons = [];
        $invalidIconVariants = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = File::get($file->getPathname());
            $relativePath = str($file->getPathname())
                ->after(base_path().DIRECTORY_SEPARATOR)
                ->toString();

            preg_match_all('/<flux:icon\b[^>]*>/s', $contents, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[0] as [$tag, $start]) {
                foreach (self::staticInlineFluxIconNames($tag) as $icon) {
                    if (! File::exists(base_path("vendor/livewire/flux/stubs/resources/views/flux/icon/{$icon}.blade.php"))) {
                        $invalidIcons[] = self::formatBladeTagOffender($relativePath, $contents, $start, $tag).' icon='.$icon;
                    }
                }

                foreach (self::staticInlineFluxIconVariants($tag) as $iconVariant) {
                    if (! in_array($iconVariant, ['outline', 'solid', 'mini', 'micro'], true)) {
                        $invalidIconVariants[] = self::formatBladeTagOffender($relativePath, $contents, $start, $tag).' variant='.$iconVariant;
                    }
                }
            }
        }

        $this->assertSame([], $invalidIcons);
        $this->assertSame([], $invalidIconVariants);
    }

    public function test_root_level_custom_rounded_panels_are_not_used_for_livewire_and_shared_components(): void
    {
        $offenders = collect([
            resource_path('views/livewire'),
            resource_path('views/components'),
        ])
            ->flatMap(fn (string $path) => File::allFiles($path))
            ->filter(fn ($file): bool => str_ends_with($file->getFilename(), '.blade.php'))
            ->flatMap(function ($file): array {
                $relativePath = str($file->getPathname())
                    ->after(base_path().DIRECTORY_SEPARATOR)
                    ->toString();

                return collect(file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [])
                    ->filter(fn (string $line): bool => preg_match('/^<\s*(article|section|div)\s+class="[^"]*rounded-lg[^"]*border/i', $line) === 1)
                    ->map(fn (string $line, int $index): string => $relativePath.':'.($index + 1).' '.trim($line))
                    ->all();
            })
            ->values()
            ->all();

        $this->assertSame([], $offenders);
    }

    /**
     * @return array<int, array{start: int, tagEnd: int, closeEnd: int, tag: string, body: string}>
     */
    private static function findFluxComponentOccurrences(string $contents, string $component): array
    {
        $occurrences = [];
        $needle = '<'.$component;
        $position = 0;
        $length = strlen($contents);

        while (($start = strpos($contents, $needle, $position)) !== false) {
            $next = $contents[$start + strlen($needle)] ?? '';

            if ($next !== '' && ! ctype_space($next) && $next !== '>' && $next !== '/') {
                $position = $start + strlen($needle);

                continue;
            }

            $tagEnd = self::findFluxOpeningTagEnd($contents, $start, $length);

            if ($tagEnd === null) {
                break;
            }

            $tag = substr($contents, $start, $tagEnd - $start);
            $closeEnd = $tagEnd;
            $body = '';

            if (preg_match('/\/\s*>$/', trim($tag)) !== 1) {
                $closeNeedle = '</'.$component.'>';
                $closeStart = strpos($contents, $closeNeedle, $tagEnd);

                if ($closeStart === false) {
                    $position = $tagEnd;

                    continue;
                }

                $body = substr($contents, $tagEnd, $closeStart - $tagEnd);
                $closeEnd = $closeStart + strlen($closeNeedle);
            }

            $occurrences[] = [
                'start' => $start,
                'tagEnd' => $tagEnd,
                'closeEnd' => $closeEnd,
                'tag' => $tag,
                'body' => $body,
            ];

            $position = $closeEnd;
        }

        return $occurrences;
    }

    private static function findFluxOpeningTagEnd(string $contents, int $start, int $length): ?int
    {
        $quote = null;
        $bladeEcho = false;

        for ($index = $start; $index < $length; $index++) {
            $char = $contents[$index];
            $next = $contents[$index + 1] ?? '';

            if ($bladeEcho) {
                if ($char === '}' && $next === '}') {
                    $bladeEcho = false;
                    $index++;
                }

                continue;
            }

            if ($quote !== null) {
                if ($char === $quote) {
                    $quote = null;
                }

                continue;
            }

            if ($char === '{' && $next === '{') {
                $bladeEcho = true;
                $index++;

                continue;
            }

            if ($char === '"' || $char === "'") {
                $quote = $char;

                continue;
            }

            if ($char === '>') {
                return $index + 1;
            }
        }

        return null;
    }

    /**
     * @param  array{start: int, tagEnd: int, closeEnd: int, tag: string, body: string}  $occurrence
     */
    private static function isFluxIconException(string $relativePath, array $occurrence): bool
    {
        $surface = strtolower($relativePath.' '.$occurrence['tag'].' '.$occurrence['body']);

        return str_contains($surface, '<flux:icon')
            || str_contains($surface, 'absolute inset-0')
            || str_contains($surface, 'navigation.appearance.label');
    }

    private static function fluxOpeningTagHasIcon(string $tag): bool
    {
        return preg_match('/(?:^|\s):?icon(?:\:trailing)?\s*=/i', $tag) === 1;
    }

    private static function fluxOpeningTagHasInputIcon(string $tag): bool
    {
        return preg_match('/(?:^|\s):?icon(?:\:leading|\:trailing)?\s*=/i', $tag) === 1;
    }

    /**
     * @param  array{start: int, tagEnd: int, closeEnd: int, tag: string, body: string}  $occurrence
     */
    private static function isFluxHeadingIconException(array $occurrence): bool
    {
        $tag = strtolower($occurrence['tag']);

        return str_contains($tag, 'line-clamp') || str_contains($tag, 'truncate');
    }

    /**
     * @param  array{start: int, tagEnd: int, closeEnd: int, tag: string, body: string}  $occurrence
     * @param  array<int, string>  $invalidIcons
     * @param  array<int, string>  $invalidIconVariants
     */
    private static function collectInvalidFluxIcons(
        string $relativePath,
        string $contents,
        array $occurrence,
        string $component,
        array &$invalidIcons,
        array &$invalidIconVariants,
    ): void {
        $validIconVariants = ['outline', 'solid', 'mini', 'micro'];

        foreach (self::staticFluxIconNames($occurrence['tag']) as $icon) {
            if (! File::exists(base_path("vendor/livewire/flux/stubs/resources/views/flux/icon/{$icon}.blade.php"))) {
                $invalidIcons[] = self::formatFluxComponentOffender($relativePath, $contents, $occurrence, $component).' icon='.$icon;
            }
        }

        foreach (self::staticFluxIconVariants($occurrence['tag']) as $iconVariant) {
            if (! in_array($iconVariant, $validIconVariants, true)) {
                $invalidIconVariants[] = self::formatFluxComponentOffender($relativePath, $contents, $occurrence, $component).' icon:variant='.$iconVariant;
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private static function staticFluxIconNames(string $tag): array
    {
        preg_match_all('/(?:^|\s)icon\s*=\s*([\'"])([^\'"]+)\1/i', $tag, $matches);

        return $matches[2] ?? [];
    }

    /**
     * @return array<int, string>
     */
    private static function staticFluxIconVariants(string $tag): array
    {
        preg_match_all('/(?:^|\s)icon\:variant\s*=\s*([\'"])([^\'"]+)\1/i', $tag, $matches);

        return $matches[2] ?? [];
    }

    /**
     * @return array<int, string>
     */
    private static function staticInlineFluxIconNames(string $tag): array
    {
        preg_match_all('/(?:^|\s)(?:name|icon)\s*=\s*([\'"])([^\'"]+)\1/i', $tag, $matches);

        return $matches[2] ?? [];
    }

    /**
     * @return array<int, string>
     */
    private static function staticInlineFluxIconVariants(string $tag): array
    {
        preg_match_all('/(?:^|\s)(?:variant|icon\:variant)\s*=\s*([\'"])([^\'"]+)\1/i', $tag, $matches);

        return $matches[2] ?? [];
    }

    /**
     * @param  array{start: int, tagEnd: int, closeEnd: int, tag: string, body: string}  $occurrence
     */
    private static function formatFluxComponentOffender(string $relativePath, string $contents, array $occurrence, string $component): string
    {
        $lineNumber = substr_count(substr($contents, 0, $occurrence['start']), "\n") + 1;
        $tag = trim(preg_replace('/\s+/', ' ', $occurrence['tag']) ?? $occurrence['tag']);

        return $relativePath.':'.$lineNumber.' '.$component.' '.$tag;
    }

    private static function formatBladeTagOffender(string $relativePath, string $contents, int $start, string $tag): string
    {
        $lineNumber = substr_count(substr($contents, 0, $start), "\n") + 1;
        $tag = trim(preg_replace('/\s+/', ' ', $tag) ?? $tag);

        return $relativePath.':'.$lineNumber.' '.$tag;
    }
}
