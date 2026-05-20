<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum UserTeam: string implements HasLabel
{
    case TeamOne = 'team_1';
    case TeamTwo = 'team_2';
    case TeamThree = 'team_3';

    public function getLabel(): string
    {
        return match ($this) {
            self::TeamOne => 'Team 1',
            self::TeamTwo => 'Team 2',
            self::TeamThree => 'Team 3',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $team): array => [$team->value => $team->getLabel()])
            ->all();
    }
}
