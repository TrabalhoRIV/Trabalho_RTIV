<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Http\Requests\StoreAgendamentoRequest;
use Illuminate\Http\JsonResponse;

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
