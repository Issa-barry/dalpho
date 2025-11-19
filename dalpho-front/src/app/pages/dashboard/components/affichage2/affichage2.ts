// src/app/pages/components/affichage2/affichage2.component.ts
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ExchangeRateService } from '@/pages/service/rate/echange-rate';
import { ExchangeRate } from '@/pages/models/ExchangeRate';

type Trend = 'up' | 'down' | 'flat';

interface CurrencyCard {
  code: string;
  label: string;
  name: string;
  pair: string;
  rate: number;
  changeAbs: number;
  changePct: number;
  trend: Trend;
  color: string;
  lastUpdate: Date;
  baseAmount: number;
  baseEquivalent: number;
  sparklineData: number[];
}

@Component({
  selector: 'app-affichage2',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './affichage2.html',
  styleUrl: './affichage2.scss',
})
export class Affichage2Component implements OnInit {
  Math = Math;

  loading = false;
  lastUpdate: Date = new Date();
  cards: CurrencyCard[] = [];

  /**
   * Mémoire des courbes par devise.
   * - clé = code (EUR, USD, GBP…)
   * - valeur = tableau de points déjà calculés
   */
  private sparklineStore = new Map<string, number[]>();

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
        const taux: ExchangeRate[] = data.map((d: any) => new ExchangeRate(d));

        this.cards = taux
          .filter((r) => r.to_currency?.code === 'GNF')
          .map((r): CurrencyCard => {
            const fromCode = r.from_currency?.code ?? 'XXX';
            const toCode = r.to_currency?.code ?? '';
            const pair = `${fromCode}/${toCode}`;

            const changeAbs = r.change_abs ?? 0;
            const changePct = r.change_pct ?? 0;

            let trend: Trend = 'flat';
            if (changeAbs > 0) trend = 'up';
            else if (changeAbs < 0) trend = 'down';

            const color =
              trend === 'up'
                ? '#10b981'
                : trend === 'down'
                ? '#ef4444'
                : '#6b7280';

            const rate = r.rate;
            const baseAmount = 100;
            const baseEquivalent = baseAmount * rate;

            // 🔹 Sparkline basée sur une mémoire par devise
            const sparklineData = this.buildSparkline(fromCode, rate);

            return {
              code: fromCode,
              label: fromCode.substring(0, 2),
              name: r.from_currency?.name ?? '',
              pair,
              rate,
              changeAbs,
              changePct,
              trend,
              color,
              lastUpdate: r.updated_at ? new Date(r.updated_at) : new Date(),
              baseAmount,
              baseEquivalent,
              sparklineData,
            };
          });

        if (this.cards.length) {
          this.lastUpdate = this.cards[0].lastUpdate;
        } else {
          this.lastUpdate = new Date();
        }
      },
      error: (err) => {
        console.error('Erreur chargement taux (affichage2)', err);
      },
      complete: () => {
        this.loading = false;
      },
    });
  }

  /**
   * Méthode publique pour refresh manuel
   * Appelée par le bouton local ou par le Dashboard
   */
  refreshRates(): void {
    this.loadRates();
  }

  /**
   * Sparkline "intelligente" :
   * - Si aucune donnée stockée pour cette devise → 8 points identiques (trait plat).
   * - Si le taux n'a pas changé → on garde EXACTEMENT la même série (graphique figé).
   * - Si le taux change → on décale la série et on ajoute le nouveau point.
   */
  private buildSparkline(code: string, rate: number): number[] {
    const points = 8;
    const previous = this.sparklineStore.get(code);

    // 1) Jamais vu cette devise dans la session → ligne plate
    if (!previous || previous.length === 0) {
      const initial = Array(points).fill(rate);
      this.sparklineStore.set(code, initial);
      return initial;
    }

    const lastVal = previous[previous.length - 1];

    // 2) Aucun changement de taux → on NE TOUCHE À RIEN
    if (lastVal === rate) {
      return previous;
    }

    // 3) Nouveau taux → on décale et on push la nouvelle valeur
    let next = [...previous];

    if (next.length >= points) {
      next = next.slice(1); // on enlève le 1er
    }

    next.push(rate);

    // sécurité pour garder exactement "points" valeurs
    if (next.length > points) {
      next = next.slice(next.length - points);
    }

    this.sparklineStore.set(code, next);
    return next;
  }

  formatNumber(num: number, decimals = 2): string {
    return new Intl.NumberFormat('fr-GN', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    }).format(num);
  }

  getPercentChange(card: CurrencyCard): number {
    if (card.changePct !== 0) {
      return card.changePct;
    }
    return card.rate
      ? (card.changeAbs / (card.rate - card.changeAbs)) * 100
      : 0;
  }

  /* ===== Helpers sparkline (SVG) ===== */

  getSparklinePoints(data: number[]): string {
    const min = Math.min(...data);
    const max = Math.max(...data);
    const range = max - min || 1;

    return data
      .map((val, i) => {
        const x = (i * (100 / (data.length - 1)));
        const y = 30 - ((val - min) / range) * 25;
        return `${x},${y}`;
      })
      .join(' ');
  }

  getAreaPoints(data: number[]): string {
    const min = Math.min(...data);
    const max = Math.max(...data);
    const range = max - min || 1;

    const points = data
      .map((val, i) => {
        const x = (i * (100 / (data.length - 1)));
        const y = 40 - ((val - min) / range) * 30;
        return `${x},${y}`;
      })
      .join(' ');

    return `0,40 ${points} 100,40`;
  }
}