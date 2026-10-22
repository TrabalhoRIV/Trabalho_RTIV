
import { Routes } from '@angular/router';
import { SolicitacaoComponent } from './solicitacao/solicitacao.component';
import { AvaliacaoParecerComponent } from './avaliacao-parecer/avaliacao-parecer.component';

export const routes: Routes = [
  { path: '', redirectTo: 'solicitacao', pathMatch: 'full' },
  { path: 'solicitacao', component: SolicitacaoComponent },
  { path: 'avaliacao-parecer', component: AvaliacaoParecerComponent }
];
