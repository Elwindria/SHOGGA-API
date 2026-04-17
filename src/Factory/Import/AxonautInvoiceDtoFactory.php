<?php

namespace App\Factory\Import;

use App\Dto\Import\AxonautInvoiceDto;

final class AxonautInvoiceDtoFactory
{
    /**
     * @param array<string, string|null> $row
     */
    public function createFromArray(array $row): AxonautInvoiceDto
    {
        return new AxonautInvoiceDto(
            companyCategories: $this->get($row, 'Catégories de société'),
            createdAt: $this->get($row, 'Date de création'),
            lastContactAt: $this->get($row, 'Date de dernier contact'),
            orderChannel: $this->get($row, 'Canal de commande'),
            billingPostalCode: $this->get($row, "Code postal de l'adresse de facturation du client"),
            deliveryPostalCode: $this->get($row, "Code postal de l'adresse de livraison du client"),
            customerThirdPartyCode: $this->get($row, 'Code tiers du client'),
            orderComments: $this->get($row, 'Commentaires de la commande'),
            invoiceDate: $this->get($row, 'Date de la facture'),
            expectedDeliveryDate: $this->get($row, 'Date de livraison prévue de la facture'),
            paymentDate: $this->get($row, 'Date de paiement de la facture'),
            dueDate: $this->get($row, "Date d'échéance"),
            currency: $this->get($row, 'Devise'),
            customerEmail: $this->get($row, 'Email du client'),
            frequency: $this->get($row, 'Fréquence'),
            invoiceId: $this->get($row, 'ID Facture'),
            companyId: $this->get($row, 'Id de la société'),
            contactName: $this->get($row, 'Interlocuteur'),
            amountExclTax: $this->get($row, 'Montant HT'),
            amountInclTax: $this->get($row, 'Montant TTC'),
            taxAmount: $this->get($row, 'Montant taxe'),
            discountAmount: $this->get($row, 'Montant de la remise'),
            paymentMethod: $this->get($row, 'Moyen de paiement'),
            orderName: $this->get($row, 'Nom de la commande'),
            customerName: $this->get($row, 'nom du client'),
            projectName: $this->get($row, 'Nom du projet (si existant)'),
            customerVatNumber: $this->get($row, 'Numéro de TVA intracommunautaire du client'),
            invoiceNumber: $this->get($row, 'Numéro de la facture'),
            projectNumber: $this->get($row, 'Numéro du projet (si existant)'),
            billingCountry: $this->get($row, "Pays de l'adresse de facturation du client"),
            deliveryCountry: $this->get($row, "Pays de l'adresse de livraison du client"),
            invoiceReference: $this->get($row, 'Référence de la facture'),
            paymentReferences: $this->get($row, 'Références des paiements'),
            billingTheme: $this->get($row, 'Thème de facturation'),
            invoiceTitle: $this->get($row, 'Titre de la facture'),
            invoiceType: $this->get($row, 'Type de la facture'),
            billingCity: $this->get($row, "Ville de l'adresse de facturation du client"),
            deliveryCity: $this->get($row, "Ville de l'adresse de livraison du client"),
        );
    }

    /**
     * @param array<string, string|null> $row
     */
    private function get(array $row, string $key): ?string
    {
        if (!array_key_exists($key, $row)) {
            return null;
        }

        $value = $row[$key];

        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}