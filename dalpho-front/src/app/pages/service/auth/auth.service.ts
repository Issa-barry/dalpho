import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { BehaviorSubject, Observable, throwError } from 'rxjs';
import { catchError, map, tap } from 'rxjs/operators';
import { environment } from 'src/environements/environment.dev';
import { Router } from '@angular/router';
import { TokenService } from '../token/token';
import { Contact } from '@/pages/models/contact';
 

export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T | null;
}
 
export interface LoginResponse {
  user: Contact;
  access_token: string;
  token_type: string;
  expires_in: number;
  expires_at: string;
} 

@Injectable({ providedIn: 'root' })
export class AuthService {
  private apiUrl = environment.apiUrl;
  private readonly STORAGE_USER = 'current_user';
  private readonly STORAGE_USER_ID = 'user_id';
  private readonly LEGACY_KEYS = ['user', 'auth_user', 'auth', 'token', 'access_token'];

  // On n'hydrate pas ici pour éviter JSON.parse au démarrage
  private currentUserSubject = new BehaviorSubject<Contact | null>(null);
  public currentUser$ = this.currentUserSubject.asObservable();

  constructor(
    private http: HttpClient,
    private router: Router,
    private tokenService: TokenService
  ) {}

  /** A appeler au bootstrap (via APP_INITIALIZER) */
  initOnStartup(): void {
    this.hydrateFromStorageSafely();
    this.checkTokenValidity();
  }

  /** Essaie de charger l'utilisateur depuis localStorage en gérant "undefined"/JSON cassé */
  private hydrateFromStorageSafely(): void {
    const raw = localStorage.getItem(this.STORAGE_USER);

    if (!raw || raw === 'undefined' || raw === 'null') {
      this.clearAuthData(true);
      return;
    }

    try {
      const user: Contact = JSON.parse(raw);
      if (user && typeof user === 'object') {
        this.currentUserSubject.next(user);
      } else {
        this.clearAuthData(true);
      }
    } catch {
      this.clearAuthData(true);
    }
  }

  public get currentUserValue(): Contact | null {
    return this.currentUserSubject.value;
  }

  /** Vérifie la validité du token au démarrage */
  private checkTokenValidity(): void {
    if (this.tokenService.isTokenExpired()) {
      this.clearAuthData(true);
    }
  }

  /** Stocke les données d'authentification */
  private setAuthData(token: string, user: Contact, expiresIn: number): void {
    this.tokenService.storeToken(token, expiresIn);
    this.currentUserSubject.next(user);
    localStorage.setItem(this.STORAGE_USER, JSON.stringify(user));
    localStorage.setItem(this.STORAGE_USER_ID, String(user.id));
  }

  /** Nettoie toutes les données d'authentification */
  clearAuthData(keepRoute = false): void {
    try {
      this.tokenService.clearToken();
    } finally {
      localStorage.removeItem(this.STORAGE_USER);
      localStorage.removeItem(this.STORAGE_USER_ID);
      // Nettoyage des anciennes clés si elles ont été utilisées par le passé
      this.LEGACY_KEYS.forEach(k => localStorage.removeItem(k));
      this.currentUserSubject.next(null);
    }
    if (!keepRoute) {
      this.router.navigate(['/auth/login']);
    }
  }

  /** Gestion des erreurs HTTP */
  private handleError = (error: HttpErrorResponse) => {
    console.error('HTTP Error:', error);

    // Messages (si tu les utilises ailleurs, expose-les depuis ici)
    // let msg = 'Une erreur inconnue est survenue';

    if (error.status === 401) {
      this.clearAuthData();
    } else if (error.status === 419) {
      this.clearAuthData();
    }

    return throwError(() => error);
  };

  /** LOGIN STATELESS */
  login(credentials: { email: string; password: string }): Observable<LoginResponse> { 
    return this.http
      .post<ApiResponse<LoginResponse>>(`${this.apiUrl}/auth/login`, credentials)
      .pipe(
        map(res => {
          if (!res.data) throw new Error('Réponse invalide du serveur');
          return res.data;
        }),
        tap(data => this.setAuthData(data.access_token, data.user, data.expires_in)),
        catchError(this.handleError)
      );
  }

  /** LOGOUT */
  logout(): Observable<any> {
    return this.http.post<ApiResponse>(`${this.apiUrl}/logout`, {}).pipe(
      tap(() => this.clearAuthData()),
      catchError(err => {
        // Même en cas d'erreur serveur, on nettoie localement
        this.clearAuthData();
        return throwError(() => err);
      })
    );
  }

  /** Récupère l'utilisateur connecté */
/** Récupère l'utilisateur connecté */
getMe(): Observable<Contact> {
  return this.http
    .get<ApiResponse<{ user: Contact }>>(`${this.apiUrl}/users/me`, {
      headers: { Accept: 'application/json' }
    })
    .pipe(
      map(res => {
        if (!res.data?.user) throw new Error('Utilisateur non trouvé');
        return res.data.user;
      }),
      tap(user => {
        this.currentUserSubject.next(user);
        localStorage.setItem(this.STORAGE_USER, JSON.stringify(user));
        localStorage.setItem(this.STORAGE_USER_ID, String(user.id));
      }),
      catchError(this.handleError)
    );
}


  /** INSCRIPTION */
/** INSCRIPTION */
register(payload: Contact): Observable<LoginResponse> {
  return this.http
    .post<ApiResponse<LoginResponse>>(`${this.apiUrl}/users/clients/create`, payload)
    .pipe(
      map(res => {
        if (!res.data) throw new Error('Erreur lors de la création du compte');
        return res.data;
      }),
      tap(data => {
        // Vérifier si on a un access_token ET un user valide
        if (data.access_token && data.user && data.user.id) {
          this.setAuthData(data.access_token, data.user, data.expires_in);
        } else if (data.user && data.user.id) {
          // Si pas de token mais user valide
          this.currentUserSubject.next(data.user);
          localStorage.setItem(this.STORAGE_USER, JSON.stringify(data.user));
          localStorage.setItem(this.STORAGE_USER_ID, String(data.user.id));
        } else {
          // Si l'user n'a pas d'ID, on ne stocke rien
          console.warn('User créé mais sans ID valide:', data.user);
        }
      }),
      catchError(this.handleError)
    );
}

  /** L'utilisateur est-il authentifié ? */
  isAuthenticated(): boolean {
    const hasToken = this.tokenService.hasToken();
    const hasUser = !!this.currentUserValue;
    if (!hasToken || !hasUser) return false;

    if (this.tokenService.isTokenExpired()) {
      this.clearAuthData(true);
      return false;
    }
    return true;
  }

  /** ID utilisateur */
  getUserId(): number | null {
    const id = this.currentUserValue?.id ?? Number(localStorage.getItem(this.STORAGE_USER_ID));
    return Number.isFinite(id) ? Number(id) : null;
  }

  /** Token */
  getToken(): string | null {
    return this.tokenService.getToken();
  }

  /** Token expiré ? */
  isTokenExpired(): boolean {
    return this.tokenService.isTokenExpired();
  }

  /** Temps restant avant expiration (s) */
  getTokenTimeRemaining(): number {
    return this.tokenService.getTokenTimeRemaining();
  }

  /** Vérifie le token côté serveur */
  verifyToken(): Observable<boolean> {
    return this.tokenService.verifyToken();
  }

  /** Infos token (debug) */
  getTokenInfo() {
    return this.tokenService.getTokenInfo();
  }
}
