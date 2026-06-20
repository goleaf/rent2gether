<?php

namespace App\View\Components\App;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class MobileNav extends Component
{
    /** @var list<array{href: string, icon: string, label: string, active: bool}> */
    public array $items;

    public string $gridColumns;

    public bool $isHostMode;

    public function __construct(private readonly Request $request)
    {
        $this->isHostMode = $this->request->routeIs('host.*');
        $this->items = $this->buildItems(app()->getLocale());
        $this->gridColumns = count($this->items) === 6 ? 'grid-cols-6' : 'grid-cols-5';
    }

    /**
     * @return list<array{href: string, icon: string, label: string, active: bool}>
     */
    private function buildItems(string $locale): array
    {
        $items = $this->isHostMode
            ? [
                ['route' => 'host.listings.index', 'active' => ['host.listings.*'], 'icon' => 'building-office-2', 'label' => __('navigation.host_listings')],
                ['route' => 'host.calendar', 'active' => ['host.calendar'], 'icon' => 'calendar-days', 'label' => __('navigation.host_calendar')],
                ['route' => 'host.requests.index', 'active' => ['host.requests.*'], 'icon' => 'clipboard-document-list', 'label' => __('navigation.host_requests')],
                ['route' => 'messages.index', 'active' => ['messages.*'], 'icon' => 'chat-bubble-left-right', 'label' => __('navigation.messages')],
                ['route' => 'host.profile', 'active' => ['host.profile'], 'icon' => 'user-circle', 'label' => __('navigation.profile')],
            ]
            : [
                ['route' => 'search.index', 'active' => ['search.*'], 'icon' => 'magnifying-glass', 'label' => __('navigation.search')],
                ['route' => 'saved-searches.index', 'active' => ['saved-searches.*'], 'icon' => 'bookmark', 'label' => __('navigation.saved_searches')],
                ['route' => 'trips.index', 'active' => ['trips.*', 'guest.bookings.*', 'bookings.*'], 'icon' => 'calendar-days', 'label' => __('navigation.trips')],
                ['route' => 'favorites.index', 'active' => ['favorites.*'], 'icon' => 'heart', 'label' => __('navigation.favorites')],
                ['route' => 'messages.index', 'active' => ['messages.*'], 'icon' => 'chat-bubble-left-right', 'label' => __('navigation.messages')],
                ['route' => 'profile.edit', 'active' => ['profile.*'], 'icon' => 'user-circle', 'label' => __('navigation.profile')],
            ];

        return array_map(fn (array $item): array => [
            'href' => route($item['route'], ['locale' => $locale]),
            'icon' => $item['icon'],
            'label' => $item['label'],
            'active' => $this->request->routeIs(...$item['active']),
        ], $items);
    }

    public function render(): View
    {
        return view('components.app.mobile-nav', [
            'gridColumns' => $this->gridColumns,
            'isHostMode' => $this->isHostMode,
            'items' => $this->items,
        ]);
    }
}
