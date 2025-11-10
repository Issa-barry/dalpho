import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Affichage1 } from './affichage1';

describe('Affichage1', () => {
  let component: Affichage1;
  let fixture: ComponentFixture<Affichage1>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Affichage1]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Affichage1);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
