import { Component, OnDestroy, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CardModule } from 'primeng/card';
import { TagModule } from 'primeng/tag';
import { TooltipModule } from 'primeng/tooltip';

type CCY = 'GNF' | 'EUR' | 'USD' | 'XOF' | 'GBP' | 'CHF' | 'CAD';

interface RateCard {
  code: CCY;
  name: string;
  symbol: string;
  flag: string;
  last: number;
  open: number;
  changeAbs: number;
  changePct: number;
  dir: 'up' | 'down' | 'flat';
  updatedAt: Date;
}

@Component({
  selector: 'app-affichage3',
  standalone: true,
  imports: [CommonModule, CardModule, TagModule, TooltipModule],
  templateUrl: './affichage3.html',
  styleUrl: './affichage3.scss'
})
export class Affichage3Component implements OnInit, OnDestroy {

  /** Référence : 1 unité = X GNF (valeurs d’ouverture) */
  private openAgainstGNF: Record<CCY, number> = {
    GNF: 1,
    EUR: 10700,
    USD: 9500,
    XOF: 17.5,
    GBP: 12500,
    CHF: 10800,
    CAD: 7200,
  };

  cards: RateCard[] = [];
  private timer?: any;

  ngOnInit(): void {
    this.cards = [
      { code: 'EUR', name: 'Euro', symbol: '€',  flag: '🇪🇺', last: this.openAgainstGNF.EUR, open: this.openAgainstGNF.EUR, changeAbs: 0, changePct: 0, dir: 'flat', updatedAt: new Date() },
      { code: 'USD', name: 'Dollar US', symbol: '$', flag: '🇺🇸', last: this.openAgainstGNF.USD, open: this.openAgainstGNF.USD, changeAbs: 0, changePct: 0, dir: 'flat', updatedAt: new Date() },
      { code: 'GBP', name: 'Livre sterling', symbol: '£', flag: '🇬🇧', last: this.openAgainstGNF.GBP, open: this.openAgainstGNF.GBP, changeAbs: 0, changePct: 0, dir: 'flat', updatedAt: new Date() },
      { code: 'CHF', name: 'Franc suisse', symbol: 'CHF', flag: '🇨🇭', last: this.openAgainstGNF.CHF, open: this.openAgainstGNF.CHF, changeAbs: 0, changePct: 0, dir: 'flat', updatedAt: new Date() },
      { code: 'CAD', name: 'Dollar canadien', symbol: '$', flag: '🇨🇦', last: this.openAgainstGNF.CAD, open: this.openAgainstGNF.CAD, changeAbs: 0, changePct: 0, dir: 'flat', updatedAt: new Date() },
      { code: 'XOF', name: 'Franc CFA (UEMOA)', symbol: 'FCFA', flag: '🇸🇳', last: this.openAgainstGNF.XOF, open: this.openAgainstGNF.XOF, changeAbs: 0, changePct: 0, dir: 'flat', updatedAt: new Date() },
    ];

    // Simule une variation de marché toutes les 3s
    this.timer = setInterval(() => this.tick(), 3000);
  }

  ngOnDestroy(): void {
    if (this.timer) clearInterval(this.timer);
  }

  private perturb(value: number): number {
    const max = 0.003; // ±0,30%
    const drift = (Math.random() * 2 - 1) * max;
    return value * (1 + drift);
  }

  private tick(): void {
    this.cards = this.cards.map(c => {
      let next = this.perturb(c.last);
      const decimals = (c.code === 'GNF' || c.code === 'XOF') ? 0 : 2;
      next = Number(next.toFixed(decimals));

      const changeAbs = next - c.open;
      const changePct = (changeAbs / c.open) * 100;
      const dir: RateCard['dir'] =
        changeAbs > 0.0001 ? 'up' : changeAbs < -0.0001 ? 'down' : 'flat';

      return {
        ...c,
        last: next,
        changeAbs,
        changePct,
        dir,
        updatedAt: new Date()
      };
    });
  }

  fmt(n: number, decimals = 2): string {
    return n.toLocaleString(undefined, {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    });
  }
}
