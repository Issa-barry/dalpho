import { Component } from '@angular/core';

@Component({
    standalone: true,
    selector: 'app-footer',
    template: `<div class="layout-footer">
        Dalpho &copy; 2024 Created by
        <a href="https://felloconsulting.fr" target="_blank" rel="noopener noreferrer" class="text-primary font-bold hover:underline">Fello-Consulting</a>
    </div>`
})
export class AppFooter {}
