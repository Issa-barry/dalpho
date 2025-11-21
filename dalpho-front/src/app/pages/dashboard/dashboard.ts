import { Component, ViewChild, OnDestroy, AfterViewInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ButtonModule } from 'primeng/button';
import { BestSellingWidget } from './components/bestsellingwidget';
import { ChangeComponent } from './components/change/change';
import { Affichage1Component } from './components/affichage1/affichage1';
import { Affichage2Component } from './components/affichage2/affichage2';
import { Affichage3Component } from './components/affichage3/affichage3';
import { Affichage4Component } from './components/affichage4/affichage4';
import { DividerModule } from 'primeng/divider';
 
@Component({
    selector: 'app-dashboard',
    standalone: true,
    imports: [
        CommonModule, // ✅ Ajout pour ngClass
        ButtonModule,
        BestSellingWidget, 
        ChangeComponent,
        Affichage1Component,
        Affichage2Component,
        Affichage3Component,
        Affichage4Component,
        DividerModule
    ],
    templateUrl: './dashboard.html',
    styleUrl: './dashboard.scss'
})
export class Dashboard implements AfterViewInit, OnDestroy {
    @ViewChild(Affichage1Component) affichage1!: Affichage1Component;
    @ViewChild(Affichage2Component) affichage2!: Affichage2Component;

    private refreshTimer?: any;
    isRefreshing = false; // ✅ État de chargement global

    ngAfterViewInit(): void {
        // Actualisation centralisée toutes les 30s
        this.refreshTimer = setInterval(() => {
            this.refreshAllRates();
        }, 30_000);
    }

    ngOnDestroy(): void {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }
    }

    /**
     * Actualise tous les composants de taux en même temps
     */
    refreshAllRates(): void {
        this.isRefreshing = true;
        console.log('🔄 Actualisation globale des taux...');
        
        if (this.affichage1) {
            this.affichage1.loadRates();
        }
        if (this.affichage2) {
            this.affichage2.loadRates();
        }

        // Arrêter l'animation après 2 secondes
        setTimeout(() => {
            this.isRefreshing = false;
        }, 2000);
    }
}