// src/app/services/exchange-rate.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map, Observable } from 'rxjs';
import { environment } from 'src/environements/environment.dev';
import { ExchangeRate } from '@/pages/models/ExchangeRate';

export type CCY = 'GNF' | 'EUR' | 'USD' | 'XOF' | 'GBP' | 'CHF' | 'CAD';

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
}

export interface ExchangeRateDto {
  from: CCY;
  to: CCY;
  rate: number;
  open?: number | null;
  high?: number | null;
  low?: number | null;
  updated_at?: string;
}

// payload pour la mise à jour (adaptable à ton UpdateExchangeRateRequest)
export interface ExchangeRateUpdatePayload {
  rate?: number;
  day_high?: number | null;
  day_low?: number | null;
  is_current?: boolean;
  effective_date?: string; // si besoin
}

@Injectable({
  providedIn: 'root',
})
export class ExchangeRateService {
  private readonly baseUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  /** GET /exchange-rates (tous les taux) */
  getCurrentRates(): Observable<ApiResponse<ExchangeRateDto[]>> {
    return this.http.get<ApiResponse<ExchangeRateDto[]>>(
      `${this.baseUrl}/exchange-rates`
    );
  }

  getCurrentRatesAll() {
    return this.http
      .get<{ data: any[] }>(`${this.baseUrl}/exchange-rates?current=1`)
      .pipe(map((res) => res.data.map((item) => new ExchangeRate(item))));
  }

  /** GET /public/exchange-rates/current/{from}/{to} */
  getCurrentPair(from: CCY, to: CCY): Observable<ApiResponse<ExchangeRateDto>> {
    return this.http.get<ApiResponse<ExchangeRateDto>>(
      `${this.baseUrl}/public/exchange-rates/current/${from}/${to}`
    );
  }

  /** PUT /exchange-rates/{id} */
updateRate(id: number, rate: number) {
  return this.http.put<any>(`${this.baseUrl}/exchange-rates/${id}`, {
    rate: rate
  });
}


  /** DELETE /exchange-rates/{id} */
  deleteRate(id: number): Observable<void> {
    return this.http
      .delete<ApiResponse<null>>(`${this.baseUrl}/exchange-rates/${id}`)
      .pipe(map(() => void 0));
  }
}
