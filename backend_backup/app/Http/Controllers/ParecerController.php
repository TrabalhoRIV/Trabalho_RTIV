<?php

namespace App\Http\Controllers;

use App\Models\Parecer;
use App\Models\Solicitacao;
use App\Http\Requests\StoreParecerRequest;
use Illuminate\Http\JsonResponse;

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
