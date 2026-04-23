<?php

namespace App\Traits;

trait HasAverage
{
    public function getAverage(iterable $scores): float
    {
        return (float) (collect($scores)->average() ?? 0);
    }
}
