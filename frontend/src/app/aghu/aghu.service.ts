import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of, throwError } from 'rxjs';
import { delay, switchMap } from 'rxjs/operators';

export interface DadosPaciente {
  prontuario: string;
  nomePaciente: string;
  cartaoSus: string;
  cpf: string;
  leito: string;
  numeroConsulta: string;
  clinicaOrigem: string;
}

/**
 * AghuService — integração com a API do AGHU.
 *
 * Endpoint real documentado:
 *   GET /api/pacientes/{prontuario}
 *   Resposta: { cpf, cartaoSus, leito, nomePaciente, numeroConsulta, clinicaOrigem }
 *
 * A simulação abaixo imita esse comportamento até o backend estar disponível.
 */
@Injectable({ providedIn: 'root' })
export class AghuService {
  private readonly baseUrl = '/api'; // prefixo do backend Laravel

  constructor(private http: HttpClient) {}

  /**
   * Busca os dados do paciente no AGHU pelo número do prontuário.
   * Retorna um Observable com os dados ou lança erro se não encontrado.
   *
   * Em produção: return this.http.get<DadosPaciente>(`${this.baseUrl}/pacientes/${prontuario}`);
   */
  buscarPaciente(prontuario: string): Observable<DadosPaciente> {
    // --- Simulação da API (substituir pela chamada HTTP real) ---
    const mockDb: Record<string, DadosPaciente> = {
      '123456': {
        prontuario: '123456',
        nomePaciente: 'Ana Beatriz Ferreira',
        cartaoSus: '7000.1234.5678.9012',
        cpf: '123.456.789-00',
        leito: 'Leito 402-A',
        numeroConsulta: '987654',
        clinicaOrigem: 'Clínica Médica Interna'
      },
      '654321': {
        prontuario: '654321',
        nomePaciente: 'João Pedro Oliveira',
        cartaoSus: '8001.4321.8765.1098',
        cpf: '987.654.321-00',
        leito: 'Leito 110-B',
        numeroConsulta: '112233',
        clinicaOrigem: 'Pronto Atendimento'
      }
    };

    return of(null).pipe(
      delay(600), // simula latência de rede
      switchMap(() => {
        const paciente = mockDb[prontuario.trim()];
        if (paciente) {
          return of(paciente);
        }
        return throwError(() => new Error('Paciente não encontrado no AGHU.'));
      })
    );
    // --- Fim da simulação ---
  }
}
