<?php

namespace App\Services\Listings;

class ListingDetailSectionService
{
    /**
     * @var list<string>
     */
    private const DEFAULT_OPEN = [
        'description',
        'included',
        'keys',
        'problem',
    ];

    /**
     * @var list<string>
     */
    private const IMPORTANT_FOR_HOST = [
        'description',
        'included',
        'keys',
        'problem',
    ];

    /**
     * @param  list<array{key:string,items:list<array{label_key:string,text:string}>}>  $sections
     * @return list<array{key:string,title_key:string,items:list<array{label_key:string,text:string}>,open_by_default:bool}>
     */
    public function visibleSections(array $sections, bool $isHostViewer = false): array
    {
        $visible = [];

        foreach ($sections as $section) {
            $key = $section['key'];
            $items = $this->visibleItems($section['items']);

            if ($items === []) {
                if (! $isHostViewer || ! in_array($key, self::IMPORTANT_FOR_HOST, true)) {
                    continue;
                }

                $items[] = [
                    'label_key' => 'listing_detail.empty.missing_host_label',
                    'text' => __('listing_detail.empty.missing_host_helper'),
                ];
            }

            $visible[] = [
                'key' => $key,
                'title_key' => 'listing_detail.sections.'.$key,
                'items' => $items,
                'open_by_default' => in_array($key, self::DEFAULT_OPEN, true),
            ];
        }

        return $visible;
    }

    /**
     * @param  list<array{label_key:string,text:?string}>  $items
     * @return list<array{label_key:string,text:string}>
     */
    private function visibleItems(array $items): array
    {
        return array_values(array_filter(
            $items,
            fn (array $item): bool => is_string($item['text'] ?? null) && trim($item['text']) !== '',
        ));
    }
}
