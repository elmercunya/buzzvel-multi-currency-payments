<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExchangeRateService {
    public function getRate(string $currency): float {

        $key = env('EXCHANGE_RATE_API_KEY');

        $response = Http::get('https://v6.exchangerate-api.com/v6/'.$key.'/latest/EUR');

        return $response->json('conversion_rates.'.$currency);
    }
}