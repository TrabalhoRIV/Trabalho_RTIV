<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Serviços de Terceiros — Integra / NIR
    |--------------------------------------------------------------------------
    |
    | As credenciais e URLs de serviços externos são lidas do arquivo .env
    | para não ficarem expostas no controle de versão.
    |
    */

    // ─── AGHU ────────────────────────────────────────────────────────────────
    // Sistema de Gestão Hospitalar (fonte dos dados do paciente — HU01)
    'aghu' => [
        'base_url' => env('AGHU_BASE_URL', 'https://aghu.hospital.gov.br/api'),
        'token'    => env('AGHU_TOKEN'),
        'timeout'  => (int) env('AGHU_TIMEOUT', 5),
    ],

    // ─── IA ──────────────────────────────────────────────────────────────────
    // Serviço de Inteligência Artificial (HU03, HU05, HU06, HU07)
    'ia' => [
        'provider' => env('AI_PROVIDER', 'gemini'),  // gemini | openai
        'api_key'  => env('AI_API_KEY'),
        'timeout'  => (int) env('AI_TIMEOUT', 10),
    ],

];
