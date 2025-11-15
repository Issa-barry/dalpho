import { TestBed } from '@angular/core/testing';

import { EchangeRate } from './echange-rate';

describe('EchangeRate', () => {
  let service: EchangeRate;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(EchangeRate);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
