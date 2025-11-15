// src/app/services/exchange-rate.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from 'src/environements/environment.dev';
 
export type CCY = 'GNF' | 'EUR' | 'USD' | 'XOF' | 'GBP' | 'CHF' | 'CAD';

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
}

/**
 * Adapte l'interface suivant ce que renvoie ton API /exchange-rates/current
 * Ici j'imagine un truc du genre :
 *  - from : code devise de base (ex: "EUR")
 *  - to   : code devise cote (ex: "GNF")
 *  - rate : mid
 */
export interface ExchangeRateDto {
  from: CCY;               // ex: "EUR"
  to: CCY;                 // ex: "GNF"
  rate: number;            // mid
  open?: number | null;
  high?: number | null;
  low?: number | null;
  updated_at?: string;
}

@Injectable({
  providedIn: 'root',
})
export class ExchangeRateService {
  private readonly baseUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  /** GET /api/public/exchange-rates/current (tous les taux actuels) */
  getCurrentRates(): Observable<ApiResponse<ExchangeRateDto[]>> {
    return this.http.get<ApiResponse<ExchangeRateDto[]>>(
      `${this.baseUrl}/exchange-rates`
    );
  }

  /** GET /api/public/exchange-rates/current/{from}/{to} (un seul cross) */
  getCurrentPair(from: CCY, to: CCY): Observable<ApiResponse<ExchangeRateDto>> {
    return this.http.get<ApiResponse<ExchangeRateDto>>(
      `${this.baseUrl}/public/exchange-rates/current/${from}/${to}`
    );
  }
}
