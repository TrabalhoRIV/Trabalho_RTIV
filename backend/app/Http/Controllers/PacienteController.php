<?php

namespace App\Http\Controllers;

use App\Services\AghuService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * PacienteController
 *
 * Expõe o endpoint GET /api/pacientes/{prontuario}
 * responsável por buscar os dados do paciente no AGHU.
 *
 * Documentado em: 3.5 Principais Endpoints (Backlog TakeOff)
 */
class PacienteController extends Controller
{
    public function __construct(private readonly AghuService $aghuService) {}

    /**
     * Busca os dados do paciente pelo número de prontuário.
     *
     * GET /api/pacientes/{prontuario}
     *
     * Resposta de sucesso (200):
     * {
     *   "prontuario":      "123456",
     *   "nomePaciente":    "Ana Beatriz Ferreira",
     *   "cartaoSus":       "7000.1234.5678.9012",
     *   "cpf":             "123.456.789-00",
     *   "leito":           "Leito 402-A",
     *   "numeroConsulta":  "987654",
     *   "clinicaOrigem":   "Clínica Médica Interna"
     * }
     *
     * Resposta de erro (404):
     * { "message": "Paciente não encontrado no AGHU." }
     */

#[OA\Get(
    path: "/api/pacientes/{prontuario}",
    summary: "Consultar paciente pelo prontuário",
    description: "Busca os dados de um paciente no sistema AGHU.",
    tags: ["Pacientes"],
    security: [["sanctum" => []]],
    parameters: [
        new OA\Parameter(
            name: "prontuario",
            in: "path",
            required: true,
            description: "Número do prontuário do paciente.",
            schema: new OA\Schema(type: "string"),
            example: "123456"
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: "Dados do paciente encontrados.",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "prontuario", type: "string", example: "123456"),
                    new OA\Property(property: "nomePaciente", type: "string", example: "Ana Beatriz Ferreira"),
                    new OA\Property(property: "cartaoSus", type: "string", example: "7000.1234.5678.9012"),
                    new OA\Property(property: "cpf", type: "string", example: "123.456.789-00"),
                    new OA\Property(property: "leito", type: "string", example: "Leito 402-A"),
                    new OA\Property(property: "numeroConsulta", type: "string", example: "987654"),
                    new OA\Property(property: "clinicaOrigem", type: "string", example: "Clínica Médica Interna")
                ]
            )
        ),
        new OA\Response(
            response: 404,
            description: "Paciente não encontrado no AGHU."
        ),
        new OA\Response(
            response: 401,
            description: "Usuário não autenticado."
        )
    ]
)]

    public function show(string $prontuario): JsonResponse
    {
        $dados = $this->aghuService->buscarPaciente($prontuario);

        if (!$dados) {
            return response()->json(
                ['message' => 'Paciente não encontrado no AGHU.'],
                404
            );
        }

        return response()->json($dados, 200);
    }
}
