<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\Imposto;
use Illuminate\Support\Facades\DB;
use App\Models\Configuracao;

class SaftService
{
    protected $dom;
    protected $auditFile;

    public function generate($mes, $ano)
    {
        $this->dom = new \DOMDocument('1.0', 'Windows-1252');
        $this->dom->formatOutput = true;

        $this->auditFile = $this->dom->createElement('AuditFile');
        $this->auditFile->setAttribute('xmlns', 'urn:OECD:StandardAuditFile-Tax:AO_1.01_01');
        $this->dom->appendChild($this->auditFile);

        $this->buildHeader($mes, $ano);
        $this->buildMasterFiles();
        $this->buildSourceDocuments($mes, $ano);

        return $this->dom->saveXML();
    }

    protected function buildHeader($mes, $ano)
    {
        $header = $this->dom->createElement('Header');
        $this->auditFile->appendChild($header);

        $configs = Configuracao::pluck('valor', 'chave')->toArray();

        $this->appendElement($header, 'AuditFileVersion', '1.01_01');
        $this->appendElement($header, 'CompanyID', $configs['nif_porto'] ?? '999999999');
        $this->appendElement($header, 'TaxRegistrationNumber', $configs['nif_porto'] ?? '999999999');
        $this->appendElement($header, 'TaxAccountingBasis', 'F');
        $this->appendElement($header, 'CompanyName', $configs['nome_porto'] ?? 'Empresa Desconhecida');
        
        $companyAddress = $this->dom->createElement('CompanyAddress');
        $this->appendElement($companyAddress, 'AddressDetail', $configs['endereco_porto'] ?? 'Luanda, Angola');
        $this->appendElement($companyAddress, 'City', 'Luanda');
        $this->appendElement($companyAddress, 'Country', 'AO');
        $header->appendChild($companyAddress);

        $this->appendElement($header, 'FiscalYear', $ano);
        
        $startDate = "{$ano}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate = date("Y-m-t", strtotime($startDate));

        $this->appendElement($header, 'StartDate', $startDate);
        $this->appendElement($header, 'EndDate', $endDate);
        $this->appendElement($header, 'CurrencyCode', 'AOA');
        $this->appendElement($header, 'DateCreated', date('Y-m-d'));
        $this->appendElement($header, 'TaxEntity', 'Global');
        $this->appendElement($header, 'ProductCompanyTaxID', '999999999'); // AGT Validator ID if any
        $this->appendElement($header, 'SoftwareCertificateNumber', '000/AGT/2026'); // Validated number
        $this->appendElement($header, 'ProductID', 'SIGP_AGT/SIGP');
        $this->appendElement($header, 'ProductVersion', '1.0');
    }

    protected function buildMasterFiles()
    {
        $masterFiles = $this->dom->createElement('MasterFiles');
        $this->auditFile->appendChild($masterFiles);

        // Clientes
        $faturas = Factura::with('concessionaria')->get();
        $clientesAdicionados = [];

        foreach ($faturas as $fatura) {
            $clienteId = $fatura->concessionaria_id ?? 'ConsumidorFinal';
            if (in_array($clienteId, $clientesAdicionados)) continue;

            $customer = $this->dom->createElement('Customer');
            $this->appendElement($customer, 'CustomerID', $clienteId);
            $this->appendElement($customer, 'AccountID', 'Desconhecido');
            $this->appendElement($customer, 'CustomerTaxID', $fatura->concessionaria->nif ?? '999999999');
            $this->appendElement($customer, 'CompanyName', $fatura->concessionaria->nome ?? 'Consumidor Final');

            $billingAddress = $this->dom->createElement('BillingAddress');
            $this->appendElement($billingAddress, 'AddressDetail', $fatura->concessionaria->endereco ?? 'Desconhecido');
            $this->appendElement($billingAddress, 'City', 'Luanda');
            $this->appendElement($billingAddress, 'Country', 'AO');
            $customer->appendChild($billingAddress);

            $this->appendElement($customer, 'SelfBillingIndicator', '0');
            $masterFiles->appendChild($customer);
            
            $clientesAdicionados[] = $clienteId;
        }

        // Tabela de Impostos (TaxTable)
        $taxTable = $this->dom->createElement('TaxTable');
        $impostos = Imposto::where('ativo', true)->get();
        foreach ($impostos as $imposto) {
            $taxTableEntry = $this->dom->createElement('TaxTableEntry');
            $this->appendElement($taxTableEntry, 'TaxType', $imposto->tipo);
            $this->appendElement($taxTableEntry, 'TaxCountryRegion', 'AO');
            $this->appendElement($taxTableEntry, 'TaxCode', $imposto->sigla);
            $this->appendElement($taxTableEntry, 'Description', $imposto->nome);
            $this->appendElement($taxTableEntry, 'TaxPercentage', number_format($imposto->taxa, 2, '.', ''));
            $taxTable->appendChild($taxTableEntry);
        }
        $masterFiles->appendChild($taxTable);
    }

    protected function buildSourceDocuments($mes, $ano)
    {
        $sourceDocuments = $this->dom->createElement('SourceDocuments');
        $this->auditFile->appendChild($sourceDocuments);

        $salesInvoices = $this->dom->createElement('SalesInvoices');
        $sourceDocuments->appendChild($salesInvoices);

        $startDate = "{$ano}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate = date("Y-m-t", strtotime($startDate));

        $faturas = Factura::with(['items.taxaIva', 'concessionaria'])
                          ->whereBetween('created_at', [$startDate . " 00:00:00", $endDate . " 23:59:59"])
                          ->whereIn('status', ['emitida', 'paga'])
                          ->orderBy('created_at', 'asc')
                          ->get();

        $numberOfEntries = $faturas->count();
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($faturas as $fatura) {
            $totalGross = $fatura->total_a_pagar > 0 ? $fatura->total_a_pagar : $fatura->valor_total;
            if ($fatura->tipo_documento === 'NC') {
                $totalDebit += abs($totalGross);
            } else {
                $totalCredit += abs($totalGross);
            }
        }

        $this->appendElement($salesInvoices, 'NumberOfEntries', $numberOfEntries);
        $this->appendElement($salesInvoices, 'TotalDebit', number_format($totalDebit, 2, '.', ''));
        $this->appendElement($salesInvoices, 'TotalCredit', number_format($totalCredit, 2, '.', ''));

        foreach ($faturas as $fatura) {
            $invoice = $this->dom->createElement('Invoice');
            $this->appendElement($invoice, 'InvoiceNo', $fatura->numero);
            
            $status = $this->dom->createElement('DocumentStatus');
            $this->appendElement($status, 'InvoiceStatus', 'N');
            $this->appendElement($status, 'InvoiceStatusDate', $fatura->created_at->format('Y-m-d\TH:i:s'));
            $this->appendElement($status, 'SourceID', 'admin');
            $this->appendElement($status, 'SourceBilling', 'P');
            $invoice->appendChild($status);

            $this->appendElement($invoice, 'Hash', $fatura->hash);
            $this->appendElement($invoice, 'HashControl', '1');
            $this->appendElement($invoice, 'Period', str_pad($mes, 2, '0', STR_PAD_LEFT));
            $this->appendElement($invoice, 'InvoiceDate', $fatura->created_at->format('Y-m-d'));
            $this->appendElement($invoice, 'InvoiceType', $fatura->tipo_documento);
            
            $especialRegimes = $this->dom->createElement('SpecialRegimes');
            $this->appendElement($especialRegimes, 'SelfBillingIndicator', '0');
            $this->appendElement($especialRegimes, 'CashVATSchemeIndicator', '0');
            $this->appendElement($especialRegimes, 'ThirdPartiesBillingIndicator', '0');
            $invoice->appendChild($especialRegimes);

            $this->appendElement($invoice, 'SourceID', 'admin');
            $this->appendElement($invoice, 'SystemEntryDate', $fatura->created_at->format('Y-m-d\TH:i:s'));
            $this->appendElement($invoice, 'CustomerID', $fatura->concessionaria_id ?? 'ConsumidorFinal');

            $lineNumber = 1;
            foreach ($fatura->items as $item) {
                $line = $this->dom->createElement('Line');
                $this->appendElement($line, 'LineNumber', $lineNumber++);
                $this->appendElement($line, 'ProductCode', 'SRV');
                $this->appendElement($line, 'ProductDescription', $item->descricao);
                $this->appendElement($line, 'Quantity', number_format(abs($item->quantidade), 2, '.', ''));
                $this->appendElement($line, 'UnitOfMeasure', 'UN');
                $this->appendElement($line, 'UnitPrice', number_format(abs($item->preco_unitario), 2, '.', ''));
                $this->appendElement($line, 'TaxPointDate', $fatura->created_at->format('Y-m-d'));
                $this->appendElement($line, 'Description', $item->descricao);
                
                $itemTotal = abs($item->subtotal);
                if ($fatura->tipo_documento === 'NC') {
                    $this->appendElement($line, 'DebitAmount', number_format($itemTotal, 2, '.', ''));
                } else {
                    $this->appendElement($line, 'CreditAmount', number_format($itemTotal, 2, '.', ''));
                }

                $tax = $this->dom->createElement('Tax');
                $taxType = $item->taxaIva ? $item->taxaIva->tipo : 'IVA';
                $taxCode = $item->taxaIva ? $item->taxaIva->sigla : 'NOR';
                $taxPercentage = $item->taxaIva ? $item->taxaIva->taxa : 14;

                $this->appendElement($tax, 'TaxType', $taxType);
                $this->appendElement($tax, 'TaxCountryRegion', 'AO');
                $this->appendElement($tax, 'TaxCode', $taxCode);
                $this->appendElement($tax, 'TaxPercentage', number_format($taxPercentage, 2, '.', ''));
                $line->appendChild($tax);

                if ($taxPercentage == 0 && $item->taxaIva && $item->taxaIva->motivo_isencao_codigo) {
                    $this->appendElement($line, 'TaxExemptionReason', $item->taxaIva->motivo_isencao_descricao);
                    $this->appendElement($line, 'TaxExemptionCode', $item->taxaIva->motivo_isencao_codigo);
                }

                $invoice->appendChild($line);
            }

            $docTotals = $this->dom->createElement('DocumentTotals');
            $this->appendElement($docTotals, 'TaxPayable', number_format(abs($fatura->total_iva), 2, '.', ''));
            $this->appendElement($docTotals, 'NetTotal', number_format(abs($fatura->total_s_iva > 0 ? $fatura->total_s_iva : $fatura->valor_total), 2, '.', ''));
            $this->appendElement($docTotals, 'GrossTotal', number_format(abs($fatura->total_a_pagar > 0 ? $fatura->total_a_pagar : $fatura->valor_total), 2, '.', ''));
            $invoice->appendChild($docTotals);

            $salesInvoices->appendChild($invoice);
        }
    }

    protected function appendElement($parent, $name, $value)
    {
        $element = $this->dom->createElement($name, htmlspecialchars((string) $value));
        $parent->appendChild($element);
        return $element;
    }
}
