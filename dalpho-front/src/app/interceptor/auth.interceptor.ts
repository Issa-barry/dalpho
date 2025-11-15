// src/app/pages/service/auth/auth.interceptor.ts
import { TokenService } from '@/pages/service/token/token.service';
import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
 import { environment } from 'src/environements/environment.dev';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const tokenService = inject(TokenService);
  const token = tokenService.getToken();

  // On ne touche pas aux appels hors API
  if (!req.url.startsWith(environment.apiUrl)) {
    return next(req);
  }

  // On ne met pas le token sur le login / register
  if (
    req.url.endsWith('/auth/login') ||
    req.url.endsWith('/users/clients/create')
  ) {
    return next(req);
  }

  // Si pas de token → requête telle quelle (le back renverra 401 si c'est protégé)
  if (!token) {
    return next(req);
  }

  const authReq = req.clone({
    setHeaders: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });

  return next(authReq);
};
