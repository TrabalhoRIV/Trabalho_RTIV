import { Routes } from '@angular/router';
import { SolicitacaoComponent } from './solicitacao/solicitacao.component';

export const routes: Routes = [
  { path: '', redirectTo: 'solicitacao', pathMatch: 'full' },
  { path: 'solicitacao', component: SolicitacaoComponent }
];
