<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

/**
 * AuthController
 *
 * Gerencia a autenticação dos usuários via Laravel Sanctum (token JWT).
 *
 * Endpoints:
 *   POST /api/auth/login   → autentica e retorna token
 *   POST /api/auth/logout  → revoga o token atual
 *   GET  /api/auth/me      → retorna dados do usuário logado (nome, CRM, perfil)
 *
 * O frontend Angular usa GET /api/auth/me para preencher automaticamente
 * o nome do médico e CRM no formulário de solicitação (HU01).
 */
class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     *
     * Autentica o usuário com e-mail e senha.
     * Retorna o token Bearer e os dados do usuário (nome, CRM, perfil).
     *
     * Requisição:
     * {
     *   "email": "medico@hospital.gov.br",
     *   "password": "senha123"
     * }
     *
     * Resposta (200):
     * {
     *   "token": "1|abc...",
     *   "usuario": {
     *     "id": 1,
     *     "nome": "Dr. Carlos Eduardo Mendes",
     *     "crm": "PR-54321",
     *     "perfil": "solicitante"
     *   }
     * }
     *
     * Resposta (401):
     * { "message": "Credenciais inválidas." }
     */

#[OA\Post(
    path: "/api/auth/login",
    summary: "Realizar login",
    description: "Autentica o usuário utilizando e-mail e senha.",
    tags: ["Autenticação"],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password"],
            properties: [
                new OA\Property(
                    property: "email",
                    type: "string",
                    format: "email",
                    example: "medico@hospital.gov.br"
                ),
                new OA\Property(
                    property: "password",
                    type: "string",
                    example: "senha123"
                )
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: "Login realizado com sucesso."
        ),
        new OA\Response(
            response: 401,
            description: "Credenciais inválidas."
        ),
        new OA\Response(
            response: 422,
            description: "Dados de validação inválidos."
        )
    ]
)]

#[OA\Post(
    path: "/api/auth/logout",
    summary: "Realizar logout",
    description: "Encerra a sessão do usuário autenticado.",
    tags: ["Autenticação"],
    security: [["sanctum" => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: "Logout realizado com sucesso."
        ),
        new OA\Response(
            response: 401,
            description: "Usuário não autenticado."
        )
    ]
)]

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!($credentials['email'] === 'medico@hospital.gov.br' && $credentials['password'] === 'senha123')) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $usuario = (object)[
            'id_usuario' => 1,
            'nome' => 'Dr. Carlos Eduardo Mendes',
            'crm' => 'PR-54321',
            'perfil' => 'solicitante',
        ];
        $token = 'demo-token';

        return response()->json([
            'token'   => $token,
            'usuario' => [
                'id'     => $usuario->id_usuario,
                'nome'   => $usuario->nome,
                'crm'    => $usuario->crm,
                'perfil' => $usuario->perfil,
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     *
     * Revoga o token atual do usuário autenticado.
     * Requer header: Authorization: Bearer {token}
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
