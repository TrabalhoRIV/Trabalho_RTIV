import { Component, OnInit, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators
} from '@angular/forms';
import { Subject, debounceTime, distinctUntilChanged, switchMap, takeUntil, catchError, of } from 'rxjs';

import { AuthService } from '../auth/auth.service';
import { AghuService } from '../aghu/aghu.service';

@Component({
  selector: 'app-solicitacao',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './solicitacao.component.html',
  styleUrl: './solicitacao.component.css'
})
export class SolicitacaoComponent implements OnInit, OnDestroy {
  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  private aghuService = inject(AghuService);

  // Controle de ciclo de vida dos observables
  private destroy$ = new Subject<void>();

  solicitacaoForm: FormGroup;

  // Estado da busca automática no AGHU
  isSearching = false;
  pacienteNaoEncontrado = false;
  buscaRealizada = false;

  // Estado do assistente de IA
  iaLoading = false;
  iaSuggestion: string | null = null;

  clinicas = [
    'Ginecologia cirúrgica',
    'Cirurgia geral',
    'Endocrinologia adulto',
    'Endocrinologia pediátrica',
    'Gastroenterologia',
    'Pneumologia',
    'Proctologia',
    'Urologia',
    'Fisioterapia',
    'Nutrição',
    'Fonoaudiologia',
    'Terapia ocupacional'
  ];

  constructor() {
    // Recupera as credenciais do usuário autenticado (HU01 — pré-preenchimento do médico)
    const usuario = this.authService.getUsuarioLogado();

    this.solicitacaoForm = this.fb.group({
      // --- Identificação do paciente (preenchidos automaticamente via AGHU) ---
      prontuario:      ['', Validators.required],
      nomePaciente:    [{ value: '', disabled: true }],
      cartaoSus:       [{ value: '', disabled: true }],
      cpf:             [{ value: '', disabled: true }],
      leito:           [{ value: '', disabled: true }],
      numeroConsulta:  [{ value: '', disabled: true }],
      clinicaOrigem:   [{ value: '', disabled: true }],

      // --- Dados da consultoria ---
      clinicaDestino: ['', Validators.required],
      data:           [{ value: new Date().toLocaleDateString('pt-BR'), disabled: true }],

      // --- Credenciais do médico solicitante — preenchidas automaticamente pelo login (HU01) ---
      // Somente leitura: o usuário não pode alterar seu próprio nome/CRM no formulário
      nomeMedico: [{ value: usuario.nome, disabled: true }, Validators.required],
      crm:        [{ value: usuario.crm,  disabled: true }, Validators.required],

      // --- Descrição clínica ---
      descricao: ['', Validators.required]
    });
  }

  ngOnInit(): void {
    /**
     * HU01 — Busca automática no AGHU
     * Assim que o usuário para de digitar o prontuário (400 ms de debounce),
     * o sistema consulta a API do AGHU automaticamente, sem necessidade de botão.
     *
     * Endpoint: GET /api/pacientes/{prontuario}
     */
    this.solicitacaoForm
      .get('prontuario')!
      .valueChanges.pipe(
        debounceTime(400),
        distinctUntilChanged(),
        switchMap(prontuario => {
          // Limpa os campos do paciente ao iniciar nova busca
          this.limparDadosPaciente();

          if (!prontuario || prontuario.trim().length < 3) {
            return of(null);
          }

          this.isSearching = true;
          this.buscaRealizada = false;

          return this.aghuService.buscarPaciente(prontuario).pipe(
            catchError(() => {
              // Caso de Borda (HU12 / EC3): paciente não encontrado → fallback manual
              this.isSearching = false;
              this.buscaRealizada = true;
              this.pacienteNaoEncontrado = true;
              this.habilitarPreenchimentoManual();
              return of(null);
            })
          );
        }),
        takeUntil(this.destroy$)
      )
      .subscribe(dados => {
        this.isSearching = false;
        this.buscaRealizada = true;

        if (!dados) return;

        // Preenche automaticamente os campos do paciente (HU01)
        this.pacienteNaoEncontrado = false;
        this.solicitacaoForm.patchValue({
          nomePaciente:   dados.nomePaciente,
          cartaoSus:      dados.cartaoSus,
          cpf:            dados.cpf,
          leito:          dados.leito,
          numeroConsulta: dados.numeroConsulta,
          clinicaOrigem:  dados.clinicaOrigem
        });
      });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  // -------------------------------------------------------------------------
  // Métodos auxiliares
  // -------------------------------------------------------------------------

  /** Limpa os campos preenchidos automaticamente pelo AGHU */
  private limparDadosPaciente(): void {
    this.pacienteNaoEncontrado = false;
    this.buscaRealizada = false;
    this.solicitacaoForm.patchValue({
      nomePaciente:   '',
      cartaoSus:      '',
      cpf:            '',
      leito:          '',
      numeroConsulta: '',
      clinicaOrigem:  ''
    });
    // Garante que os campos estejam desabilitados novamente
    ['nomePaciente', 'cartaoSus', 'cpf', 'leito', 'numeroConsulta', 'clinicaOrigem'].forEach(
      campo => this.solicitacaoForm.get(campo)?.disable()
    );
  }

  /**
   * Fallback manual — HU01 (Caso de Borda EC3 / HU12):
   * Paciente sem prontuário no AGHU ou AGHU indisponível.
   * Libera preenchimento manual com validação de CPF/SUS.
   */
  habilitarPreenchimentoManual(): void {
    ['nomePaciente', 'cartaoSus', 'cpf', 'leito', 'numeroConsulta', 'clinicaOrigem'].forEach(
      campo => this.solicitacaoForm.get(campo)?.enable()
    );
  }

  // -------------------------------------------------------------------------
  // HU03 — Assistência da IA na descrição clínica
  // -------------------------------------------------------------------------

  /**
   * Envia a descrição ao endpoint POST /api/ia/descricao e exibe sugestão.
   * A IA apenas sugere — a decisão final é sempre do médico (EC7 / EC11).
   */
  gerarSugestaoIA(): void {
    const descricaoAtual = this.solicitacaoForm.get('descricao')?.value;
    if (!descricaoAtual) {
      alert('Por favor, inicie a descrição antes de pedir ajuda à IA.');
      return;
    }

    this.iaLoading = true;

    // Simulação do POST /api/ia/descricao
    setTimeout(() => {
      this.iaSuggestion =
        '⚠️ Sugestão da IA (requer revisão humana): Complementar com tempo de evolução do quadro, comorbidades relevantes (ex.: HAS, DM e tipo de insulina utilizada) e exames de imagem ou laboratoriais recentes que embasem a solicitação.';
      this.iaLoading = false;
    }, 1500);
  }

  /** Aceita a sugestão da IA, anexando-a à descrição existente (HU03) */
  aceitarSugestaoIA(): void {
    const descricaoAtual = this.solicitacaoForm.get('descricao')?.value;
    this.solicitacaoForm.patchValue({
      descricao: descricaoAtual + '\n\n' + this.iaSuggestion
    });
    this.iaSuggestion = null;
  }

  /** Rejeita a sugestão da IA sem alterar a descrição (HU03) */
  rejeitarSugestaoIA(): void {
    this.iaSuggestion = null;
  }

  // -------------------------------------------------------------------------
  // Ações do formulário
  // -------------------------------------------------------------------------

  /** Limpa o formulário mantendo as credenciais do médico logado */
  limparFormulario(): void {
    const usuario = this.authService.getUsuarioLogado();
    this.solicitacaoForm.reset();
    this.solicitacaoForm.patchValue({
      data:       new Date().toLocaleDateString('pt-BR'),
      nomeMedico: usuario.nome,
      crm:        usuario.crm
    });
    this.iaSuggestion = null;
    this.pacienteNaoEncontrado = false;
    this.buscaRealizada = false;
    // Redisabilita campos do paciente
    this.limparDadosPaciente();
  }

  /**
   * HU02 — Envio da solicitação
   * Registra via POST /api/solicitacoes.
   * Inclui auditoria de uso da IA (HU10) via payload.
   */
  enviarSolicitacao(): void {
    if (this.solicitacaoForm.invalid) {
      this.solicitacaoForm.markAllAsTouched();
      return;
    }

    const payload = this.solicitacaoForm.getRawValue();
    // Em produção: this.http.post('/api/solicitacoes', payload).subscribe(...)
    console.log('[POST /api/solicitacoes] Payload:', payload);
    alert('Solicitação enviada com sucesso para a fila de regulação!');
    this.limparFormulario();
  }

  /** Abre janela de impressão para o formulário */
  imprimirSolicitacao(): void {
    window.print();
  }
}
