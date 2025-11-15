import { Permission } from "./Permission";

 
export class Role {
  id?: number;
  name: string;
  permissions: Permission[];
  guard_name: string;
  created_at?: string;
  updated_at?: string;
  role: any;

  constructor() {
    this.name = "";
    this.permissions = [];
    this.guard_name = "web"; // Valeur par défaut
    this.role = {};
  }
}
