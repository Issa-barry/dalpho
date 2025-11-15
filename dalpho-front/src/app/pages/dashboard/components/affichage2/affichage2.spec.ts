import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Affichage2 } from './affichage2';

describe('Affichage2', () => {
  let component: Affichage2;
  let fixture: ComponentFixture<Affichage2>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Affichage2]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Affichage2);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
