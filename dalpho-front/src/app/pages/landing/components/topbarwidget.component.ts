import { Component, OnInit, OnDestroy } from '@angular/core';
import { StyleClassModule } from 'primeng/styleclass';
import { Router, RouterModule } from '@angular/router';
import { RippleModule } from 'primeng/ripple';
import { ButtonModule } from 'primeng/button';
import { CommonModule } from '@angular/common';
import { AppFloatingConfigurator } from "@/layout/component/app.floatingconfigurator";
import { AuthService } from '@/pages/service/auth/auth.service';
import { Subscription } from 'rxjs';

@Component({
    selector: 'topbar-widget',
    standalone: true,
    imports: [
        CommonModule,
        RouterModule, 
        StyleClassModule, 
        ButtonModule, 
        RippleModule, 
        AppFloatingConfigurator
    ],
    templateUrl: './topbarwidget.html', 
})
export class TopbarWidget implements OnInit, OnDestroy {
    isLoggedIn = false;
    private authSubscription?: Subscription;

    constructor(
        public router: Router,
        private authService: AuthService
    ) {}

    ngOnInit() {
        // Vérification initiale
        this.isLoggedIn = this.authService.isAuthenticated();
        console.log("test islogged", this.isLoggedIn);
        
        // Souscription aux changements d'authentification
        this.authSubscription = this.authService.currentUser$.subscribe(user => {
            // ✅ FIX: Utilisez getToken() au lieu de hasToken()
            this.isLoggedIn = !!user && !!this.authService.getToken();
            console.log("Auth state changed:", this.isLoggedIn);
        });
    }

    ngOnDestroy() {
        // Nettoyage de la souscription
        this.authSubscription?.unsubscribe();
    }

    logout() {
        this.authService.logout().subscribe({
            next: () => {
                console.log('Déconnexion réussie');
                this.router.navigate(['/auth/login']);
            },
            error: (err) => {
                console.error('Erreur lors de la déconnexion:', err);
                // Même en cas d'erreur, on redirige vers login
                this.router.navigate(['/auth/login']);
            }
        });
    }
}