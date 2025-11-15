import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Affichage3 } from './affichage3';

describe('Affichage3', () => {
  let component: Affichage3;
  let fixture: ComponentFixture<Affichage3>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Affichage3]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Affichage3);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
