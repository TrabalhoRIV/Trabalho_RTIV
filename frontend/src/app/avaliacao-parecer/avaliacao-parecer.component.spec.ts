import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AvaliacaoParecerComponent } from './avaliacao-parecer.component';

describe('AvaliacaoParecerComponent', () => {
  let component: AvaliacaoParecerComponent;
  let fixture: ComponentFixture<AvaliacaoParecerComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AvaliacaoParecerComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AvaliacaoParecerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
