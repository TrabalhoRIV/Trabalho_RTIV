
import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

interface Solicitacao {
  prontuario: string;
  nome: string;
  clinica: string;
  data: string;
  status: 'Triado' | 'Pendente';
  prioridade: '' | 'P1' | 'P2' | 'P3';
}

@Component({
  selector: 'app-avaliacao-parecer',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './avaliacao-parecer.component.html',
  styleUrl: './avaliacao-parecer.component.css'
})
export class AvaliacaoParecerComponent {
  private readonly chaveArmazenamento =
    'avaliacao-parecer-solicitacoes';

  clinicas = [
    'Cardiologia',
    'Neurologia',
    'Ortopedia',
    'Clínica Médica'
  ];

  private solicitacoesIniciais: Solicitacao[] = [
    {
      prontuario: '123456',
      nome: 'Ana Beatriz Ferreira',
      clinica: 'Cardiologia',
      data: '21/10/2026',
      status: 'Pendente',
      prioridade: ''
    },
    {
      prontuario: '654321',
      nome: 'João Pedro Oliveira',
      clinica: 'Neurologia',
      data: '21/10/2026',
      status: 'Pendente',
      prioridade: ''
    },
    {
      prontuario: '345678',
      nome: 'Ana Costa',
      clinica: 'Ortopedia',
      data: '20/10/2026',
      status: 'Triado',
      prioridade: 'P2'
    }
  ];

  solicitacoes: Solicitacao[] = [];

  constructor() {
    this.solicitacoes = this.carregarSolicitacoes();

    // Atualiza os dois cadastros de teste existentes,
    // preservando as demais informações de cada solicitação.
    let alterado = false;

    this.solicitacoes.forEach(s => {
      if (s.prontuario === '123456' &&
          s.nome !== 'Ana Beatriz Ferreira') {
        s.nome = 'Ana Beatriz Ferreira';
        alterado = true;
      }

      if (s.prontuario === '234567') {
        s.prontuario = '654321';
        s.nome = 'João Pedro Oliveira';
        alterado = true;
      }
    });

    if (alterado) {
      this.salvarNoNavegador();
    }
  }

  private carregarSolicitacoes(): Solicitacao[] {
    try {
      const dadosSalvos = localStorage.getItem(
        this.chaveArmazenamento
      );

      if (dadosSalvos) {
        const dados: unknown = JSON.parse(dadosSalvos);

        if (Array.isArray(dados)) {
          return dados as Solicitacao[];
        }
      }
    } catch (erro) {
      console.error('Erro ao carregar avaliações:', erro);
    }

    return this.solicitacoesIniciais.map(s => ({ ...s }));
  }

  private salvarNoNavegador(): boolean {
    try {
      const dados = JSON.stringify(this.solicitacoes);

      localStorage.setItem(
        this.chaveArmazenamento,
        dados
      );

      return localStorage.getItem(
        this.chaveArmazenamento
      ) === dados;
    } catch (erro) {
      console.error('Erro ao salvar avaliações:', erro);
      return false;
    }
  }

  filtroNome = '';
  filtroProntuario = '';
  filtroClinica = '';
  filtroStatus = '';

  solicitacaoSelecionada: Solicitacao | null = null;
  statusEdicao: 'Triado' | 'Pendente' = 'Pendente';
  prioridadeEdicao: '' | 'P1' | 'P2' | 'P3' = '';

  get solicitacoesFiltradas(): Solicitacao[] {
    return this.solicitacoes.filter(s => {
      return s.nome
        .toLowerCase()
        .includes(this.filtroNome.toLowerCase())
        && s.prontuario.includes(this.filtroProntuario)
        && (!this.filtroClinica ||
          s.clinica === this.filtroClinica)
        && (!this.filtroStatus ||
          s.status === this.filtroStatus);
    });
  }

  abrirAvaliacao(s: Solicitacao): void {
    this.solicitacaoSelecionada = s;
    this.statusEdicao = s.status;
    this.prioridadeEdicao = s.prioridade;
  }

  fecharAvaliacao(): void {
    this.solicitacaoSelecionada = null;
  }

  salvarAvaliacao(): void {
    if (!this.solicitacaoSelecionada) {
      return;
    }

    if (!this.prioridadeEdicao) {
      alert('Selecione uma prioridade antes de salvar.');
      return;
    }

    this.solicitacaoSelecionada.status = this.statusEdicao;
    this.solicitacaoSelecionada.prioridade =
      this.prioridadeEdicao;

    const salvou = this.salvarNoNavegador();

    if (!salvou) {
      alert(
        'Não foi possível confirmar o salvamento no navegador.'
      );
      return;
    }

    this.fecharAvaliacao();

    alert('Avaliação salva no navegador com sucesso!');
  }
}
