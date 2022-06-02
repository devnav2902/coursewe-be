<?php

namespace App\Http\Controllers;

use AmrShawky\LaravelCurrency\Facade\Currency;

class CurrencyController extends Controller
{
    function convert($from, $to, $money)
    {
        $money = str_replace('.', '', $money);

        return Currency::convert()
            ->from($from)
            ->to($to)
            ->amount($money)
            ->round(2)
            ->get();
    }
}
