import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-solicitacao',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './solicitacao.component.html',
  styleUrl: './solicitacao.component.css'
})
export class SolicitacaoComponent {
  private fb = inject(FormBuilder);
  private http = inject(HttpClient);

  solicitacaoForm: FormGroup;
  isSearching = false;
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

  // Inicializa o formulário reativo com suas validações e estados (readonly)
  constructor() {
    this.solicitacaoForm = this.fb.group({
      prontuario: ['', Validators.required],
      nomePaciente: ['', Validators.required],
      cartaoSus: [{ value: '', disabled: true }],
      cpf: [{ value: '', disabled: true }],
      leito: [{ value: '', disabled: true }],
      numeroConsulta: [{ value: '', disabled: true }],
      clinicaOrigem: [{ value: '', disabled: true }],
      clinicaDestino: ['', Validators.required],
      data: [{ value: new Date().toLocaleDateString('pt-BR'), disabled: true }],
      nomeMedico: ['', Validators.required],
      crm: ['', Validators.required],
      descricao: ['', Validators.required]
    });
  }

  // Método responsável por buscar os dados do paciente via integração com AGHU
  buscarPaciente() {
    const prontuario = this.solicitacaoForm.get('prontuario')?.value;
    const nome = this.solicitacaoForm.get('nomePaciente')?.value;

    if (!prontuario && !nome) {
      alert('Por favor, informe o Prontuário ou o Nome do paciente para realizar a busca.');
      return;
    }

    this.isSearching = true;

    // Simulando chamada à API no endpoint do backend do AGHU
    // `GET /api/pacientes/${prontuario}`
    setTimeout(() => {
      // Se o prontuário for diferente de '123456', simulamos que o paciente não existe no AGHU
      if (prontuario !== '123456') {
        alert('Paciente não encontrado no AGHU. O preenchimento manual foi liberado para fins de contingência.');
        this.habilitarPreenchimentoManual();
        this.isSearching = false;
        return;
      }

      this.solicitacaoForm.patchValue({
        nomePaciente: nome || 'Paciente Fictício Silva',
        prontuario: '123456',
        cartaoSus: '7000.1234.5678.9012',
        cpf: '123.456.789-00',
        leito: 'Leito 402',
        numeroConsulta: '987654',
        clinicaOrigem: 'Clínica Médica Interna'
      });
      this.isSearching = false;
    }, 1000);
  }

  // Fallback: HU01 - Caso de Borda onde o paciente não é achado no AGHU
  habilitarPreenchimentoManual() {
    this.solicitacaoForm.get('cartaoSus')?.enable();
    this.solicitacaoForm.get('cpf')?.enable();
    this.solicitacaoForm.get('leito')?.enable();
    this.solicitacaoForm.get('numeroConsulta')?.enable();
    this.solicitacaoForm.get('clinicaOrigem')?.enable();
  }

  // HU03 - Assistência da IA na descrição clínica
  gerarSugestaoIA() {
    const descricaoAtual = this.solicitacaoForm.get('descricao')?.value;
    if (!descricaoAtual) {
      alert('Por favor, inicie a descrição antes de pedir ajuda à IA.');
      return;
    }

    this.iaLoading = true;
    
    // Simula `POST /api/ia/descricao`
    setTimeout(() => {
      this.iaSuggestion = "Sugestão da IA: Adicionar tempo de evolução e quais exames de imagem recentes foram realizados para melhor avaliação.";
      this.iaLoading = false;
    }, 1500);
  }

  aceitarSugestaoIA() {
    const descricaoAtual = this.solicitacaoForm.get('descricao')?.value;
    this.solicitacaoForm.patchValue({
      descricao: descricaoAtual + '\n\n' + this.iaSuggestion
    });
    this.iaSuggestion = null;
  }

  rejeitarSugestaoIA() {
    this.iaSuggestion = null;
  }

  // Método para redefinir o formulário e limpar os dados preenchidos
  limparFormulario() {
    this.solicitacaoForm.reset();
    this.solicitacaoForm.patchValue({
      data: new Date().toLocaleDateString('pt-BR')
    });
    this.iaSuggestion = null;
  }

  // Método para submeter a solicitação de consultoria
  enviarSolicitacao() {
    if (this.solicitacaoForm.invalid) {
      this.solicitacaoForm.markAllAsTouched();
      return;
    }

    const payload = this.solicitacaoForm.getRawValue();
    // Simula a requisição POST para o backend
    console.log('Enviando solicitação...', payload);
    alert('Solicitação enviada com sucesso para a fila de regulação!');
    this.limparFormulario();
  }

  // Abre a janela de impressão do navegador
  imprimirSolicitacao() {
    window.print();
  }
}
