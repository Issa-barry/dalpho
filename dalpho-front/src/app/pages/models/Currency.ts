export class Currency {

  id?: number;
  code: string = "";
  name: string = "";
  symbol: string = "";
  is_active: boolean = true;
  is_base_currency: boolean = false;
  created_at?: string;
  updated_at?: string;

  constructor(data?: Partial<Currency>) {
    Object.assign(this, data);
  }
}
