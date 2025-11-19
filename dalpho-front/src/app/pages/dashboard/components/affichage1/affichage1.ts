// src/app/pages/components/affichage1/affichage1.component.ts
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TableModule } from 'primeng/table';
import { TagModule } from 'primeng/tag';
import { TooltipModule } from 'primeng/tooltip';

import { ExchangeRateService } from '@/pages/service/rate/echange-rate';
import { ExchangeRate, RateDirection } from '@/pages/models/ExchangeRate';

type CCY = 'GNF' | 'EUR' | 'USD' | 'XOF' | 'GBP' | 'CHF' | 'CAD';

interface RateRow {
  pair: string;
  base: CCY;
  quote: CCY;
  last: number;
  open: number;
  high: number;
  low: number;
  changeAbs: number;
  changePct: number;
  dir: RateDirection;
  updatedAt: Date;
}

@Component({
  selector: 'app-affichage1',
  standalone: true,
  imports: [CommonModule, TableModule, TagModule, TooltipModule],
  templateUrl: './affichage1.html',
  styleUrl: './affichage1.scss',
})
export class Affichage1Component implements OnInit {
  taux: ExchangeRate[] = [];
  rows: RateRow[] = [];
  loading = false;

  constructor(private exchangeRateService: ExchangeRateService) {}

  ngOnInit(): void {
    // Chargement initial uniquement
    this.loadRates();
  }

  /** 
   * Méthode publique appelée par le composant parent (Dashboard)
   * pour charger/actualiser les taux
   */
  loadRates(): void {
    this.loading = true;

    this.exchangeRateService.getCurrentRates().subscribe({
      next: (res) => {
        const data = res.data ?? [];

        this.taux = data.map((item: any) => new ExchangeRate(item));

        this.rows = this.taux
          .filter(r => !!r.from_currency?.code && !!r.to_currency?.code)
          .map((r): RateRow => {
            const base  = r.from_currency!.code as CCY;
            const quote = r.to_currency!.code as CCY;
            const last  = r.rate;

            const high = r.day_high ?? r.high ?? r.rate;
            const low  = r.day_low  ?? r.low  ?? r.rate;

            const open = r.rate;

            const changeAbs = r.change_abs ?? 0;
            const changePct = r.change_pct ?? 0;
            const dir: RateDirection = r.direction ?? 'flat';

            return {
              pair: `${base}/${quote}`,
              base,
              quote,
              last,
              open,
              high,
              low,
              changeAbs,
              changePct,
              dir,
              updatedAt: r.updated_at ? new Date(r.updated_at) : new Date(),
            };
          });

        console.log('rows BDD: ', this.rows);
      },
      error: (err) => {
        console.error('Erreur chargement taux (affichage1)', err);
      },
      complete: () => {
        this.loading = false;
      },
    });
  }

  /**
   * Formattage des nombres avec séparateurs
   */
  fmt(n: number, decimals = 2): string {
    return n.toLocaleString(undefined, {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    });
  }

  /**
   * Méthode publique pour refresh manuel
   * Appelée par le bouton local ou par le Dashboard
   */
  refreshRates(): void {
    this.loadRates();
  }
}