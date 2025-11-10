import { Component } from '@angular/core';
import { NotificationsWidget } from './components/notificationswidget';
import { StatsWidget } from './components/statswidget';
import { RecentSalesWidget } from './components/recentsaleswidget';
import { BestSellingWidget } from './components/bestsellingwidget';
import { RevenueStreamWidget } from './components/revenuestreamwidget';
import { Change } from './components/change/change';

@Component({
    selector: 'app-dashboard',
    imports: [
        StatsWidget, 
        RecentSalesWidget, 
        BestSellingWidget, 
        RevenueStreamWidget, 
        NotificationsWidget, 
        Change
    ],
    templateUrl: './dashboard.html',
    styleUrl: './dashboard.scss'
})
export class Dashboard {}
