import { Role } from "./Role";

 
 
 export class Contact {
    id?: number;
    reference?:string;
    nom_complet: string;
    nom:string;
    prenom: string;
    phone: string;
    email:string;
    date_naissance?:string;
    password:string;
    password_confirmation:string;
    role_id?:number;
    roles?: Role;
    agence_id?:number;
    statut: string;
    role?: any;
 
    constructor()
    {
        this.role ="";
        this.nom_complet = "";
        this.nom="";
        this.prenom = "";
         this.date_naissance="1999-01-01";
        this.password="";
        this.password_confirmation="";
        this.phone = "";
        this.email = "";
        this.statut="attente";
         this.roles= new Role();
    }
}  