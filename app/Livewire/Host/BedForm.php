<?php

namespace App\Livewire\Host;

use App\Enums\BedType;
use App\Enums\CancellationPolicy;
use App\Models\Bed;
use App\Models\Room;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BedForm extends Component
{
    #[Locked]
    public int $roomId;

    #[Locked]
    public ?int $bedId = null;

    public string $title = '';

    public string $type = 'single';

    public int $maxGuests = 1;

    public float $pricePerNight = 0;

    public ?float $priceWeekend = null;

    public ?float $priceWeekly = null;

    public ?float $priceMonthly = null;

    public float $cleaningFee = 0;

    public float $deposit = 0;

    public ?float $discountWeekly = null;

    public ?float $discountMonthly = null;

    public int $minNights = 1;

    public ?int $maxNights = null;

    public bool $instantBook = true;

    public string $cancellationPolicy = 'flexible';

    public bool $hasLocker = false;

    public bool $hasOutlet = true;

    public bool $hasLamp = true;

    public bool $hasCurtain = false;

    public bool $hasShelf = false;

    public bool $hasLuggageSpace = false;

    public bool $hasLinen = true;

    public bool $hasTowel = false;

    public string $description = '';

    public function mount(Room $room, ?Bed $bed = null): void
    {
        $this->authorizeRoom($room);
        $this->roomId = $room->id;

        if ($bed?->exists) {
            abort_unless((int) $bed->room_id === (int) $room->id, 404);

            $this->bedId = $bed->id;
            $this->title = $bed->title;
            $this->type = $bed->type?->value ?? 'single';
            $this->maxGuests = $bed->max_guests ?? 1;
            $this->pricePerNight = (float) $bed->price_per_night;
            $this->priceWeekend = $bed->price_weekend ? (float) $bed->price_weekend : null;
            $this->priceWeekly = $bed->price_weekly ? (float) $bed->price_weekly : null;
            $this->priceMonthly = $bed->price_monthly ? (float) $bed->price_monthly : null;
            $this->cleaningFee = (float) ($bed->cleaning_fee ?? 0);
            $this->deposit = (float) ($bed->deposit ?? 0);
            $this->discountWeekly = $bed->discount_weekly ? (float) $bed->discount_weekly : null;
            $this->discountMonthly = $bed->discount_monthly ? (float) $bed->discount_monthly : null;
            $this->minNights = $bed->min_nights ?? 1;
            $this->maxNights = $bed->max_nights;
            $this->instantBook = (bool) $bed->instant_book;
            $this->cancellationPolicy = $bed->cancellation_policy?->value ?? 'flexible';
            $this->hasLocker = (bool) $bed->has_locker;
            $this->hasOutlet = (bool) $bed->has_outlet;
            $this->hasLamp = (bool) $bed->has_lamp;
            $this->hasCurtain = (bool) $bed->has_curtain;
            $this->hasShelf = (bool) $bed->has_shelf;
            $this->hasLuggageSpace = (bool) $bed->has_luggage_space;
            $this->hasLinen = (bool) $bed->has_linen;
            $this->hasTowel = (bool) $bed->has_towel;
            $this->description = $bed->description ?? '';
        }
    }

    public function save(): void
    {
        $room = $this->room;
        $this->authorizeRoom($room);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'pricePerNight' => ['required', 'numeric', 'min:1'],
            'minNights' => ['required', 'integer', 'min:1'],
        ]);

        $data = [
            'room_id' => $room->id,
            'title' => $this->title,
            'type' => $this->type,
            'max_guests' => $this->maxGuests,
            'price_per_night' => $this->pricePerNight,
            'price_weekend' => $this->priceWeekend,
            'price_weekly' => $this->priceWeekly,
            'price_monthly' => $this->priceMonthly,
            'cleaning_fee' => $this->cleaningFee,
            'deposit' => $this->deposit,
            'discount_weekly' => $this->discountWeekly,
            'discount_monthly' => $this->discountMonthly,
            'min_nights' => $this->minNights,
            'max_nights' => $this->maxNights,
            'instant_book' => $this->instantBook,
            'cancellation_policy' => $this->cancellationPolicy,
            'has_locker' => $this->hasLocker,
            'has_outlet' => $this->hasOutlet,
            'has_lamp' => $this->hasLamp,
            'has_curtain' => $this->hasCurtain,
            'has_shelf' => $this->hasShelf,
            'has_luggage_space' => $this->hasLuggageSpace,
            'has_linen' => $this->hasLinen,
            'has_towel' => $this->hasTowel,
            'description' => $this->description ?: null,
            'status' => 'active',
        ];

        $bed = $this->bed;

        if ($bed) {
            abort_unless((int) $bed->room_id === (int) $room->id, 404);

            $bed->update($data);
            session()->flash('success', __('notifications.flash.bed_updated'));
        } else {
            Bed::create($data);
            session()->flash('success', __('notifications.flash.bed_created'));
        }

        $this->redirect(route('host.properties.show', ['locale' => app()->getLocale(), 'property' => $room->property]), navigate: true);
    }

    public function bedTypes(): array
    {
        return collect(BedType::cases())->mapWithKeys(
            fn (BedType $t) => [$t->value => $t->label()]
        )->all();
    }

    public function cancellationPolicies(): array
    {
        return collect(CancellationPolicy::cases())->mapWithKeys(
            fn (CancellationPolicy $p) => [$p->value => ucfirst($p->value)]
        )->all();
    }

    public function render(): View
    {
        return view('livewire.host.bed-form', [
            'room' => $this->room,
            'bed' => $this->bed,
        ]);
    }

    #[Computed]
    public function room(): Room
    {
        return Room::query()
            ->select(['id', 'property_id', 'title'])
            ->with(['property:id,host_user_id,title'])
            ->findOrFail($this->roomId);
    }

    #[Computed]
    public function bed(): ?Bed
    {
        if ($this->bedId === null) {
            return null;
        }

        return Bed::query()
            ->select([
                'id',
                'room_id',
                'title',
                'type',
                'max_guests',
                'price_per_night',
                'price_weekend',
                'price_weekly',
                'price_monthly',
                'cleaning_fee',
                'deposit',
                'discount_weekly',
                'discount_monthly',
                'min_nights',
                'max_nights',
                'instant_book',
                'cancellation_policy',
                'has_locker',
                'has_outlet',
                'has_lamp',
                'has_curtain',
                'has_shelf',
                'has_luggage_space',
                'has_linen',
                'has_towel',
                'description',
            ])
            ->findOrFail($this->bedId);
    }

    private function authorizeRoom(Room $room): void
    {
        $room->loadMissing('property:id,host_user_id,title');

        abort_unless((int) $room->property?->host_user_id === (int) auth()->id(), 403);
    }
}
