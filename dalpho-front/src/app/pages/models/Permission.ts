export class Permission {
    id?: number; 
    name: string;
    model_type:string;
    // guard_name:string;
     
    constructor()
    {
        this.name = ""; 
        this.model_type="";
        // this.guard_name="web";
    }
}