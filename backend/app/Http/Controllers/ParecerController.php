<?php

namespace App\Http\Controllers;

use App\Models\Parecer;
use App\Models\Solicitacao;
use App\Http\Requests\StoreParecerRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * ParecerController
 *
 * Gerencia a emissão de pareceres pelo Médico Regulador (HU07).
 *
 * Endpoint: POST /api/pareceres
 */
class ParecerController extends Controller
{
    /**
     * POST /api/pareceres
     * Registra o parecer emitido pelo Médico Regulador (HU07).
     *
     * Requisição:
     * {
     *   "id_solicitacao": 42,
     *   "texto_parecer":  "Paciente encaminhado para consulta..."
     * }
     *
     * Resposta (201):
     * {
     *   "id":      1,
     *   "message": "Parecer registrado com sucesso."
     * }
     */

#[OA\Post(
    path: "/api/pareceres",
    summary: "Registrar parecer médico",
    description: "Registra o parecer emitido pelo Médico Regulador e atualiza o status da solicitação.",
    tags: ["Pareceres"],
    security: [["sanctum" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["id_solicitacao", "texto_parecer"],
            properties: [
                new OA\Property(
                    property: "id_solicitacao",
                    type: "integer",
                    example: 42
                ),
                new OA\Property(
                    property: "texto_parecer",
                    type: "string",
                    example: "Paciente encaminhado para consulta especializada."
                )
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: "Parecer registrado com sucesso.",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "id", type: "integer", example: 1),
                    new OA\Property(property: "message", type: "string", example: "Parecer registrado com sucesso.")
                ]
            )
        ),
        new OA\Response(response: 401, description: "Usuário não autenticado."),
        new OA\Response(response: 422, description: "Dados enviados inválidos.")
    ]
)]

    public function store(StoreParecerRequest $request): JsonResponse
    {
        $parecer = Parecer::create([
            'id_solicitacao' => $request->id_solicitacao,
            'id_regulador'   => auth()->id(),
            'texto_parecer'  => $request->texto_parecer,
            'data_parecer'   => now(),
        ]);

        // Atualiza o status da solicitação após emissão do parecer
        Solicitacao::where('id_solicitacao', $request->id_solicitacao)
            ->update(['status' => 'Parecer Emitido']);

        return response()->json([
            'id'      => $parecer->id_parecer,
            'message' => 'Parecer registrado com sucesso.',
        ], 201);
    }
}
