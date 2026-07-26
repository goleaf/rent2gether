<?php

namespace App\Services\HostHints;

use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Services\HostHints\Concerns\BuildsHostHints;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

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
        $hasRuleRecords = $property->relationLoaded('rules')
            ? collect($property->getRelation('rules'))->isNotEmpty()
            : $property->rules()->exists();

        return ! $hasRuleRecords && ! $this->hasTranslatedHouseRuleLike($property, ['']);
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
        $needles = $this->needlesFor($needle);
        $jsonRules = collect($property->getAttribute('rules') ?? [])
            ->flatten()
            ->filter(fn (mixed $value): bool => Str::contains(Str::lower((string) $value), $needles))
            ->isNotEmpty();

        if ($jsonRules) {
            return true;
        }

        if ($property->relationLoaded('rules')) {
            $hasLoadedRule = collect($property->getRelation('rules'))
                ->filter(function ($rule) use ($needles): bool {
                    return Str::contains(Str::lower((string) $rule->slug), $needles)
                        || Str::contains(Str::lower((string) $rule->category), $needles);
                })
                ->isNotEmpty();

            if ($hasLoadedRule) {
                return true;
            }
        } else {
            $hasStoredRule = $property->rules()
                ->where(function (Builder $query) use ($needles): void {
                    foreach ($needles as $ruleNeedle) {
                        $query->orWhere('slug', 'like', '%'.$ruleNeedle.'%')
                            ->orWhere('category', 'like', '%'.$ruleNeedle.'%');
                    }
                })
                ->exists();

            if ($hasStoredRule) {
                return true;
            }
        }

        return $this->hasTranslatedHouseRuleLike($property, $needles);
    }

    /**
     * @param  list<string>  $needles
     */
    private function hasTranslatedHouseRuleLike(Property $property, array $needles): bool
    {
        if ($property->relationLoaded('translations')) {
            return $property->translations
                ->filter(function ($translation) use ($needles): bool {
                    $text = Str::lower((string) $translation->house_rules_text);

                    return $needles === ['']
                        ? filled($text)
                        : Str::contains($text, $needles);
                })
                ->isNotEmpty();
        }

        $query = $property->translations()
            ->whereNotNull('house_rules_text')
            ->where('house_rules_text', '!=', '');

        if ($needles === ['']) {
            return $query->exists();
        }

        return $query
            ->where(function (Builder $builder) use ($needles): void {
                foreach ($needles as $textNeedle) {
                    $builder->orWhere('house_rules_text', 'like', '%'.$textNeedle.'%');
                }
            })
            ->exists();
    }

    /**
     * @return list<string>
     */
    private function needlesFor(string $needle): array
    {
        return match ($needle) {
            'bathroom' => ['bathroom', 'shower', 'ванн', 'душ'],
            'kitchen' => ['kitchen', 'cook', 'кухн', 'готов'],
            'lost_key' => ['lost_key', 'lost key', 'key lost', 'потер', 'ключ'],
            'pet' => ['pet', 'pets', 'animal', 'животн', 'питом'],
            'quiet' => ['quiet', 'noise', 'тиш', 'шум'],
            'smoking' => ['smoking', 'smoke', 'кур'],
            default => [$needle],
        };
    }
}
