import { Injectable } from '@angular/core';

export interface UsuarioLogado {
  id: number;
  nome: string;
  crm: string;
  perfil: 'solicitante' | 'regulador' | 'nir';
}

/**
 * AuthService — centraliza as credenciais do usuário autenticado.
 *
 * Em produção, esses dados viriam de um token JWT ou de uma sessão SSO
 * retornada pelo backend (ex.: GET /api/auth/me).
 * Aqui usamos um usuário fictício para demonstração do módulo.
 */
@Injectable({ providedIn: 'root' })
export class AuthService {
  // Dados que normalmente viriam do payload do JWT / resposta da API de auth
  private usuarioAtual: UsuarioLogado = {
    id: 1,
    nome: 'Dr. Carlos Eduardo Mendes',
    crm: 'PR-54321',
    perfil: 'solicitante'
  };

  /** Retorna o usuário logado. Lança erro se não houver sessão ativa. */
  getUsuarioLogado(): UsuarioLogado {
    return this.usuarioAtual;
  }

  /** Verifica se existe uma sessão ativa. */
  isLogado(): boolean {
    return !!this.usuarioAtual;
  }
}
