import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';

interface Currency {
  code: string;
  name: string;
  symbol: string;
  flag: string;
  rate: number;
  change: number;
  trend: 'up' | 'down' | 'stable';
  sparklineData: number[];
  color: string;
}


@Component({
  selector: 'app-affichage4',
     imports: [CommonModule],
  standalone: true,
  templateUrl: './affichage4.html',
  styleUrl: './affichage4.scss'
})
export class Affichage4Component implements OnInit {
  // ✅ Ajoutez cette ligne pour accéder à Math dans le template
  Math = Math;
  
  lastUpdate: Date = new Date();
  loading: boolean = false;

  currencies: Currency[] = [
    {
      code: 'EUR',
      name: 'Euro',
      symbol: '€',
      flag: '🇪🇺',
      rate: 10700.00,
      change: 120.50,
      trend: 'up',
      sparklineData: [10500, 10550, 10600, 10650, 10620, 10680, 10700],
      color: '#10b981'
    },
    {
      code: 'USD',
      name: 'Dollar Américain',
      symbol: '$',
      flag: '🇺🇸',
      rate: 8676.56,
      change: -45.30,
      trend: 'down',
      sparklineData: [8750, 8720, 8700, 8680, 8690, 8680, 8676],
      color: '#ef4444'
    },
    {
      code: 'GBP',
      name: 'Livre Sterling',
      symbol: '£',
      flag: '🇬🇧',
      rate: 11436.00,
      change: 89.20,
      trend: 'up',
      sparklineData: [11300, 11350, 11380, 11400, 11420, 11430, 11436],
      color: '#10b981'
    },
    {
      code: 'CHF',
      name: 'Franc Suisse',
      symbol: 'CHF',
      flag: '🇨🇭',
      rate: 10782.00,
      change: 0.00,
      trend: 'stable',
      sparklineData: [10780, 10781, 10782, 10782, 10781, 10782, 10782],
      color: '#6b7280'
    },
    {
      code: 'XOF',
      name: 'Franc CFA',
      symbol: 'CFA',
      flag: '🌍',
      rate: 15.00,
      change: 0.25,
      trend: 'up',
      sparklineData: [14.5, 14.6, 14.7, 14.8, 14.9, 14.95, 15],
      color: '#10b981'
    },
    {
      code: 'CAD',
      name: 'Dollar Canadien',
      symbol: 'C$',
      flag: '🇨🇦',
      rate: 6190.00,
      change: -30.15,
      trend: 'down',
      sparklineData: [6250, 6230, 6210, 6200, 6195, 6190, 6190],
      color: '#ef4444'
    }
  ];

  ngOnInit(): void {
    setInterval(() => {
      this.refreshRates();
    }, 30000);
  }

  refreshRates(): void {
    this.loading = true;
    
    setTimeout(() => {
      this.currencies = this.currencies.map(currency => {
        const newRate = currency.rate + (Math.random() - 0.5) * 100;
        const newChange = (Math.random() - 0.5) * 200;
        const newTrend = newChange > 0 ? 'up' : newChange < 0 ? 'down' : 'stable';
        
        return {
          ...currency,
          rate: newRate,
          change: newChange,
          trend: newTrend,
          color: newTrend === 'up' ? '#10b981' : newTrend === 'down' ? '#ef4444' : '#6b7280',
          sparklineData: [...currency.sparklineData.slice(1), newRate]
        };
      });
      
      this.lastUpdate = new Date();
      this.loading = false;
    }, 500);
  }

  formatNumber(num: number): string {
    return new Intl.NumberFormat('fr-GN', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(num);
  }

  getPercentChange(currency: Currency): number {
    return (currency.change / currency.rate) * 100;
  }

  // ✅ Méthodes helper pour les calculs dans le template
  getSparklinePoints(data: number[]): string {
    const min = Math.min(...data);
    const max = Math.max(...data);
    const range = max - min || 1;
    
    return data
      .map((val, i) => {
        const x = (i * (100 / (data.length - 1)));
        const y = 30 - ((val - min) / range * 25);
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
        const y = 40 - ((val - min) / range * 30);
        return `${x},${y}`;
      })
      .join(' ');
    
    return `0,40 ${points} 100,40`;
  }

  getBarHeight(val: number, data: number[]): number {
    const max = Math.max(...data);
    return (val / max) * 100;
  }
}