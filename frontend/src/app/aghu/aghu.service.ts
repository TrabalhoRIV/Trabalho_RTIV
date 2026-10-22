
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

@Injectable({ providedIn: 'root' })
export class AghuService {
  private readonly baseUrl = '/api';

  constructor(private http: HttpClient) {}

  buscarPaciente(prontuario: string): Observable<DadosPaciente> {
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
      },
      '345678': {
        prontuario: '345678',
        nomePaciente: 'Ana Costa',
        cartaoSus: '898 0000 1234 5678',
        cpf: '111.222.333-44',
        leito: 'Leito 205-B',
        numeroConsulta: '345678',
        clinicaOrigem: 'Ortopedia'
      }
    };

    return of(null).pipe(
      delay(600),
      switchMap(() => {
        const paciente = mockDb[prontuario.trim()];

        if (paciente) {
          return of(paciente);
        }

        return throwError(
          () => new Error('Paciente não encontrado no AGHU.')
        );
      })
    );
  }
}
