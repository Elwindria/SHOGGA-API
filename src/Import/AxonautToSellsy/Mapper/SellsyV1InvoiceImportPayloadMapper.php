<?php

namespace App\Import\AxonautToSellsy\Mapper;

use App\Import\AxonautToSellsy\DTO\NormalizedInvoiceLineDto;
use App\Import\AxonautToSellsy\Resolver\AxonautInvoiceDiscountMappingResolver;
use App\Sellsy\Tax\SellsyTaxMappingResolver;
use App\Sellsy\Catalogue\SellsyCatalogueMappingResolver;
use App\Sellsy\PayMediums\SellsyPayMediumsMappingResolver;

final class SellsyV1InvoiceImportPayloadMapper
{
    public function __construct(
        private SellsyTaxMappingResolver $taxResolver,
        private AxonautInvoiceDiscountMappingResolver $AxonautInvoiceDiscountMappingResolver,
        private SellsyCatalogueMappingResolver $sellsyCatalogueMappingResolver,
        private SellsyPayMediumsMappingResolver $sellsyPayMediumsMappingResolver,
    ) {
    }

    /**
     * @param array<int, NormalizedInvoiceLineDto> $lines
     * @param int $thirdId
     * @return array<string, mixed>
     */
    public function map(array $lines, int $thirdId, int $staffId): array
    {
        if (count($lines) === 0) {
            throw new \RuntimeException('Aucune ligne à mapper.');
        }

        $first = $lines[0];

        return [
            'method' => 'Document.create',
            'params' => [
                'document' => $this->buildDocument($first, $thirdId, $staffId),
                'row' => $this->buildRows($lines),
            ],
        ];
    }

    private function buildDocument(
        NormalizedInvoiceLineDto $line,
        int $thirdId,
        int $staffId
    ): array {

        return [
            'doctype' => 'invoice',
            'thirdid' => (string) $thirdId,
            'ident' => (string) $line->invoiceNumber,
            'displayedDate' => $this->toTimestamp($line->invoiceDate),
            'subject' => 'Import historique Axonaut - '.$line->invoiceNumber,
            'notes' => $this->buildNotes($line),
            'globalDiscount' => $this->AxonautInvoiceDiscountMappingResolver->getGlobalDiscountByInvoiceNumber($line->invoiceNumber),
            'globalDiscountUnit' => 'amount',
            'docspeakerStaffId' => $staffId,
            'payMediums' => [
                $this->sellsyPayMediumsMappingResolver->getPayMediumsIdByName($line->paymentMethod),
            ],
            'enable_draft_number' => 1,
        ];
    }

    /**
     * @param array<int, NormalizedInvoiceLineDto> $lines
     * @return array<string, array<string, string>>
     */
    private function buildRows(array $lines): array
    {
        $rows = [];

        foreach ($lines as $index => $line) {
            $rows[(string) ($index + 1)] = $this->buildRow($line);
        }

        return $rows;
    }

    private function buildRow(NormalizedInvoiceLineDto $line): array
    {
        if ($line->lineLabel === "Frais de livraison – Participation aux coûts logistiques (commande < 500 € HT)") {
            //lignes de type frais de livraisons 
            $row = [
                'row_type' => 'once',
            ];
        } else {
            //lignes de type Produit
            $row = [
                'row_type' => 'item',
                'row_linkedid' => $this->sellsyCatalogueMappingResolver->getCatalogueIdByInvoiceLineName($line->lineLabel),
                'row_purchaseAmount' => $this->sellsyCatalogueMappingResolver->getPurchaseAmountByInvoiceLineName($line->lineLabel),
            ];
        }

        $row['row_name'] = $line->lineLabel;
        $row['row_notes'] = $line->lineLabel;
        $row['row_unitAmount'] = $this->format($line->lineUnitHt);
        $row['row_taxid'] = (string) $this->taxResolver->getTaxIdByRate($line->lineTaxRate);
        $row['row_qt'] = $this->format($line->lineQuantity);


        if ($line->lineDiscount !== 0) {
            $row['row_discount'] = $this->format($line->lineDiscount);
            $row['row_discountUnit'] = 'amount';
        }

        return $row;
    }

    private function format(float $value): string
    {
        //Il faut enlever les - des réduction d'axonaut car sellsy fait prix - (-reduction) et - - = +
        return number_format(abs($value), 3, '.', '');
    }

    private function buildNotes(NormalizedInvoiceLineDto $line): string
    {
        return sprintf(
            "Ancien numéro de facture Axonaut : %s\nDate de la facture : %s\nDate de paiement : %s\nMode de paiement : %s",
            $line->invoiceNumber,
            $line->invoiceDate,
            $line->paidDate,
            $line->paymentMethod
        );
    }

    private function toTimestamp(string $date): int
    {
        $dateTime = \DateTimeImmutable::createFromFormat('!d/m/Y', trim($date));

        if (!$dateTime) {
            throw new \RuntimeException('Date invalide : ' . $date);
        }

        return $dateTime->getTimestamp();
    }

    public function mapPayment(string $docId, array $lines): array
    {
        $first = $lines[0];

        $date = \DateTimeImmutable::createFromFormat(
            'd/m/Y',
            $first->paidDate
        );

        if (!$date) {
            throw new \RuntimeException(sprintf(
                'Date de paiement invalide : %s',
                $first->paidDate
            ));
        }

        return [
            'method' => 'Document.createPayment',
            'params' => [
                'payment' => [
                    'date' => $date->getTimestamp(),
                    'amount' => $this->calculateInvoiceTotalTtc($lines),
                    'medium' => $this->sellsyPayMediumsMappingResolver->getPayMediumsIdByName($first->paymentMethod),
                    'ident' => (string) $first->invoiceNumber,
                    'doctype' => 'invoice',
                    'docid' => (int) $docId,
                    'notes' => sprintf(
                        'Paiement import historique d axonaut à Sellsy => facture %s',
                        $first->invoiceNumber
                    ),
                    'email' => 'N',
                ],
            ],
        ];
    }

    private function calculateInvoiceTotalTtc(array $lines): string
    {
        $total = 0.0;

        foreach ($lines as $line) {
            $total += $line->invoiceTotalTtc;
        }

        return number_format($total, 2, '.', '');
    }
}