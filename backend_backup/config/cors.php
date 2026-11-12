<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS — Cross-Origin Resource Sharing
    |--------------------------------------------------------------------------
    |
    | Permite que o frontend Angular (rodando em localhost:4200) faça
    | requisições ao backend Laravel sem bloqueio de segurança do navegador.
    |
    */

    'paths'                    => ['api/*'],
    'allowed_methods'          => ['*'],
    'allowed_origins'          => [env('FRONTEND_URL', 'http://localhost:4200')],
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
    'supports_credentials'     => true,

];
