export class Agent {

  id?: number;
  name: string = "";
  email: string = "";
  email_verified_at?: string | null;
  created_at?: string;
  updated_at?: string;

  constructor(data?: Partial<Agent>) {
    Object.assign(this, data);
  }
}
