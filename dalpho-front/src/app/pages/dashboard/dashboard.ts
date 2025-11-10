import { Component } from '@angular/core';
import { NotificationsWidget } from './components/notificationswidget';
import { StatsWidget } from './components/statswidget';
import { RecentSalesWidget } from './components/recentsaleswidget';
import { BestSellingWidget } from './components/bestsellingwidget';
import { RevenueStreamWidget } from './components/revenuestreamwidget';
import { ChangeComponent } from './components/change/change';
import { Affichage1Component } from './components/affichage1/affichage1';
import { Affichage2Component } from './components/affichage2/affichage2';
import { Affichage3Component } from './components/affichage3/affichage3';
 
@Component({
    selector: 'app-dashboard',
    imports: [
         BestSellingWidget, 
         ChangeComponent,
        Affichage1Component,
        Affichage2Component,
        Affichage3Component
    ],
    templateUrl: './dashboard.html',
    styleUrl: './dashboard.scss'
})
export class Dashboard {}
