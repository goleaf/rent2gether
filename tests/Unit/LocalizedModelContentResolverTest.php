<?php

namespace Tests\Unit;

use App\Services\Localization\LocalizedModelContentResolver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;

class LocalizedModelContentResolverTest extends TestCase
{
    public function test_it_returns_the_requested_locale(): void
    {
        $translations = $this->translations([
            ['locale' => 'en', 'title' => 'English title'],
            ['locale' => 'ru', 'title' => 'Русский заголовок'],
        ]);

        $translation = (new LocalizedModelContentResolver('en'))
            ->resolve($translations, 'ru', 'en');

        $this->assertSame('Русский заголовок', $translation?->getAttribute('title'));
    }

    public function test_it_falls_back_to_the_application_fallback_locale(): void
    {
        $translations = $this->translations([
            ['locale' => 'en', 'title' => 'English title'],
            ['locale' => 'fr', 'title' => 'Titre français'],
        ]);

        $translation = (new LocalizedModelContentResolver('en'))
            ->resolve($translations, 'de', 'fr');

        $this->assertSame('English title', $translation?->getAttribute('title'));
    }

    public function test_it_falls_back_to_the_source_locale(): void
    {
        $translations = $this->translations([
            ['locale' => 'fr', 'title' => 'Titre français'],
        ]);

        $translation = (new LocalizedModelContentResolver('en'))
            ->resolve($translations, 'de', 'fr');

        $this->assertSame('Titre français', $translation?->getAttribute('title'));
    }

    /**
     * @param  list<array{locale: string, title: string}>  $items
     * @return Collection<int, TranslationStub>
     */
    private function translations(array $items): Collection
    {
        return new Collection(array_map(
            fn (array $attributes): TranslationStub => new TranslationStub($attributes),
            $items,
        ));
    }
}

class TranslationStub extends Model
{
    protected $guarded = [];
}
