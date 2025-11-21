import { provideHttpClient, withFetch, withInterceptors } from '@angular/common/http';
import { ApplicationConfig, APP_INITIALIZER } from '@angular/core';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';
import { provideRouter, withEnabledBlockingInitialNavigation, withInMemoryScrolling } from '@angular/router';
import Aura from '@primeuix/themes/aura';
import { providePrimeNG } from 'primeng/config';
import { appRoutes } from './app.routes';
import { authInterceptor } from '@/interceptor/auth.interceptor';
import { AuthService } from '@/pages/service/auth/auth.service';

// Factory pour initialiser AuthService au démarrage de l'app
export function initializeAuth(authService: AuthService) {
  return () => {
    console.log('🔧 Initialisation de AuthService...');
    authService.initOnStartup();
    console.log('✅ AuthService initialisé');
    return Promise.resolve();
  };
}

export const appConfig: ApplicationConfig = {
    providers: [
        provideRouter(
            appRoutes, 
            withInMemoryScrolling({ 
                anchorScrolling: 'enabled', 
                scrollPositionRestoration: 'enabled' 
            }), 
            withEnabledBlockingInitialNavigation()
        ),
        provideHttpClient(
            withFetch(),
            withInterceptors([authInterceptor]) 
        ),
        provideAnimationsAsync(),
        providePrimeNG({ 
            theme: { 
                preset: Aura, 
                options: { darkModeSelector: '.app-dark' } 
            } 
        }),
        // 🔥 Initialisation de AuthService avant le bootstrap de l'app
        {
            provide: APP_INITIALIZER,
            useFactory: initializeAuth,
            deps: [AuthService],
            multi: true
        }
    ]
};
 