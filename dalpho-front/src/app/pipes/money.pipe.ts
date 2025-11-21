import { Pipe, PipeTransform } from '@angular/core';

type Space = 'narrow' | 'normal' | 'wide' | 'figure' | 'en' | 'em';
function isSpaceKeyword(x: any): x is Space {
  return x === 'narrow' || x === 'normal' || x === 'wide' || x === 'figure' || x === 'en' || x === 'em';
}

@Pipe({ name: 'money', standalone: true, pure: true })
export class MoneyPipe implements PipeTransform {
  private defaults: Record<string, number> = { EUR: 2, GNF: 0 };

  transform(
    value: number | string | null | undefined,
    currency: 'EUR' | 'GNF' | string = 'GNF',
    decOrSpace: number | 'auto' | Space = 'auto',   // 2e param: décimales OU type d’espace interne
    compact = false,
    display: 'symbol' | 'code' | 'narrow' | 'none' = 'narrow',
    locale = 'fr-FR',
    gap: 'narrow' | 'normal' | 'wide' = 'normal'
  ): string {
    if (value === null || value === undefined || value === '') return '';
    const num = typeof value === 'string' ? Number(value) : value;
    if (!Number.isFinite(num)) return '';

    const cur = (currency || 'GNF').toUpperCase();

    // --- 2e param: narrow type safely ---
    let group: Space | null = null;
    let decimals: number | 'auto';
    if (typeof decOrSpace === 'string' && isSpaceKeyword(decOrSpace)) {
      group = decOrSpace;                  // on utilise ce mot-clé pour l’espace des milliers
      decimals = 'auto';
    } else {
      decimals = decOrSpace as number | 'auto';
    }

    // décimales par défaut
    const defaultD = this.defaults[cur] ?? 0;
    const d = decimals === 'auto' ? defaultD : (decimals as number);

    // 👉 EUR: si centimes = 0 alors 0 décimales (ex: 5,00€ -> 5 €)
    const cents = Math.round(Math.abs(num) * 100) % 100;
    const isZeroCentsEUR = (cur === 'EUR') && (cents === 0) && (decimals === 'auto');
    const minFD = isZeroCentsEUR ? 0 : d;
    const maxFD = d;

    // espaces (entre nombre et devise + entre milliers)
    const gapSpace = gap === 'wide' ? '\u00A0\u00A0' : gap === 'normal' ? '\u00A0' : '\u202F';
    const groupSpace =
      group === 'em'     ? '\u2003' :
      group === 'en'     ? '\u2002' :
      group === 'figure' ? '\u2007' :
      group === 'wide'   ? '\u00A0\u00A0' :
      group === 'normal' ? '\u00A0' : '\u202F'; // défaut: 'narrow' (fine)

    // éviter le symbole étroit "FG" pour GNF
    let disp = display;
    if (cur === 'GNF' && disp === 'narrow') disp = 'none';

    // helper: appliquer notre séparateur de milliers
    const reGroup = /(\d)[\u00A0\u202F\u2007\u2002\u2003\s](?=\d)/g;
    const applyGroup = (s: string) => s.replace(reGroup, `$1${groupSpace}`);

    if (disp === 'none') {
      const body = new Intl.NumberFormat(locale, {
        style: 'decimal',
        minimumFractionDigits: minFD,
        maximumFractionDigits: maxFD,
        notation: compact ? 'compact' : 'standard',
        compactDisplay: 'short',
        useGrouping: true
      }).format(num);
      const sym = cur === 'EUR' ? '€' : cur; // GNF
      return `${applyGroup(body)}${gapSpace}${sym}`;
    }

    const currencyDisplay =
      disp === 'narrow' ? 'narrowSymbol' :
      disp === 'symbol' ? 'symbol' :
      disp === 'code'   ? 'code'   : 'symbol';

    const out = new Intl.NumberFormat(locale, {
      style: 'currency',
      currency: cur,
      currencyDisplay,
      minimumFractionDigits: minFD,
      maximumFractionDigits: maxFD,
      notation: compact ? 'compact' : 'standard',
      compactDisplay: 'short',
      useGrouping: true
    }).format(num);

    return applyGroup(out);
  }
}