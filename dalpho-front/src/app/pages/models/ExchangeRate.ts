// src/app/pages/models/ExchangeRate.ts
import { Agent } from "./Agent";
import { Currency } from "./Currency";

export type RateDirection = 'up' | 'down' | 'flat';

export class ExchangeRate {

  id?: number;

  from_currency_id!: number;
  to_currency_id!: number;

  rate: number = 0;
  agent_id?: number | null;

  effective_date: string = "";
  is_current: boolean = false;

  created_at?: string;
  updated_at?: string;

  // Relations
  from_currency: Currency = new Currency();
  to_currency: Currency = new Currency();
  agent?: Agent;

  // High / Low (jour ou génériques)
  high?: number;
  low?: number;
  day_high?: number;
  day_low?: number;

  // Variation envoyée par le back
  change_abs: number = 0;
  change_pct: number = 0;
  direction: RateDirection = 'flat';

  constructor(data?: any) {
    if (data) {
      Object.assign(this, data);

      // numériques (souvent string côté Laravel)
      if (data.rate !== undefined)      this.rate      = Number(data.rate);
      if (data.high !== undefined)      this.high      = Number(data.high);
      if (data.low !== undefined)       this.low       = Number(data.low);
      if (data.day_high !== undefined)  this.day_high  = Number(data.day_high);
      if (data.day_low !== undefined)   this.day_low   = Number(data.day_low);
      if (data.change_abs !== undefined) this.change_abs = Number(data.change_abs);
      if (data.change_pct !== undefined) this.change_pct = Number(data.change_pct);

      if (data.direction) {
        this.direction = data.direction as RateDirection;
      }

      // Relations
      if (data.from_currency) this.from_currency = new Currency(data.from_currency);
      if (data.to_currency)   this.to_currency   = new Currency(data.to_currency);
      if (data.agent)         this.agent         = new Agent(data.agent);
    }
  }

  convert(amount: number): number {
    return amount * this.rate;
  }

  inverse(): number {
    return this.rate !== 0 ? 1 / this.rate : 0;
  }

  formatted(decimals = 4): string {
    return this.rate.toLocaleString('fr-FR', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    });
  }

  description(): string {
    const from = this.from_currency?.code ?? "FROM";
    const to   = this.to_currency?.code   ?? "TO";
    return `1 ${from} = ${this.formatted()} ${to}`;
  }
}
