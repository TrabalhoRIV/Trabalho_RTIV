<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(
                ['message' => 'Credenciais inválidas.'],
                401
            );
        }

        $usuario = Auth::user();

        // Cria o token de acesso via Sanctum
        $token = $usuario->createToken('integra-nir-token')->plainTextToken;

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
