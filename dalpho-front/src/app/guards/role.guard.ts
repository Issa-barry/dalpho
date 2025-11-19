import { AuthService } from "@/pages/service/auth/auth.service";
import { Injectable } from "@angular/core";
import { ActivatedRouteSnapshot, CanActivate, Router } from "@angular/router";

@Injectable({providedIn:'root'})
export class RoleGuard implements CanActivate {

  constructor(private auth: AuthService, private router: Router){}

  canActivate(route: ActivatedRouteSnapshot): boolean {
    const expectedRoles = route.data['roles'];
    const userRole = this.auth.getRole();

    if (expectedRoles.includes(userRole)) {
      return true;
    }

    this.router.navigate(['/dashboard/home']);
    return false;
  }
}
