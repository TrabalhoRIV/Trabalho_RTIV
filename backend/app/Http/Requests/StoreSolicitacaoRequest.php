<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreSolicitacaoRequest
 *
 * Valida os dados recebidos no POST /api/solicitacoes (HU02).
 *
 * Campos obrigatórios conforme documentação (seção 2.1.1 — HU02):
 *   - id_paciente       → paciente identificado via AGHU
 *   - especialidade     → clínica destino selecionada no formulário
 *   - descricao_clinica → descrição da situação clínica (obrigatória, HU02 critério 2)
 *
 * O id_solicitante NÃO é recebido do formulário — é extraído do token JWT
 * autenticado no controller (auth()->id()), impedindo falsificação de identidade.
 */
class StoreSolicitacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Apenas usuários autenticados com perfil "solicitante" podem criar solicitações
        return auth()->check() && auth()->user()->perfil === 'solicitante';
    }

    public function rules(): array
    {
        return [
            'id_paciente'       => ['required', 'integer', 'exists:pacientes,id_paciente'],
            'especialidade'     => ['required', 'string', 'max:100'],
            'descricao_clinica' => ['required', 'string', 'min:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_paciente.required'       => 'O paciente é obrigatório.',
            'id_paciente.exists'         => 'Paciente não encontrado no sistema.',
            'especialidade.required'     => 'Selecione a clínica destino.',
            'descricao_clinica.required' => 'A descrição clínica é obrigatória.',
            'descricao_clinica.min'      => 'A descrição clínica deve ter pelo menos 20 caracteres.',
        ];
    }
}
