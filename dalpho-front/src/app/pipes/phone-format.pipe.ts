import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'phoneFormat',
  standalone: true
})
export class PhoneFormatPipe implements PipeTransform {

  transform(value: string | null | undefined, country: 'GN' | 'FR' | 'INT' = 'GN'): string {
    if (!value) return '';

    // Enlève espaces, tirets, etc.
    let cleaned = value.replace(/\D/g, '');

    switch (country) {
      case 'GN':
        // Format Guinée : +224 XX XX XX XX
        if (cleaned.startsWith('224')) {
          cleaned = '+' + cleaned;
        } else if (cleaned.length === 9) {
          cleaned = '+224' + cleaned;
        }
        return cleaned.replace(/(\+224)(\d{2})(\d{2})(\d{2})(\d{2})/, '$1 $2 $3 $4 $5');

      case 'FR':
        // Format France : 0X XX XX XX XX
        if (cleaned.startsWith('33')) {
          cleaned = '0' + cleaned.substring(2);
        }
        return cleaned.replace(/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/, '$1 $2 $3 $4 $5');

      case 'INT':
        // Format international générique : +XXX XX XX XX...
        if (!cleaned.startsWith('+')) {
          cleaned = '+' + cleaned;
        }
        return cleaned.replace(/(\+\d{1,3})(\d{2})(\d{2})(\d{2})(\d{2})/, '$1 $2 $3 $4 $5');

      default:
        return value;
    }
  }
}
