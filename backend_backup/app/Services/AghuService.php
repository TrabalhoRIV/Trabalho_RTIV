<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AghuService
 *
 * Responsável pela integração com a API externa do AGHU (sistema hospitalar).
 * Consome o endpoint de pacientes e retorna os dados para o PacienteController.
 *
 * Endpoint AGHU consumido:
 *   GET {AGHU_BASE_URL}/pacientes/{prontuario}
 *   Header: Authorization: Bearer {AGHU_TOKEN}
 *
 * Casos de Borda tratados:
 *   EC3 — Paciente sem prontuário no AGHU → retorna null (fallback manual)
 *   EC6 — AGHU indisponível              → retorna null (fallback manual + log de falha)
 */
class AghuService
{
    private string $baseUrl;
    private string $token;
    private int    $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.aghu.base_url', env('AGHU_BASE_URL'));
        $this->token   = config('services.aghu.token',    env('AGHU_TOKEN'));
        $this->timeout = (int) config('services.aghu.timeout', env('AGHU_TIMEOUT', 5));
    }

    /**
     * Busca os dados do paciente pelo número de prontuário.
     *
     * @param  string $prontuario  Número do prontuário do paciente
     * @return array|null          Dados do paciente ou null se não encontrado / AGHU indisponível
     */
    public function buscarPaciente(string $prontuario): ?array
    {
        try {
            $response = Http::withToken($this->token)
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/pacientes/{$prontuario}");

            // Paciente não encontrado no AGHU (EC3)
            if ($response->status() === 404) {
                Log::info('[AghuService] Paciente não encontrado no AGHU.', [
                    'prontuario' => $prontuario,
                ]);
                return null;
            }

            // Outro erro HTTP
            if ($response->failed()) {
                Log::warning('[AghuService] Erro ao consultar AGHU.', [
                    'prontuario' => $prontuario,
                    'status'     => $response->status(),
                ]);
                return null;
            }

            $dados = $response->json();

            // Mapeia os campos da resposta do AGHU para o contrato da nossa API
            return [
                'prontuario'     => $dados['nr_prontuario']      ?? $prontuario,
                'nomePaciente'   => $dados['nm_paciente']         ?? null,
                'cartaoSus'      => $dados['nr_cartao_sus']       ?? null,
                'cpf'            => $dados['nr_cpf']              ?? null,
                'leito'          => $dados['ds_leito']            ?? null,
                'numeroConsulta' => $dados['nr_consulta']         ?? null,
                'clinicaOrigem'  => $dados['ds_clinica_origem']   ?? null,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // AGHU fora do ar ou timeout (EC6)
            Log::error('[AghuService] AGHU indisponível — timeout ou conexão recusada.', [
                'prontuario' => $prontuario,
                'erro'       => $e->getMessage(),
            ]);
            return null;

        } catch (\Throwable $e) {
            // Qualquer outro erro inesperado
            Log::error('[AghuService] Erro inesperado ao consultar AGHU.', [
                'prontuario' => $prontuario,
                'erro'       => $e->getMessage(),
            ]);
            return null;
        }
    }
}
