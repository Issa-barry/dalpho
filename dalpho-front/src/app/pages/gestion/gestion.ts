import { Component, OnInit, signal, ViewChild } from '@angular/core';
import { ConfirmationService, MessageService } from 'primeng/api';
import { Table, TableModule } from 'primeng/table';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ButtonModule } from 'primeng/button';
import { RippleModule } from 'primeng/ripple';
import { ToastModule } from 'primeng/toast';
import { ToolbarModule } from 'primeng/toolbar';
import { RatingModule } from 'primeng/rating';
import { InputTextModule } from 'primeng/inputtext';
import { TextareaModule } from 'primeng/textarea';
import { SelectModule } from 'primeng/select';
import { RadioButtonModule } from 'primeng/radiobutton';
import { InputNumberModule } from 'primeng/inputnumber';
import { DialogModule } from 'primeng/dialog';
import { TagModule } from 'primeng/tag';
import { InputIconModule } from 'primeng/inputicon';
import { IconFieldModule } from 'primeng/iconfield';
import { ConfirmDialogModule } from 'primeng/confirmdialog';
import { Product, ProductService } from '../service/product.service';
import { ExchangeRate } from '../models/ExchangeRate';
import { ExchangeRateService } from '../service/rate/echange-rate';

interface Column {
    field: string;
    header: string;
    customExportHeader?: string;
}

interface ExportColumn {
    title: string;
    dataKey: string;
}

interface CurrencyOption {
    label: string;
    value: number;
    code: string;
    symbol: string;
}

@Component({
  selector: 'app-gestion',
  standalone: true,
  templateUrl: './gestion.html',
  styleUrl: './gestion.scss',
  providers: [MessageService, ProductService, ConfirmationService],
   imports: [
        CommonModule,
        TableModule,
        FormsModule,
        ButtonModule,
        RippleModule,
        ToastModule,
        ToolbarModule,
        RatingModule,
        InputTextModule,
        TextareaModule,
        SelectModule,
        RadioButtonModule,
        InputNumberModule,
        DialogModule,
        TagModule,
        InputIconModule,
        IconFieldModule,
        ConfirmDialogModule,
        ToastModule,
    ],
})

export class Gestion implements OnInit {
    rateDialog: boolean = false;

    products = signal<Product[]>([]);

    product!: Product;

    selectedProducts!: Product[] | null;

    submitted: boolean = false;

    statuses!: any[];

    @ViewChild('dt') dt!: Table;

    exportColumns!: ExportColumn[];

    cols!: Column[];

    // IBA
    taux: ExchangeRate[] = [];
    rate: ExchangeRate = new ExchangeRate();
    loading = false;

    // Options de devises dynamiques
    currencyOptions: CurrencyOption[] = [];
    
    // Contrôle de l'état d'édition
    isEditMode: boolean = false;

    constructor(
        private productService: ProductService,
        private messageService: MessageService,
        private confirmationService: ConfirmationService,
        private exchangeRateService: ExchangeRateService
    ) {}

    exportCSV() {
        this.dt.exportCSV();
    }

    ngOnInit() {
        this.loadDemoData();
        this.loadRates();
    }

    
    private loadRates(): void {
        this.loading = true;
        this.exchangeRateService.getCurrentRates().subscribe({
            next: (res) => {
                const data = res.data ?? [];
                this.taux = data.map((item: any) => new ExchangeRate(item));
                
                // Générer les options de devises à partir des taux chargés
                this.generateCurrencyOptions();
                
                console.log("taux", this.taux);
                console.log("currencyOptions", this.currencyOptions);
                this.loading = false;
            },
            error: (err) => {
                this.loading = false;
            },
            complete: () => {
                this.loading = false;
            }
        });
    }

    /**
     * Génère les options de devises à partir des taux disponibles
     * Évite les doublons en utilisant un Map
     */
    private generateCurrencyOptions(): void {
        const currencyMap = new Map<number, CurrencyOption>();

        this.taux.forEach(rate => {
            // Ajouter la devise source (from_currency)
            if (rate.from_currency && rate.from_currency.id) {
                currencyMap.set(rate.from_currency.id, {
                    label: `${rate.from_currency.name} (${rate.from_currency.symbol})`,
                    value: rate.from_currency.id,
                    code: rate.from_currency.code,
                    symbol: rate.from_currency.symbol
                });
            }

            // Ajouter la devise cible (to_currency)
            if (rate.to_currency && rate.to_currency.id) {
                currencyMap.set(rate.to_currency.id, {
                    label: `${rate.to_currency.name} (${rate.to_currency.symbol})`,
                    value: rate.to_currency.id,
                    code: rate.to_currency.code,
                    symbol: rate.to_currency.symbol
                });
            }
        });

        // Convertir le Map en tableau et trier par nom
        this.currencyOptions = Array.from(currencyMap.values())
            .sort((a, b) => a.label.localeCompare(b.label));
    }

    loadDemoData() {
        this.productService.getProducts().then((data) => {
            this.products.set(data);
        });

        this.statuses = [
            { label: 'ACTIVE', value: 'instock' },
            { label: 'LOWSTOCK', value: 'lowstock' },
            { label: 'OUTOFSTOCK', value: 'outofstock' }
        ];

        this.cols = [
            { field: 'devise', header: 'Code', customExportHeader: 'Product Code' },
            { field: 'name', header: 'Name' },
            { field: 'image', header: 'Image' },
            { field: 'price', header: 'Price' },
            { field: 'category', header: 'Category' }
        ];

        this.exportColumns = this.cols.map((col) => ({ title: col.header, dataKey: col.field }));
    }

    onGlobalFilter(table: Table, event: Event) {
        table.filterGlobal((event.target as HTMLInputElement).value, 'contains');
    }

    openNew() {
        this.rate = new ExchangeRate();
        this.product = {};
        this.submitted = false;
        this.isEditMode = false; // Mode création
        this.rateDialog = true;
    }

    editRate(rate: ExchangeRate) {
        // Créer une copie profonde pour éviter les modifications directes
        this.rate = new ExchangeRate({
            ...rate,
            from_currency_id: rate.from_currency?.id,
            to_currency_id: rate.to_currency?.id
        });
        this.isEditMode = true; // Mode édition
        this.rateDialog = true;
    }

    deleteSelectedProducts() {
        this.confirmationService.confirm({
            message: 'Are you sure you want to delete the selected products?',
            header: 'Confirm',
            icon: 'pi pi-exclamation-triangle',
            accept: () => {
                this.products.set(this.products().filter((val) => !this.selectedProducts?.includes(val)));
                this.selectedProducts = null;
                this.messageService.add({
                    severity: 'success',
                    summary: 'Successful',
                    detail: 'Products Deleted',
                    life: 3000
                });
            }
        });
    }

    hideDialog() {
        this.rateDialog = false;
        this.submitted = false;
        this.isEditMode = false; // Réinitialiser le mode
    }

    deleteProduct(product: Product) {
        this.confirmationService.confirm({
            message: 'Are you sure you want to delete ' + product.name + '?',
            header: 'Confirm',
            icon: 'pi pi-exclamation-triangle',
            accept: () => {
                this.products.set(this.products().filter((val) => val.id !== product.id));
                this.product = {};
                this.messageService.add({
                    severity: 'success',
                    summary: 'Successful',
                    detail: 'Product Deleted',
                    life: 3000
                });
            }
        });
    }

    findIndexById(id: string): number {
        let index = -1;
        for (let i = 0; i < this.products().length; i++) {
            if (this.products()[i].id === id) {
                index = i;
                break;
            }
        }

        return index;
    }

    createId(): string {
        let id = '';
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        for (var i = 0; i < 5; i++) {
            id += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return id;
    }

    getSeverity(status: string) {
        switch (status) {
            case 'INSTOCK':
                return 'success';
            case 'LOWSTOCK':
                return 'warn';
            case 'OUTOFSTOCK':
                return 'danger';
            default:
                return 'info';
        }
    }

    saveProduct() {
        this.submitted = true;

        console.log(this.rate.id, this.rate.rate);

        // Vérifier que les champs requis sont remplis
        if (!this.rate.id || !this.rate.rate) {
            this.messageService.add({
                severity: 'warn',
                summary: 'Attention',
                detail: 'Veuillez remplir tous les champs requis',
                life: 3000
            });
            return;
        }

        this.exchangeRateService.updateRate(this.rate.id, this.rate.rate).subscribe({
            next: (res) => {
                this.messageService.add({   
                    severity: 'success',
                    summary: '👉  Succès',
                    detail: 'Taux mis à jour',
                    life: 3000
                });
                this.hideDialog();
                this.loadRates();
            },
            error: (err) => {
                this.loading = false;
                this.messageService.add({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: 'Échec de la mise à jour du taux',
                    life: 3000
                });
            }
        });
    }
}