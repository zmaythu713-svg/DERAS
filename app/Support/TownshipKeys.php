<?php

namespace App\Support;

class TownshipKeys
{
    /** @var array<string, string> Myanmar name => slug */
    private const NAME_TO_SLUG = [
        'မြန်အောင်' => 'myanaung',
        'ကြံခင်း' => 'kyankhin',
        'အင်္ဂပူ' => 'ingapu',
    ];

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_values(self::NAME_TO_SLUG);
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(self::NAME_TO_SLUG);
    }

    public static function slugToName(string $slug): ?string
    {
        foreach (self::NAME_TO_SLUG as $name => $key) {
            if ($key === $slug) {
                return $name;
            }
        }

        return null;
    }

    public static function nameToSlug(string $name): ?string
    {
        return self::NAME_TO_SLUG[$name] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public static function nameToSlugMap(): array
    {
        return self::NAME_TO_SLUG;
    }
}
