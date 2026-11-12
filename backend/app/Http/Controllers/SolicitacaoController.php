<?php

namespace App\Http\Controllers;

use App\Models\Solicitacao;
use App\Http\Requests\StoreSolicitacaoRequest;
use App\Http\Requests\UpdateSolicitacaoRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * SolicitacaoController
 *
 * Gerencia o ciclo de vida das solicitações de consultoria ambulatorial.
 *
 * Endpoints documentados (3.5 Principais Endpoints):
 *   GET  /api/solicitacoes          — lista para triagem do Médico Regulador
 *   GET  /api/solicitacoes/{id}     — detalhe de uma solicitação
 *   POST /api/solicitacoes          — cria nova solicitação (HU02)
 *   PUT  /api/solicitacoes/{id}     — atualiza solicitação existente
 *   POST /api/solicitacoes/{id}/devolver — devolve para complementação (HU09)
 */
class SolicitacaoController extends Controller
{
    /**
     * GET /api/solicitacoes
     * Lista solicitações aguardando triagem pelo Médico Regulador (HU04).
     */

#[OA\Get(
    path: "/api/solicitacoes",
    summary: "Listar solicitações",
    description: "Lista as solicitações de consultoria para triagem.",
    tags: ["Solicitações"],
    security: [["sanctum" => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: "Lista de solicitações retornada com sucesso."
        ),
        new OA\Response(
            response: 401,
            description: "Usuário não autenticado."
        )
    ]
)]

    public function index(): JsonResponse
    {
        $solicitacoes = Solicitacao::with(['paciente', 'solicitante'])
            ->orderBy('data_solicitacao', 'asc')
            ->get();

        return response()->json($solicitacoes);
    }

    /**
     * GET /api/solicitacoes/{id}
     * Retorna os detalhes completos de uma solicitação específica.
     */

#[OA\Get(
    path: "/api/solicitacoes/{solicitaco}",
    summary: "Consultar solicitação",
    description: "Retorna os detalhes de uma solicitação específica.",
    tags: ["Solicitações"],
    security: [["sanctum" => []]],
    parameters: [
        new OA\Parameter(
            name: "solicitaco",
            in: "path",
            required: true,
            description: "Identificador da solicitação.",
            schema: new OA\Schema(type: "integer"),
            example: 1
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: "Solicitação encontrada."
        ),
        new OA\Response(
            response: 401,
            description: "Usuário não autenticado."
        ),
        new OA\Response(
            response: 404,
            description: "Solicitação não encontrada."
        )
    ]
)]

    public function show(Solicitacao $solicitacao): JsonResponse
    {
        $solicitacao->load(['paciente', 'solicitante', 'pareceres', 'prioridade', 'historico']);
        return response()->json($solicitacao);
    }

    /**
     * POST /api/solicitacoes
     * Cria uma nova solicitação de consultoria (HU02).
     *
     * Requisição:
     * {
     *   "id_paciente":       1,
     *   "especialidade":     "Endocrinologia adulto",
     *   "descricao_clinica": "Paciente com DM tipo 2..."
     * }
     * O id_solicitante é extraído do token JWT do usuário autenticado.
     */

#[OA\Post(
    path: "/api/solicitacoes",
    summary: "Cadastrar solicitação",
    description: "Registra uma nova solicitação de consultoria ambulatorial.",
    tags: ["Solicitações"],
    security: [["sanctum" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["id_paciente", "especialidade", "descricao_clinica"],
            properties: [
                new OA\Property(
                    property: "id_paciente",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "especialidade",
                    type: "string",
                    maxLength: 100,
                    example: "Endocrinologia adulto"
                ),
                new OA\Property(
                    property: "descricao_clinica",
                    type: "string",
                    minLength: 20,
                    example: "Paciente com diabetes tipo 2."
                )
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: "Solicitação cadastrada com sucesso."
        ),
        new OA\Response(
            response: 401,
            description: "Usuário não autenticado ou sem autorização."
        ),
        new OA\Response(
            response: 422,
            description: "Dados enviados inválidos."
        )
    ]
)]

    public function store(StoreSolicitacaoRequest $request): JsonResponse
    {
        $solicitacao = Solicitacao::create([
            'id_paciente'      => $request->id_paciente,
            'id_solicitante'   => auth()->id(),   // vem do login — não do form
            'especialidade'    => $request->especialidade,
            'descricao_clinica'=> $request->descricao_clinica,
            'status'           => 'Aguardando Triagem',
            'data_solicitacao' => now(),
        ]);

        return response()->json([
            'id'     => $solicitacao->id_solicitacao,
            'status' => $solicitacao->status,
        ], 201);
    }

    /**
     * PUT /api/solicitacoes/{id}
     * Atualiza uma solicitação existente.
     */
  /**Dados ficticios pq ta sem o request */
#[OA\Put(
    path: "/api/solicitacoes/{solicitaco}",
    summary: "Atualizar solicitação",
    description: "Atualiza os dados de uma solicitação existente.",
    tags: ["Solicitações"],
    security: [["sanctum" => []]],
    parameters: [
        new OA\Parameter(
            name: "solicitaco",
            in: "path",
            required: true,
            description: "Identificador da solicitação.",
            schema: new OA\Schema(type: "integer"),
            example: 1
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "especialidade", type: "string", example: "Cardiologia"),
                new OA\Property(property: "descricao_clinica", type: "string", example: "Descrição clínica atualizada.")
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: "Solicitação atualizada."),
        new OA\Response(response: 401, description: "Usuário não autenticado."),
        new OA\Response(response: 404, description: "Solicitação não encontrada."),
        new OA\Response(response: 422, description: "Dados inválidos.")
    ]
)]
#[OA\Patch(
    path: "/api/solicitacoes/{solicitaco}",
    summary: "Atualizar parcialmente solicitação",
    description: "Atualiza parcialmente os dados de uma solicitação.",
    tags: ["Solicitações"],
    security: [["sanctum" => []]],
    parameters: [
        new OA\Parameter(
            name: "solicitaco",
            in: "path",
            required: true,
            schema: new OA\Schema(type: "integer"),
            example: 1
        )
    ],
    responses: [
        new OA\Response(response: 200, description: "Solicitação atualizada."),
        new OA\Response(response: 401, description: "Usuário não autenticado."),
        new OA\Response(response: 404, description: "Solicitação não encontrada.")
    ]
)]

    public function update(UpdateSolicitacaoRequest $request, Solicitacao $solicitacao): JsonResponse
    {
        $solicitacao->update($request->validated());
        return response()->json($solicitacao);
    }

    /**
     * POST /api/solicitacoes/{id}/devolver
     * Devolve a solicitação ao médico solicitante para complementação (HU09).
     *
     * Requisição: { "motivo": "Informar tipo de insulina utilizada." }
     * Resposta:   { "status": "Pendente" }
     */

#[OA\Post(
    path: "/api/solicitacoes/{solicitacao}/devolver",
    summary: "Devolver solicitação",
    description: "Devolve a solicitação ao médico solicitante para complementação.",
    tags: ["Solicitações"],
    security: [["sanctum" => []]],
    parameters: [
        new OA\Parameter(
            name: "solicitacao",
            in: "path",
            required: true,
            schema: new OA\Schema(type: "integer"),
            example: 1
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: "Solicitação devolvida.",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", example: "Pendente")
                ]
            )
        ),
        new OA\Response(response: 401, description: "Usuário não autenticado."),
        new OA\Response(response: 404, description: "Solicitação não encontrada.")
    ]
)]

    public function devolver(Solicitacao $solicitacao): JsonResponse
    {
        $solicitacao->update(['status' => 'Pendente']);

        return response()->json(['status' => $solicitacao->status]);
    }
}
