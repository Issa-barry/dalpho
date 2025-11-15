import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from 'src/environements/environment.dev';
// adapte le chemin selon ton projet
 
export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
}

export interface Currency {
  id: number;
  code: string;             // ex: "EUR"
  name: string;             // ex: "Euro"
  symbol: string | null;    // ex: "€"
  is_base_currency: boolean;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

@Injectable({
  providedIn: 'root',
})
export class CurrencyService {
  /**
   * Exemple d’URL :
   * environment.apiUrl = 'http://127.0.0.1:8000/api'
   * -> this.baseUrl = 'http://127.0.0.1:8000/api'
   */
  private readonly baseUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  // -----------------------------
  // ROUTES PROTÉGÉES /currencies
  // -----------------------------

  /** GET /api/currencies */
  getAll(): Observable<ApiResponse<Currency[]>> {
    return this.http.get<ApiResponse<Currency[]>>(
      `${this.baseUrl}/currencies`
    );
  }

  /** GET /api/currencies/{id} */
  getById(id: number): Observable<ApiResponse<Currency>> {
    return this.http.get<ApiResponse<Currency>>(
      `${this.baseUrl}/currencies/${id}`
    );
  }

  /** POST /api/currencies */
  create(payload: Partial<Currency>): Observable<ApiResponse<Currency>> {
    return this.http.post<ApiResponse<Currency>>(
      `${this.baseUrl}/currencies`,
      payload
    );
  }

  /** PUT /api/currencies/{id} */
  update(
    id: number,
    payload: Partial<Currency>
  ): Observable<ApiResponse<Currency>> {
    return this.http.put<ApiResponse<Currency>>(
      `${this.baseUrl}/currencies/${id}`,
      payload
    );
  }

  /** DELETE /api/currencies/{id} */
  delete(id: number): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(
      `${this.baseUrl}/currencies/${id}`
    );
  }

  /** POST /api/currencies/{id}/toggle-active */
  toggleActive(id: number): Observable<ApiResponse<Currency>> {
    return this.http.post<ApiResponse<Currency>>(
      `${this.baseUrl}/currencies/${id}/toggle-active`,
      {} // pas de body, mais POST quand même
    );
  }

  /** GET /api/currencies/base/currency */
  getBaseCurrency(): Observable<ApiResponse<Currency>> {
    return this.http.get<ApiResponse<Currency>>(
      `${this.baseUrl}/currencies/base/currency`
    );
  }

  // -----------------------------------------
  // ROUTE PUBLIQUE /api/public/currencies/active
  // -----------------------------------------

  /** GET /api/public/currencies/active */
  getActivePublic(): Observable<ApiResponse<Currency[]>> {
    return this.http.get<ApiResponse<Currency[]>>(
      `${this.baseUrl}/public/currencies/active`
    );
  }
}
