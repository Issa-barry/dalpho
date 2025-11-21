import { Routes } from '@angular/router';
import { AppLayout } from './app/layout/component/app.layout';
import { Dashboard } from './app/pages/dashboard/dashboard';
import { Documentation } from './app/pages/documentation/documentation';
import { Landing } from './app/pages/landing/landing';
import { Notfound } from './app/pages/notfound/notfound';
import { Login } from '@/pages/auth/login/login';
import { authGuard } from '@/guards/auth.guard';
import { Gestion } from '@/pages/gestion/gestion';
import { Contact } from '@/pages/contact/contact';
import { RoleGuard } from '@/guards/role.guard';

export const appRoutes: Routes = [
    // 🟢 Public : page d'accueil
    { path: '', component: Landing },

    // 🔒 Zone protégée : tout ce qui est dans AppLayout nécessite d'être connecté
    {
        path: '',
        component: AppLayout,
        canActivate: [authGuard],
        children: [
            { path: 'dashboard', component: Dashboard },
            {
                path: 'gestion',
                component: Gestion,
                canActivate: [RoleGuard],
                data: { roles: ['agent', 'manager', 'admin'] }
            },
            { path: 'contact', component: Contact },
            { path: 'uikit', loadChildren: () => import('./app/pages/uikit/uikit.routes') },
            { path: 'documentation', component: Documentation },
            { path: 'pages', loadChildren: () => import('./app/pages/pages.routes') }
        ]
    },

    // 🟢 Autres routes publiques
    { path: 'landing', component: Landing },
    { path: 'login', component: Login },
    { path: 'auth', loadChildren: () => import('./app/pages/auth/auth.routes') },
    { path: 'notfound', component: Notfound },

    // 404
    { path: '**', redirectTo: '/notfound' }
];
