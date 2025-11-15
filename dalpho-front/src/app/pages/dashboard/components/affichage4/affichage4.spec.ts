import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Affichage4 } from './affichage4';

describe('Affichage4', () => {
  let component: Affichage4;
  let fixture: ComponentFixture<Affichage4>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Affichage4]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Affichage4);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
