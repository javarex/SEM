<?php

namespace App\Models;

use Database\Factories\StudentProfileTabSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class StudentProfileTabSetting extends Model
{
    /** @use HasFactory<StudentProfileTabSettingFactory> */
    use HasFactory;

    /**
     * @var array<string, array{tab_name: string, sort_order: int}>
     */
    public const DEFAULT_TABS = [
        'details' => [
            'tab_name' => 'Details',
            'sort_order' => 10,
        ],
        'assessment' => [
            'tab_name' => 'Assessment',
            'sort_order' => 20,
        ],
        'remarks' => [
            'tab_name' => 'Remarks',
            'sort_order' => 30,
        ],
        'system' => [
            'tab_name' => 'System',
            'sort_order' => 40,
        ],
    ];

    protected $fillable = [
        'tab_key',
        'tab_name',
        'is_visible',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    public static function defaultTabOptions(): array
    {
        return collect(self::DEFAULT_TABS)
            ->mapWithKeys(fn (array $tab, string $key): array => [$key => $tab['tab_name']])
            ->all();
    }

    public static function seedMissingDefaults(): void
    {
        foreach (self::DEFAULT_TABS as $tabKey => $tab) {
            self::query()->firstOrCreate(
                ['tab_key' => $tabKey],
                [
                    'tab_name' => $tab['tab_name'],
                    'is_visible' => true,
                    'sort_order' => $tab['sort_order'],
                ],
            );
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $sections
     * @return Collection<string, array<string, mixed>>
     */
    public static function visibleSectionsFor(array $sections): Collection
    {
        $settings = self::query()
            ->whereIn('tab_key', array_keys($sections))
            ->get()
            ->keyBy('tab_key');

        return collect($sections)
            ->map(function (array $section, string $key) use ($settings): array {
                $setting = $settings->get($key);
                $default = self::DEFAULT_TABS[$key] ?? [
                    'tab_name' => $section['label'] ?? str($key)->replace('_', ' ')->title()->toString(),
                    'sort_order' => 1000,
                ];

                return [
                    ...$section,
                    'label' => $setting?->tab_name ?? $default['tab_name'],
                    'tab_visible' => $setting?->is_visible ?? true,
                    'tab_sort_order' => $setting?->sort_order ?? $default['sort_order'],
                ];
            })
            ->filter(fn (array $section): bool => $section['tab_visible'])
            ->sortBy('tab_sort_order')
            ->map(fn (array $section): array => collect($section)
                ->except(['tab_visible', 'tab_sort_order'])
                ->all());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
