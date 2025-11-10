import { Component, OnDestroy, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TableModule } from 'primeng/table';
import { TagModule } from 'primeng/tag';
import { TooltipModule } from 'primeng/tooltip';

type CCY = 'GNF' | 'EUR' | 'USD' | 'XOF' | 'GBP' | 'CHF' | 'CAD';

interface RateRow {
  pair: string;          // ex: "EUR/GNF"
  base: CCY;
  quote: CCY;
  last: number;          // dernier cours (mid)
  open: number;          // cours d'ouverture (session)
  high: number;          // plus haut session
  low: number;           // plus bas session
  changeAbs: number;     // variation absolue vs open
  changePct: number;     // variation %
  dir: 'up' | 'down' | 'flat';
  updatedAt: Date;
}

@Component({
  selector: 'app-affichage1',
  standalone: true,
  imports: [CommonModule, TableModule, TagModule, TooltipModule],
  templateUrl: './affichage1.html',
  styleUrl: './affichage1.scss'
})
export class Affichage1Component implements OnInit, OnDestroy {
  /** Cours MID contre GNF (pivot) à l’ouverture */
  private openAgainstGNF: Record<CCY, number> = {
    GNF: 1,
    EUR: 10700,  // 1 € = 10 700 GNF
    USD: 9500,
    XOF: 17.5,
    GBP: 12500,
    CHF: 10800,
    CAD: 7200,
  };

  rows: RateRow[] = [];
  private timer?: any;

  ngOnInit(): void {
    const pairs: [CCY, CCY][] = [
      ['EUR','GNF'], ['USD','GNF'], ['GBP','GNF'],
      ['CHF','GNF'], ['CAD','GNF'], ['XOF','GNF'],
      ['EUR','USD'], ['EUR','XOF'], ['USD','XOF']
    ];

    this.rows = pairs.map(([b, q]) => {
      const last = this.computeCross(b, q);
      return {
        pair: `${b}/${q}`,
        base: b, quote: q,
        last, open: last, high: last, low: last,
        changeAbs: 0, changePct: 0, dir: 'flat',
        updatedAt: new Date()
      };
    });

    this.timer = setInterval(() => this.tick(), 3000);
  }

  ngOnDestroy(): void {
    if (this.timer) clearInterval(this.timer);
  }

  /** Cross via pivot GNF si nécessaire */
  private computeCross(from: CCY, to: CCY): number {
    if (from === to) return 1;
    const gnfPerFrom = this.openAgainstGNF[from];
    const gnfPerTo = this.openAgainstGNF[to];
    return gnfPerFrom / gnfPerTo;
  }

  /** Petite variation de marché (+/- 0,30% par tick) */
  private perturb(value: number): number {
    const max = 0.003; // 30 bps
    const drift = (Math.random() * 2 - 1) * max;
    return value * (1 + drift);
  }

  private tick(): void {
    this.rows = this.rows.map(r => {
      let next = this.perturb(r.last);
      const decimals = (r.quote === 'GNF' || r.quote === 'XOF') ? 0 : 4;
      next = Number(next.toFixed(decimals));

      const high = Math.max(r.high, next);
      const low  = Math.min(r.low, next);
      const changeAbs = next - r.open;
      const changePct = r.open ? (changeAbs / r.open) * 100 : 0;
      const dir: RateRow['dir'] =
        changeAbs > 0.0000001 ? 'up' : changeAbs < -0.0000001 ? 'down' : 'flat';

      return {
        ...r,
        last: next,
        high, low,
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
