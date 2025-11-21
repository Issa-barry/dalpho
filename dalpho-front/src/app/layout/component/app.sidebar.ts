import { Component, ElementRef } from '@angular/core';
import { AppMenu } from './app.menu';
import { of } from 'rxjs';
import { Router } from '@angular/router';
import { AuthService } from '@/pages/service/auth/auth.service';

@Component({
    selector: 'app-sidebar',
    standalone: true,
    imports: [AppMenu],
    templateUrl: './app.sidebar.html',
    styleUrls: ['./app.sidebar.scss']
})
export class AppSidebar {
    constructor(
        public el: ElementRef,
         private router: Router,
         private authService: AuthService
        ) {}
    
    logout() {
        this.authService.logout().subscribe({
            next: () => {
                this.router.navigate(['/login']);
            },
            error: (err) => {
                console.error('Erreur lors de la déconnexion :', err);
            }
        })
      
   }
}
