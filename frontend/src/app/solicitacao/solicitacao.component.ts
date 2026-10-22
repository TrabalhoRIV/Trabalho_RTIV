
import { Component, OnInit, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators
} from '@angular/forms';
import {
  Subject,
  debounceTime,
  distinctUntilChanged,
  switchMap,
  takeUntil,
  catchError,
  of
} from 'rxjs';

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
  private destroy$ = new Subject<void>();

  solicitacaoForm: FormGroup;

  isSearching = false;
  pacienteNaoEncontrado = false;
  buscaRealizada = false;

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
    const usuario = this.authService.getUsuarioLogado();

    this.solicitacaoForm = this.fb.group({
      prontuario: ['', Validators.required],
      nomePaciente: [''],
      cartaoSus: [''],
      cpf: [''],
      leito: [''],
      numeroConsulta: [''],
      clinicaOrigem: [''],
      clinicaDestino: ['', Validators.required],
      data: [{ value: new Date().toLocaleDateString('pt-BR'), disabled: true }],
      nomeMedico: [{ value: usuario.nome, disabled: true }, Validators.required],
      crm: [{ value: usuario.crm, disabled: true }, Validators.required],
      descricao: ['', Validators.required]
    });
  }

  ngOnInit(): void {
    this.solicitacaoForm.get('prontuario')!.valueChanges
      .pipe(
        debounceTime(400),
        distinctUntilChanged(),
        switchMap(valor => {
          const prontuario = String(valor ?? '').trim();

          this.limparDadosPaciente();

          if (prontuario.length < 3) {
            this.isSearching = false;
            return of(null);
          }

          this.isSearching = true;
          this.buscaRealizada = false;

          return this.aghuService.buscarPaciente(prontuario).pipe(
            catchError(() => {
              this.isSearching = false;
              this.buscaRealizada = true;
              this.pacienteNaoEncontrado = true;
              return of(null);
            })
          );
        }),
        takeUntil(this.destroy$)
      )
      .subscribe(dados => {
        this.isSearching = false;

        if (!dados) {
          return;
        }

        this.buscaRealizada = true;
        this.pacienteNaoEncontrado = false;

        this.solicitacaoForm.patchValue({
          nomePaciente: dados.nomePaciente ?? '',
          cartaoSus: dados.cartaoSus ?? '',
          cpf: dados.cpf ?? '',
          leito: dados.leito ?? '',
          numeroConsulta: dados.numeroConsulta ?? '',
          clinicaOrigem: dados.clinicaOrigem ?? ''
        });
      });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  private limparDadosPaciente(): void {
    this.pacienteNaoEncontrado = false;
    this.buscaRealizada = false;

    this.solicitacaoForm.patchValue({
      nomePaciente: '',
      cartaoSus: '',
      cpf: '',
      leito: '',
      numeroConsulta: '',
      clinicaOrigem: ''
    }, { emitEvent: false });
  }

  habilitarPreenchimentoManual(): void {
    // Os campos de identificação permanecem editáveis.
  }

  gerarSugestaoIA(): void {
    const descricaoAtual = this.solicitacaoForm.get('descricao')?.value;

    if (!descricaoAtual?.trim()) {
      alert('Por favor, inicie a descrição antes de pedir ajuda à IA.');
      return;
    }

    this.iaLoading = true;

    setTimeout(() => {
      this.iaSuggestion =
        'Sugestão para revisão: complemente com o tempo de evolução do quadro, comorbidades relevantes e exames recentes que fundamentem a solicitação.';
      this.iaLoading = false;
    }, 1500);
  }

  aceitarSugestaoIA(): void {
    const descricaoAtual =
      this.solicitacaoForm.get('descricao')?.value ?? '';

    this.solicitacaoForm.patchValue({
      descricao: descricaoAtual
        ? `${descricaoAtual}\n\n${this.iaSuggestion ?? ''}`
        : this.iaSuggestion ?? ''
    });

    this.iaSuggestion = null;
  }

  rejeitarSugestaoIA(): void {
    this.iaSuggestion = null;
  }

  limparFormulario(): void {
    const usuario = this.authService.getUsuarioLogado();

    this.solicitacaoForm.reset({
      prontuario: '',
      nomePaciente: '',
      cartaoSus: '',
      cpf: '',
      leito: '',
      numeroConsulta: '',
      clinicaOrigem: '',
      clinicaDestino: '',
      data: new Date().toLocaleDateString('pt-BR'),
      nomeMedico: usuario.nome,
      crm: usuario.crm,
      descricao: ''
    });

    this.iaSuggestion = null;
    this.iaLoading = false;
    this.isSearching = false;
    this.pacienteNaoEncontrado = false;
    this.buscaRealizada = false;
  }

  enviarSolicitacao(): void {
    if (this.solicitacaoForm.invalid) {
      this.solicitacaoForm.markAllAsTouched();
      return;
    }

    const payload = this.solicitacaoForm.getRawValue();

    console.log('Dados da solicitação:', payload);
    alert('Formulário validado. O envio à API ainda precisa ser integrado.');

    this.limparFormulario();
  }

  imprimirSolicitacao(): void {
    window.print();
  }
}
