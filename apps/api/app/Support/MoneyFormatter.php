<?php

namespace App\Support;

class MoneyFormatter
{
    public static function format(int $amount): string
    {
        return sprintf('%d.00', $amount);
    }
}
