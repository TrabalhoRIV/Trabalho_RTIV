<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Integra",
    version: "1.0.0",
    description: "Documentação da API do sistema Integra."
)]

#[OA\Server(
    url: "http://localhost:8000",
    description: "Servidor local"
)]

#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "http",
    scheme: "bearer",
    bearerFormat: "Token",
    description: "Token de autenticação Sanctum"
)]


class OpenApi
{
    #[OA\Get(
        path: "/api/auth/me",
        summary: "Consultar usuário autenticado",
        description: "Retorna os dados do usuário atualmente autenticado.",
        tags: ["Autenticação"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Dados do usuário retornados com sucesso."
            ),
            new OA\Response(
                response: 401,
                description: "Usuário não autenticado."
            )
        ]
    )]
    public function consultarUsuarioAutenticado(): void
    {
        //
    }
}

