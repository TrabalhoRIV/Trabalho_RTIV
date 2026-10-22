
import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface SolicitacaoApi {
  id: number;
  prontuario: string;
  nome: string;
  clinica: string;
  data?: string;
  status: 'Triado' | 'Pendente';
  prioridade?: 'P1' | 'P2' | 'P3' | null;
}

@Injectable({
  providedIn: 'root'
})
export class SolicitacoesService {
  private http = inject(HttpClient);

  private readonly apiUrl = '/api/solicitacoes';

  listar(): Observable<SolicitacaoApi[]> {
    return this.http.get<SolicitacaoApi[]>(this.apiUrl);
  }
}
