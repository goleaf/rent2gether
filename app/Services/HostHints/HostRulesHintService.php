<?php

namespace App\Services\HostHints;

use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Services\HostHints\Concerns\BuildsHostHints;
use Illuminate\Database\Eloquent\Builder;

class HostRulesHintService
{
    use BuildsHostHints;

    /**
     * @return list<array<string, mixed>>
     */
    public function forSleepingPlace(SleepingPlace $place): array
    {
        $property = $place->property;
        $room = $place->room;

        return collect([
            $property instanceof Property && $this->missingHouseRules($property) ? $this->hint('add_exact_rules', 'rules', 'required', 'high', 130, 'edit_rules', true, true, true, true) : null,
            $property instanceof Property && $this->missingKitchenRules($property) ? $this->hint('missing_kitchen_rules', 'rules', 'suggestion', 'medium', 105, 'edit_rules', true, true, true) : null,
            $property instanceof Property && $this->missingBathroomRules($property) ? $this->hint('missing_bathroom_rules', 'rules', 'suggestion', 'medium', 100, 'edit_rules', true, true, true) : null,
            $property instanceof Property && $this->missingQuietHours($property, $room) ? $this->hint('missing_quiet_hours', 'rules', 'suggestion', 'medium', 90, 'edit_rules') : null,
            $property instanceof Property && ! $this->hasRuleLike($property, 'smoking') ? $this->hint('missing_smoking_rules', 'rules', 'suggestion', 'low', 50, 'edit_rules') : null,
            $property instanceof Property && ! $this->hasRuleLike($property, 'pet') ? $this->hint('missing_pet_rules', 'rules', 'suggestion', 'low', 45, 'edit_rules') : null,
            $property instanceof Property && ! $this->hasRuleLike($property, 'lost_key') ? $this->hint('missing_lost_key_rules', 'rules', 'suggestion', 'low', 40, 'edit_rules') : null,
            $room instanceof Room && blank($room->room_rules_text) ? $this->hint('missing_room_rules', 'room', 'suggestion', 'medium', 85, 'edit_room_rules') : null,
        ])->filter()->values()->all();
    }

    public function missingHouseRules(Property $property): bool
    {
        return $property->rules()->doesntExist()
            && $property->translations()->whereNotNull('house_rules_text')->where('house_rules_text', '!=', '')->doesntExist();
    }

    public function missingKitchenRules(Property $property): bool
    {
        return ! $this->hasRuleLike($property, 'kitchen');
    }

    public function missingBathroomRules(Property $property): bool
    {
        return ! $this->hasRuleLike($property, 'bathroom');
    }

    public function missingQuietHours(Property $property, ?Room $room): bool
    {
        return ! $this->hasRuleLike($property, 'quiet')
            && blank($room?->room_rules_text)
            && $property->translations()->where('house_rules_text', 'like', '%quiet%')->doesntExist();
    }

    private function hasRuleLike(Property $property, string $needle): bool
    {
        $jsonRules = collect($property->getAttribute('rules') ?? [])
            ->filter(fn (mixed $value): bool => str_contains((string) $value, $needle))
            ->isNotEmpty();

        if ($jsonRules) {
            return true;
        }

        return $property->rules()
            ->where(function (Builder $query) use ($needle): void {
                $query->where('slug', 'like', '%'.$needle.'%')
                    ->orWhere('category', 'like', '%'.$needle.'%');
            })
            ->exists();
    }
}
