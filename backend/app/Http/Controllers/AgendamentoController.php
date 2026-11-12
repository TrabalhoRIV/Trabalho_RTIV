<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Http\Requests\StoreAgendamentoRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * AgendamentoController
 *
 * Gerencia o agendamento de consultorias pelo NIR (HU08 / HU09).
 *
 * Endpoint: POST /api/agendamentos
 */
class AgendamentoController extends Controller
{
    /**
     * POST /api/agendamentos
     * Realiza o agendamento da consulta (NIR — HU09).
     *
     * Requisição:
     * {
     *   "id_solicitacao":    42,
     *   "data_agendamento":  "2026-12-15T14:00:00",
     *   "local":             "Ambulatório Endocrinologia — Sala 3"
     * }
     *
     * Resposta (201):
     * {
     *   "id":     1,
     *   "status": "Agendado"
     * }
     */

#[OA\Post(
    path: "/api/agendamentos",
    summary: "Cadastrar agendamento",
    description: "Realiza o agendamento de uma consulta pelo NIR.",
    tags: ["Agendamentos"],
    security: [["sanctum" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["id_solicitacao", "data_agendamento", "local"],
            properties: [
                new OA\Property(
                    property: "id_solicitacao",
                    type: "integer",
                    example: 42
                ),
                new OA\Property(
                    property: "data_agendamento",
                    type: "string",
                    format: "date-time",
                    example: "2026-12-15T14:00:00"
                ),
                new OA\Property(
                    property: "local",
                    type: "string",
                    example: "Ambulatório Endocrinologia — Sala 3"
                )
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: "Agendamento realizado com sucesso.",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "id", type: "integer", example: 1),
                    new OA\Property(property: "status", type: "string", example: "Agendado")
                ]
            )
        ),
        new OA\Response(response: 401, description: "Usuário não autenticado."),
        new OA\Response(response: 422, description: "Dados enviados inválidos.")
    ]
)]

    public function store(StoreAgendamentoRequest $request): JsonResponse
    {
        $agendamento = Agendamento::create([
            'id_solicitacao'   => $request->id_solicitacao,
            'data_agendamento' => $request->data_agendamento,
            'local'            => $request->local,
            'status'           => 'Agendado',
        ]);

        return response()->json([
            'id'     => $agendamento->id_agendamento,
            'status' => $agendamento->status,
        ], 201);
    }
}
