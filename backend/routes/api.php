<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PacienteController;
use App\Http\Controllers\SolicitacaoController;
use App\Http\Controllers\ParecerController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\IA\IAController;

/*
|--------------------------------------------------------------------------
| API Routes — Integra / NIR
| Módulo de Consultoria Ambulatorial — TakeOff RTIV
|--------------------------------------------------------------------------
|
| Todas as rotas abaixo são protegidas pelo middleware 'auth:sanctum',
| garantindo que apenas usuários autenticados possam acessar o módulo.
| (HU01 — "ele só poderá acessar esse módulo se estiver logado")
|
*/

// ─── Autenticação ────────────────────────────────────────────────────────────
// Rota pública: login (não exige token)
Route::post('/auth/login',  [\App\Http\Controllers\Auth\AuthController::class, 'login']);

// Rotas protegidas por token JWT (Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Retorna os dados do usuário logado (nome, CRM, perfil)
    // Usado pelo AuthService do Angular para pré-preencher nome e CRM (HU01)
    Route::get('/auth/me', function (Request $request) {
        return response()->json($request->user());
    });

    Route::post('/auth/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout']);

    // ─── ÉPICO 1: Solicitação de Consultoria ─────────────────────────────────

    /**
     * GET /api/pacientes/{prontuario}
     * Busca os dados do paciente no AGHU pelo número de prontuário.
     * Resposta: { prontuario, nomePaciente, cartaoSus, cpf, leito, numeroConsulta, clinicaOrigem }
     * Equipe 1 — HU01
     */
    Route::get('/pacientes/{prontuario}', [PacienteController::class, 'show']);

    /**
     * GET    /api/solicitacoes        → lista para triagem (HU04 — Equipe 2)
     * GET    /api/solicitacoes/{id}   → detalhe de uma solicitação
     * POST   /api/solicitacoes        → cria nova solicitação (HU02 — Equipe 1)
     * PUT    /api/solicitacoes/{id}   → atualiza solicitação
     */
    Route::apiResource('solicitacoes', SolicitacaoController::class);

    /**
     * POST /api/solicitacoes/{id}/devolver
     * Devolve a solicitação para complementação de informações (HU09).
     * Altera o status para "Pendente" e registra o motivo no histórico.
     */
    Route::post('/solicitacoes/{solicitacao}/devolver', [SolicitacaoController::class, 'devolver']);

    // ─── ÉPICO 2: Triagem e Regulação ────────────────────────────────────────

    /**
     * POST /api/pareceres
     * Registra o parecer emitido pelo Médico Regulador (HU07 — Equipe 2).
     * Requisição: { id_solicitacao, texto_parecer }
     */
    Route::post('/pareceres', [ParecerController::class, 'store']);

    // ─── ÉPICO 3: Conclusão / NIR ─────────────────────────────────────────────

    /**
     * POST /api/agendamentos
     * Realiza o agendamento da consulta pelo NIR (HU08 / HU09).
     * Requisição: { id_solicitacao, data_agendamento, local }
     */
    Route::post('/agendamentos', [AgendamentoController::class, 'store']);

    // ─── ÉPICO 1 / 2: Assistência de IA ─────────────────────────────────────

    /**
     * POST /api/ia/descricao  → auxilia na descrição clínica (HU03)
     * POST /api/ia/resumo     → resume o caso para o regulador (HU05)
     * POST /api/ia/prioridade → sugere P1, P2 ou P3 (HU06)
     * POST /api/ia/parecer    → auxilia na redação do parecer (HU07)
     *
     * POLÍTICA: todos os endpoints retornam apenas sugestões.
     * A decisão final é sempre do profissional responsável (seção 2.4).
     */
    Route::prefix('ia')->group(function () {
        Route::post('/descricao',  [IAController::class, 'descricao']);
        Route::post('/resumo',     [IAController::class, 'resumo']);
        Route::post('/prioridade', [IAController::class, 'prioridade']);
        Route::post('/parecer',    [IAController::class, 'parecer']);
    });
});
