<?php

namespace App\Http\Controllers;

use App\Models\Solicitacao;
use App\Http\Requests\StoreSolicitacaoRequest;
use App\Http\Requests\UpdateSolicitacaoRequest;
use Illuminate\Http\JsonResponse;

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
    public function devolver(Solicitacao $solicitacao): JsonResponse
    {
        $solicitacao->update(['status' => 'Pendente']);

        return response()->json(['status' => $solicitacao->status]);
    }
}
