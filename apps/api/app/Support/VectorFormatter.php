<?php

namespace App\Support;

class VectorFormatter
{
    /**
     * @param  list<float>  $vector
     */
    public static function toPgVector(array $vector): string
    {
        return '['.implode(',', array_map(
            static fn (float $value): string => sprintf('%.8f', $value),
            $vector,
        )).']';
    }
}
