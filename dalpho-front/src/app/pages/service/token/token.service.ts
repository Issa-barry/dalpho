import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { environment } from 'src/environements/environment.dev';

type ApiResponse<T = any> = { 
  success: boolean; 
  message: string; 
  data?: T | null; 
};

@Injectable({ providedIn: 'root' })
export class TokenService {
  private readonly apiUrl = environment.apiUrl;
  
  // Clés de stockage
  private readonly TOKEN_KEY = 'auth_token';
  private readonly EXPIRES_KEY = 'token_expires_at';
  private readonly USER_KEY = 'current_user';
  private readonly USER_ID_KEY = 'user_id';

  // Observable du token
  private tokenSubject: BehaviorSubject<string | null>;
  public token$: Observable<string | null>;

  constructor(private http: HttpClient) {
    // Initialisation du token depuis localStorage
    const storedToken = this.getStoredToken();
    this.tokenSubject = new BehaviorSubject<string | null>(storedToken);
    this.token$ = this.tokenSubject.asObservable();

    // Nettoie si le token est expiré au démarrage
    if (storedToken && this.isTokenExpired()) {
      this.clearToken();
    }
  }

  /**
   * Récupère le token depuis localStorage (avec validation)
   */
  private getStoredToken(): string | null {
    const token = localStorage.getItem(this.TOKEN_KEY);
    
    // Nettoie les valeurs invalides
    if (!token || token === 'undefined' || token === 'null' || token.trim() === '') {
      this.clearInvalidToken();
      return null;
    }
    
    return token;
  }

  /**
   * Nettoie un token invalide
   */
  private clearInvalidToken(): void { 
    localStorage.removeItem(this.TOKEN_KEY);
  }

  /**
   * Stocke le token avec sa date d'expiration
   */
  storeToken(token: string, expiresIn: number): void {
    // Validation du token
    if (!token || token === 'undefined' || token === 'null' || token.trim() === '') {
      console.warn('Tentative de stockage d\'un token invalide');
      this.clearToken();
      return;
    }

    // Calcule la date d'expiration
    const expiresAt = Date.now() + (expiresIn * 1000);

    // Stocke le token et son expiration
    localStorage.setItem(this.TOKEN_KEY, token);
    localStorage.setItem(this.EXPIRES_KEY, expiresAt.toString());
    
    // Met à jour l'observable
    this.tokenSubject.next(token);
  }

  /**
   * Supprime le token et toutes les données associées
   */
  clearToken(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    localStorage.removeItem(this.EXPIRES_KEY);
    localStorage.removeItem(this.USER_KEY);
    localStorage.removeItem(this.USER_ID_KEY);
    
    this.tokenSubject.next(null);
  }

  /**
   * Récupère le token actuel
   */
  getToken(): string | null {
    // Vérifie l'expiration avant de retourner
    if (this.isTokenExpired()) {
      this.clearToken();
      return null;
    }
    
    return this.tokenSubject.value;
  }

  /**
   * Vérifie si un token existe et est valide
   */
  hasToken(): boolean {
    const token = this.getToken();
    return !!token && !this.isTokenExpired();
  }

  /**
   * Vérifie si le token est expiré
   */
  isTokenExpired(): boolean {
    const expiresAt = localStorage.getItem(this.EXPIRES_KEY);
    
    if (!expiresAt) {
      return true;
    }
    
    const now = Date.now();
    const expiry = parseInt(expiresAt, 10);
    
    // Vérifie que la valeur est un nombre valide
    if (isNaN(expiry)) {
      return true;
    }
    
    return expiry <= now;
  }

  /**
   * Retourne le temps restant avant expiration (en secondes)
   */
  getTokenTimeRemaining(): number {
    const expiresAt = localStorage.getItem(this.EXPIRES_KEY);
    
    if (!expiresAt) {
      return 0;
    }
    
    const now = Date.now();
    const expiry = parseInt(expiresAt, 10);
    
    if (isNaN(expiry)) {
      return 0;
    }
    
    const remaining = Math.floor((expiry - now) / 1000);
    return remaining > 0 ? remaining : 0;
  }

  /**
   * Retourne la date d'expiration du token
   */
  getTokenExpiryDate(): Date | null {
    const expiresAt = localStorage.getItem(this.EXPIRES_KEY);
    
    if (!expiresAt) {
      return null;
    }
    
    const expiry = parseInt(expiresAt, 10);
    
    if (isNaN(expiry)) {
      return null;
    }
    
    return new Date(expiry);
  }

  /**
   * Vérifie la validité du token côté API
   * Endpoint : GET /api/v1/check-token-header
   */
  verifyToken(): Observable<boolean> {
    if (!this.hasToken()) {
      return of(false);
    }

    return this.http
      .get<ApiResponse>(`${this.apiUrl}/check-token-header`)
      .pipe(
        map(response => {
          if (response && response.success) {
            return true;
          }
          
          // Token invalide selon l'API
          this.clearToken();
          return false;
        }),
        catchError(error => {
          console.error('Erreur lors de la vérification du token:', error);
          
          // Si 401, le token est invalide
          if (error.status === 401) {
            this.clearToken();
          }
          
          return of(false);
        })
      );
  }

  /**
   * Debug: Affiche les infos du token
   */
  getTokenInfo(): {
    token: string | null;
    hasToken: boolean;
    isExpired: boolean;
    timeRemaining: number;
    expiryDate: Date | null;
  } {
    return {
      token: this.getToken(),
      hasToken: this.hasToken(),
      isExpired: this.isTokenExpired(),
      timeRemaining: this.getTokenTimeRemaining(),
      expiryDate: this.getTokenExpiryDate()
    };
  }
}