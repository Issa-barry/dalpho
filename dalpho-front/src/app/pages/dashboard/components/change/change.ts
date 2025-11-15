import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';

interface ExchangeRate {
  code: string;
  name: string;
  flag: string;
  rate: number;
  symbol: string;
  trend: 'up' | 'down' | 'stable';
  change: number;
}

@Component({
  selector: 'app-change',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './change.html',
  styleUrl: './change.scss'
})
export class ChangeComponent implements OnInit {
  lastUpdate: Date = new Date();
  loading: boolean = true;
  
  exchangeRates: ExchangeRate[] = [
    {
      code: 'EUR',
      name: 'Euro',
      flag: '🇪🇺',
      rate: 11500,
      symbol: '€',
      trend: 'up',
      change: 0.5
    },
    {
      code: 'USD',
      name: 'Dollar Américain',
      flag: '🇺🇸',
      rate: 10800,
      symbol: '$',
      trend: 'down',
      change: -0.3
    },
    {
      code: 'GBP',
      name: 'Livre Sterling',
      flag: '🇬🇧',
      rate: 13200,
      symbol: '£',
      trend: 'up',
      change: 0.8
    },
    {
      code: 'CHF',
      name: 'Franc Suisse',
      flag: '🇨🇭',
      rate: 12100,
      symbol: 'CHF',
      trend: 'stable',
      change: 0.0
    },
    {
      code: 'XOF',
      name: 'Franc CFA',
      flag: '🌍',
      rate: 17.5,
      symbol: 'CFA',
      trend: 'up',
      change: 0.2
    },
    {
      code: 'CAD',
      name: 'Dollar Canadien',
      flag: '🇨🇦',
      rate: 7800,
      symbol: 'C$',
      trend: 'down',
      change: -0.4
    }
  ];

  ngOnInit(): void {
    // Simuler le chargement des données
    setTimeout(() => {
      this.loading = false;
    }, 1000);

    // Actualiser les taux toutes les 30 secondes
    setInterval(() => {
      this.refreshRates();
    }, 30000);
  }

  refreshRates(): void {
    this.loading = true;
    
    // Simuler l'appel API
    setTimeout(() => {
      this.exchangeRates = this.exchangeRates.map(rate => ({
        ...rate,
        rate: rate.rate + (Math.random() - 0.5) * 100,
        change: (Math.random() - 0.5) * 2,
        trend: Math.random() > 0.5 ? 'up' : Math.random() > 0.3 ? 'down' : 'stable'
      }));
      
      this.lastUpdate = new Date();
      this.loading = false;
    }, 500);
  }

  getTrendIcon(trend: string): string {
    switch(trend) {
      case 'up': return '📈';
      case 'down': return '📉';
      default: return '➡️';
    }
  }

  getTrendClass(trend: string): string {
    switch(trend) {
      case 'up': return 'trend-up';
      case 'down': return 'trend-down';
      default: return 'trend-stable';
    }
  }

  formatNumber(num: number): string {
    return new Intl.NumberFormat('fr-GN', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(num);
  }

  convertAmount(amount: number, rate: number): string {
    return this.formatNumber(amount * rate);
  }
}