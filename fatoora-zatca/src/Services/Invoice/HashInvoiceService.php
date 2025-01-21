<?php

namespace Bl\FatooraZatca\Services\Invoice;

use Bl\FatooraZatca\Actions\GetXmlFileAction;
use Bl\FatooraZatca\Classes\InvoiceType;

class HashInvoiceService
{
    /**
     * the document type.
     *
     * @var string
     */
    protected $documentType;

    /**
     * the xml invoice content.
     *
     * @var string
     */
    protected $invoiceXml;

    /**
     * the seller data.
     *
     * @var object
     */
    protected $seller;

    /**
     * the invoice data.
     *
     * @var object
     */
    protected $invoice;

    /**
     * the client data.
     *
     * @var object|null
     */
    protected $client;

    /**
     * __construct
     *
     * @param  object       $seller
     * @param  object       $invoice
     * @param  object|null  $client
     * @return void
     */
    public function __construct(object $seller, object $invoice, ?object $client)
    {
        $this->seller   = $seller;

        $this->invoice  = $invoice;

        $this->client   = $client;
    }

    /**
     * generate the hash invoice.
     *
     * @param  string $document_type
     * @return string
     */
    public function generate(string $document_type): string
    {
        $this->documentType = $document_type;

        $this->invoiceXml = GetXmlFileAction::handle('xml_to_hash');

        $this->invoiceXml = str_replace("\r", "", $this->invoiceXml);

        $this->xmlGenerator();

        // Eliminate Additional Signed Tags
        $invoice = str_replace('SET_XML_ENCODING', '', $this->invoiceXml);

        $invoice = str_replace('SET_UBL_EXTENSIONS_FOR_SIGNED', "    ", $invoice);

        $invoice = str_replace('SET_QR_AND_SIGNATURE_FOR_SIGNED', "    \n    ", $invoice);

        $invoiceHash = hash('sha256', $invoice, true);

        return base64_encode($invoiceHash);
    }

    /**
     * get the invoice xml content.
     *
     * @return string
     */
    public function getInvoiceXmlContent(): string
    {
        return $this->invoiceXml;
    }

    /**
     * generate xml of hashed invoice.
     *
     * @return void
     */
    protected function xmlGenerator(): void
    {
        $this->setInvoiceDetails();

        $this->setInvoiceBillingReferenceIfExists();

        $this->setPreviousInvoiceHash();

        $this->setAccountingSupplierParty();

        $this->setAccountingCustomerParty();

        $this->setDeliveryDate();

        $this->setInvoicePaymentMeans();

        (new XmlInvoiceItemsService($this->invoice))->generate($this->invoiceXml);

        $this->invoiceXml = str_replace('SET_CURRENCY', $this->invoice->currency, $this->invoiceXml);
    }

    /**
     * assign xml data to the invoice content.
     *
     * @param  string $tag
     * @param  mixed $value
     * @return void
     */
    protected function setXmlInvoiceItem(string $tag, $value): void
    {
        $this->invoiceXml = str_replace($tag, $value, $this->invoiceXml);
    }

    /**
     * set invoice details xml data.
     * @return void
     */
    protected function setInvoiceDetails(): void
    {
        $this->setXmlInvoiceItem('SET_INVOICE_SERIAL_NUMBER', htmlspecialchars($this->invoice->invoice_number, ENT_XML1, 'UTF-8'));
        $this->setXmlInvoiceItem('SET_TERMINAL_UUID', htmlspecialchars($this->invoice->invoice_uuid, ENT_XML1, 'UTF-8'));
        $this->setXmlInvoiceItem('SET_ISSUE_DATE', $this->invoice->invoice_date);
        $this->setXmlInvoiceItem('SET_ISSUE_TIME', $this->invoice->invoice_time . 'Z');
        $this->setXmlInvoiceItem('SET_INVOICE_TYPE', $this->invoice->invoice_type);
        $this->setXmlInvoiceItem('SET_DOCUMENT', $this->documentType);
        $this->setXmlInvoiceItem('SET_INVOICE_COUNTER_NUMBER', $this->invoice->id);
    }

    /**
     * set invoice billing reference xml data.
     *
     * @return void
     */
    protected function setInvoiceBillingReferenceIfExists(): void
    {
        $billingReferenceContent = '';

        $billingId = $this->invoice->invoice_billing_id ?? null;

        if($billingId) {

            $billingReferenceContent = GetXmlFileAction::handle('xml_billing_reference');

            $billingReferenceContent = str_replace("SET_INVOICE_NUMBER", $billingId, $billingReferenceContent);

            $this->setXmlInvoiceItem('SET_BILLING_REFERENCE', $billingReferenceContent);

        }

        $this->setXmlInvoiceItem('SET_BILLING_REFERENCE', $billingReferenceContent);
    }

    /**
     * set previous invoice hash xml data.
     *
     * @return void
     */
    protected function setPreviousInvoiceHash(): void
    {
        $previousHash = $this->invoice->previous_hash;

        if(! $previousHash) {

            $previousHash = base64_encode(hash('sha256', 0,true));

        }

        $this->setXmlInvoiceItem('SET_PREVIOUS_INVOICE_HASH', $previousHash);
    }

    /**
     * set accounting supplier party xml data.
     *
     * @return void
     */
    protected function setAccountingSupplierParty(): void
    {
        $this->setXmlInvoiceItem('SET_COMMERCIAL_REGISTRATION_NUMBER', htmlspecialchars($this->seller->registration_number, ENT_XML1, 'UTF-8'));
        $this->setXmlInvoiceItem('SET_STREET_NAME', htmlspecialchars($this->seller->street_name, ENT_XML1, 'UTF-8'));
        $this->setXmlInvoiceItem('SET_BUILDING_NUMBER', htmlspecialchars($this->seller->building_number, ENT_XML1, 'UTF-8'));
        $this->setXmlInvoiceItem('SET_PLOT_IDENTIFICATION', htmlspecialchars($this->seller->plot_identification, ENT_XML1, 'UTF-8'));
        $this->setXmlInvoiceItem('SET_CITY_SUBDIVISION', htmlspecialchars($this->seller->city_sub_division, ENT_XML1, 'UTF-8'));
        $this->setXmlInvoiceItem('SET_CITY', htmlspecialchars($this->seller->city, ENT_XML1, 'UTF-8'));
        $this->setXmlInvoiceItem('SET_POSTAL_NUMBER', htmlspecialchars($this->seller->postal_number, ENT_XML1, 'UTF-8'));
        $this->setXmlInvoiceItem('SET_SUPPLIER_COUNTRY', htmlspecialchars($this->seller->country, ENT_XML1, 'UTF-8'));
        $this->setXmlInvoiceItem('SET_VAT_NUMBER', htmlspecialchars($this->seller->tax_number, ENT_XML1, 'UTF-8'));
        $this->setXmlInvoiceItem('SET_VAT_NAME', htmlspecialchars($this->seller->registration_name, ENT_XML1, 'UTF-8'));
    }

    /**
     * set accounting customer party xml data.
     *
     * @return void
     */
    protected function setAccountingCustomerParty(): void
    {
        $clientContent = '';

        if($this->client) {

            $clientContent = GetXmlFileAction::handle('xml_client');

            $clientContent = str_replace('SET_CLIENT_STREET_NAME', htmlspecialchars($this->client->street_name, ENT_XML1, 'UTF-8'), $clientContent);
            $clientContent = str_replace('SET_CLIENT_BUILDING_NUMBER', htmlspecialchars($this->client->building_number, ENT_XML1, 'UTF-8'), $clientContent);
            $clientContent = str_replace('SET_CLIENT_PLOT_IDENTIFICATION', htmlspecialchars($this->client->plot_identification, ENT_XML1, 'UTF-8'), $clientContent);
            $clientContent = str_replace('SET_CLIENT_SUB_DIVISION_NAME', htmlspecialchars($this->client->city_subdivision_name, ENT_XML1, 'UTF-8'), $clientContent);
            $clientContent = str_replace('SET_CLIENT_CITY_NAME', htmlspecialchars($this->client->city, ENT_XML1, 'UTF-8'), $clientContent);
            $clientContent = str_replace('SET_CLIENT_COUNTRY_CODE', htmlspecialchars($this->client->country, ENT_XML1, 'UTF-8'), $clientContent);
            $clientContent = str_replace('SET_CLIENT_POSTAL_ZONE', htmlspecialchars($this->client->postal_number, ENT_XML1, 'UTF-8'), $clientContent);
            $clientContent = str_replace('SET_CLIENT_REGISTRATION_NAME', htmlspecialchars($this->client->registration_name, ENT_XML1, 'UTF-8'), $clientContent);

            // Set tax number if exists.
            $clientPartyTaxSchema = '';
            if(! empty($this->client->tax_number)) {
                $clientPartyTaxSchema = GetXmlFileAction::handle('xml_client_party_tax_schema');
                $clientPartyTaxSchema = str_replace('SET_CLIENT_VAT_NUMBER', $this->client->tax_number, $clientPartyTaxSchema);
            }
            $clientContent = str_replace('SET_CLIENT_PARTY_TAX_SCHEMA', $clientPartyTaxSchema, $clientContent);

            // Set national id if exists.
            $clientPartyIdentification = '';
            if(! empty($this->client->national_id)) {
                $clientPartyIdentification = GetXmlFileAction::handle('xml_client_party_identification');
                $clientPartyIdentification = str_replace('SET_CLIENT_NATIONAL_ID', $this->client->national_id, $clientPartyIdentification);
            }
            $clientContent = str_replace('SET_CLIENT_PARTY_IDENTIFICATION', $clientPartyIdentification, $clientContent);
        }

        $this->setXmlInvoiceItem('SET_CLIENT', $clientContent);
    }

    protected function setDeliveryDate(): void
    {
        $this->setXmlInvoiceItem('SET_DELIVERY_DATE', $this->invoice->delivery_date);
    }

    /**
     * set invoice payment type.
     * set invoice note.
     * set payment note.
     *
     * @return void
     */
    protected function setInvoicePaymentMeans(): void
    {
        $paymentTypeContent = GetXmlFileAction::handle('xml_payment_means');

        $paymentTypeContent = str_replace('SET_INVOICE_PAYMENT_TYPE', $this->invoice->payment_type, $paymentTypeContent);

        $invoiceNoteContent = '';

        $invoiceType = (int) $this->invoice->invoice_type;

        if(in_array($invoiceType, [InvoiceType::CREDIT_NOTE, InvoiceType::DEBIT_NOTE])) {

            $invoiceNoteContent = GetXmlFileAction::handle('xml_invoice_note');

            $reason = empty($this->invoice->invoice_note) 
            ? $this->getDefaultReason($invoiceType)
            : htmlspecialchars($this->invoice->invoice_note, ENT_XML1, 'UTF-8');

            $invoiceNoteContent = str_replace('SET_INVOICE_NOTE', $reason, $invoiceNoteContent);

        }

        $paymentNoteContent = '';

        if($this->invoice->payment_note) {

            $paymentNoteContent = GetXmlFileAction::handle('xml_payment_note');

            $paymentNoteContent = str_replace('SET_PAYMENT_NOTE', htmlspecialchars($this->invoice->payment_note, ENT_XML1, 'UTF-8'), $paymentNoteContent);

        }

        $paymentTypeContent = str_replace('SET_INVOICE_NOTE', $invoiceNoteContent, $paymentTypeContent);

        $paymentTypeContent = str_replace('SET_PAYMENT_NOTE', $paymentNoteContent, $paymentTypeContent);

        $this->setXmlInvoiceItem('SET_PAYMENT_TYPE', $paymentTypeContent);
    }

    /**
     * get default reason of [refund|credit]
     *
     * @param  int $invoiceType
     * @return string
     */
    protected function getDefaultReason(int $invoiceType): string
    {
        if($invoiceType === InvoiceType::CREDIT_NOTE) {
            return 'Goods or services refund.';
        }
        else if ($invoiceType === InvoiceType::DEBIT_NOTE) {
            return 'Adjustment due to increased quantity of goods or services.';
        }
        else return '';
    }
}
