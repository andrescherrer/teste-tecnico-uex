<?php

namespace App\Http\Services\Location;

use Illuminate\Support\Facades\Http;

class ViaCepService
{
    protected string $baseUrl = 'https://viacep.com.br/ws';

    public function execute(string $cep): ?array
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);

        if (strlen($cep) !== 8) {
            return null;
        }

        $url = "{$this->baseUrl}/{$cep}/json/";

        $response = Http::get($url);

        if ($response->successful() && !$response->json('erro')) {
            return $response->json();
        }

        return null;
    }
}
