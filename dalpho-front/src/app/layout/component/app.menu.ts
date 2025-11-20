import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MenuItem } from 'primeng/api';
import { AppMenuitem } from './app.menuitem';
import { AuthService } from '@/pages/service/auth/auth.service';
 
@Component({
    selector: 'app-menu',
    standalone: true,
    imports: [CommonModule, AppMenuitem, RouterModule],
    template: `
        <ul class="layout-menu">
            <ng-container *ngFor="let item of model; let i = index">
                <li
                    app-menuitem
                    *ngIf="!item.separator"
                    [item]="item"
                    [index]="i"
                    [root]="true"
                ></li>
                <li *ngIf="item.separator" class="menu-separator"></li>
            </ng-container>
        </ul>
    `,
})
export class AppMenu {
    model: MenuItem[] = [];

    constructor(private auth: AuthService) {}

    ngOnInit() {
        const role = this.auth.getRole(); // string | null

        // Si pas de rôle (pas encore loggé, ou bug), on peut :
        // - soit ne rien afficher,
        // - soit afficher seulement Accueil.
        if (!role) {
            this.model = [
                {
                    label: 'landing',
                    items: [
                        {
                            label: 'Acceuil',
                            icon: 'pi pi-fw pi-home',
                            routerLink: ['/landing'],
                        },
                    ],
                },
            ];
            return;
        }

        // Menu complet avec rôles
        const fullMenu: (MenuItem & { roles?: string[] })[] = [
            {
                label: 'Dashboard',
                items: [
                    {
                        label: 'Acceuil',
                        icon: 'pi pi-fw pi-home',
                        routerLink: ['/dashboard'],
                        roles: ['client', 'agent', 'manager', 'admin'],
                    },
                    {
                        label: 'Gestion',
                        icon: 'pi pi-fw pi-cog',
                        routerLink: ['/dashboard/gestion'],
                        roles: ['agent', 'manager', 'admin'],
                    },
                    {
                        label: 'Contact',
                        icon: 'pi pi-fw pi-users',
                        routerLink: ['/dashboard/contact'],
                        roles: ['manager', 'admin'],
                    },
                ],
            },
        ];

        // Filtrage par rôle (ici `role` est forcément string)
        this.model = fullMenu.map(section => ({
            ...section,
            items: section.items?.filter(item => {
                const allowedRoles = (item as any).roles as string[] | undefined;
                // si pas de "roles" défini → visible pour tous
                if (!allowedRoles || allowedRoles.length === 0) return true;
                return allowedRoles.includes(role);
            }),
        }));
    }
}
