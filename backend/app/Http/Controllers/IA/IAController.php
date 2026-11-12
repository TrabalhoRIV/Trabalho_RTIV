<?php

namespace App\Http\Controllers\IA;

use App\Http\Controllers\Controller;
use App\Http\Requests\IA\DescricaoRequest;
use App\Http\Requests\IA\ResumoRequest;
use App\Http\Requests\IA\PrioridadeRequest;
use App\Http\Requests\IA\ParecerRequest;
use App\Services\IAService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/**
 * IAController
 *
 * Intermediador entre o frontend e os serviços de IA.
 * Todos os endpoints recebem dados do backend, repassam à IA e
 * retornam sugestões — nunca decisões finais (HU03, HU05, HU06, HU07).
 *
 * Endpoints (3.5 Principais Endpoints):
 *   POST /api/ia/descricao  — Auxilia na descrição clínica (HU03)
 *   POST /api/ia/resumo     — Gera resumo do caso (HU05)
 *   POST /api/ia/prioridade — Sugere P1, P2 ou P3 (HU06)
 *   POST /api/ia/parecer    — Auxilia na elaboração do parecer (HU07)
 *
 * Política de IA (seção 2.4):
 *   - A IA utiliza somente as informações fornecidas pelo sistema.
 *   - A IA NÃO inventa informações clínicas.
 *   - Todos os resultados são tratados como sugestões (revisão humana obrigatória).
 *   - Casos ambíguos ou com informações insuficientes são sinalizados como
 *     "indeterminado" e escalados para avaliação humana (EC4, EC8).
 */
class IAController extends Controller
{
    public function __construct(private readonly IAService $iaService) {}

    /**
     * POST /api/ia/descricao
     *
     * Analisa a descrição clínica redigida pelo médico e sugere complementações,
     * como tempo de evolução, comorbidades ou exames ausentes (HU03 / EC11).
     *
     * Requisição:  { "descricao_clinica": "Paciente com DM tipo 2..." }
     * Resposta:    { "sugestao": "Complementar com...", "tipo": "DESCRICAO_CLINICA" }
     */

#[OA\Post(
    path: "/api/ia/descricao",
    summary: "Auxiliar na descrição clínica",
    description: "Analisa a descrição clínica e sugere complementações. O resultado é apenas uma sugestão, exigindo revisão humana.",
    tags: ["Inteligência Artificial"],
    security: [["sanctum" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["descricao_clinica"],
            properties: [
                new OA\Property(
                    property: "descricao_clinica",
                    type: "string",
                    example: "Paciente com DM tipo 2..."
                )
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: "Sugestão gerada com sucesso."),
        new OA\Response(response: 401, description: "Usuário não autenticado."),
        new OA\Response(response: 422, description: "Dados inválidos.")
    ]
)]

    public function descricao(DescricaoRequest $request): JsonResponse
    {
        $sugestao = $this->iaService->auxiliarDescricao(
            $request->validated('descricao_clinica')
        );

        return response()->json([
            'sugestao' => $sugestao,
            'tipo'     => 'DESCRICAO_CLINICA',
        ]);
    }

    /**
     * POST /api/ia/resumo
     *
     * Gera um resumo estruturado da solicitação para o Médico Regulador (HU05).
     * Utiliza somente informações já presentes na solicitação (EC7, EC11).
     *
     * Requisição:  { "id_solicitacao": 42 }
     * Resposta:    { "resumo": "...", "tipo": "RESUMO" }
     */

#[OA\Post(
    path: "/api/ia/resumo",
    summary: "Gerar resumo da solicitação",
    description: "Gera um resumo estruturado da solicitação para auxiliar o Médico Regulador.",
    tags: ["Inteligência Artificial"],
    security: [["sanctum" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["id_solicitacao"],
            properties: [
                new OA\Property(
                    property: "id_solicitacao",
                    type: "integer",
                    example: 42
                )
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: "Resumo gerado com sucesso."),
        new OA\Response(response: 401, description: "Usuário não autenticado."),
        new OA\Response(response: 422, description: "Dados inválidos.")
    ]
)]

    public function resumo(ResumoRequest $request): JsonResponse
    {
        $resumo = $this->iaService->gerarResumo(
            $request->validated('id_solicitacao')
        );

        return response()->json([
            'resumo' => $resumo,
            'tipo'   => 'RESUMO',
        ]);
    }

    /**
     * POST /api/ia/prioridade
     *
     * Sugere a classificação de urgência P1, P2 ou P3 com justificativa (HU06).
     * A decisão final é sempre do Médico Regulador.
     *
     * Casos com informações insuficientes ou ambíguas retornam
     * prioridade = "INDETERMINADO" (EC4 / EC8).
     *
     * Requisição:  { "id_solicitacao": 42 }
     * Resposta:    { "prioridade": "P2", "justificativa": "...", "confianca": 0.87, "tipo": "PRIORIDADE" }
     */

#[OA\Post(
    path: "/api/ia/prioridade",
    summary: "Sugerir prioridade da solicitação",
    description: "Sugere uma classificação P1, P2, P3 ou INDETERMINADO. A decisão final é sempre do Médico Regulador.",
    tags: ["Inteligência Artificial"],
    security: [["sanctum" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["id_solicitacao"],
            properties: [
                new OA\Property(
                    property: "id_solicitacao",
                    type: "integer",
                    example: 42
                )
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: "Prioridade sugerida com sucesso."),
        new OA\Response(response: 401, description: "Usuário não autenticado."),
        new OA\Response(response: 422, description: "Dados inválidos.")
    ]
)]

    public function prioridade(PrioridadeRequest $request): JsonResponse
    {
        $resultado = $this->iaService->sugerirPrioridade(
            $request->validated('id_solicitacao')
        );

        return response()->json([
            'prioridade'   => $resultado['prioridade'],   // P1 | P2 | P3 | INDETERMINADO
            'justificativa'=> $resultado['justificativa'],
            'confianca'    => $resultado['confianca'],    // 0.0 – 1.0
            'tipo'         => 'PRIORIDADE',
        ]);
    }

    /**
     * POST /api/ia/parecer
     *
     * Auxilia na elaboração do texto do parecer médico (HU07).
     * Retorna apenas uma sugestão — a aprovação e edição ficam com o regulador.
     *
     * Requisição:  { "id_solicitacao": 42 }
     * Resposta:    { "sugestao": "...", "tipo": "PARECER" }
     */

#[OA\Post(
    path: "/api/ia/parecer",
    summary: "Auxiliar na elaboração do parecer",
    description: "Gera uma sugestão de texto para auxiliar o Médico Regulador. A aprovação e edição ficam sob responsabilidade humana.",
    tags: ["Inteligência Artificial"],
    security: [["sanctum" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["id_solicitacao"],
            properties: [
                new OA\Property(
                    property: "id_solicitacao",
                    type: "integer",
                    example: 42
                )
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: "Sugestão de parecer gerada com sucesso."),
        new OA\Response(response: 401, description: "Usuário não autenticado."),
        new OA\Response(response: 422, description: "Dados inválidos.")
    ]
)]

    public function parecer(ParecerRequest $request): JsonResponse
    {
        $sugestao = $this->iaService->auxiliarParecer(
            $request->validated('id_solicitacao')
        );

        return response()->json([
            'sugestao' => $sugestao,
            'tipo'     => 'PARECER',
        ]);
    }
}
