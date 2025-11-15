import { AppFloatingConfigurator } from '@/layout/component/app.floatingconfigurator';
import { AuthService } from '@/pages/service/auth/auth.service';
import { CommonModule } from '@angular/common';
 import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { ButtonModule } from 'primeng/button';
import { CheckboxModule } from 'primeng/checkbox';
import { InputTextModule } from 'primeng/inputtext';
import { PasswordModule } from 'primeng/password';
import { RippleModule } from 'primeng/ripple';
 
@Component({
  selector: 'app-login',
      standalone: true,
    imports: [CommonModule, ButtonModule, CheckboxModule, InputTextModule, PasswordModule, FormsModule, RouterModule, RippleModule, AppFloatingConfigurator],
  templateUrl: './login.html',
  styleUrl: './login.scss'
})
export class Login {
     email: string = '';
    password: string = '';
    checked: boolean = false;
    submited: boolean = false;
  errorMessage = '';
  errors: { [key: string]: string } = {};

    constructor(
    private router: Router,
    private route: ActivatedRoute,
    private authService: AuthService,
   ) { }


    login(): void {
       this.errorMessage = '';
        const email = (this.email || '').trim();
        const password = (this.password || '').trim();
        this.submited = true; 

        this.authService.login({email,password}).subscribe({
            next: () => {
                this.submited = false;
                this.router.navigateByUrl('/dashboard');
            },
            error: (err) => {
                this.submited = false;
                this.errorMessage = err?.error?.message || 'Une erreur est survenue lors de la connexion.';

                this.errors = err?.error || { };

                console.log("erreors", this.errors);
                
            }
        });
        
    }
}
