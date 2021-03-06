<?php

namespace Rutatiina\POS\Services;

use Illuminate\Support\Facades\Validator;
use Rutatiina\Contact\Models\Contact;
use Rutatiina\POS\Models\POSOrderSetting;

class POSOrderValidateService
{
    public static $errors = [];

    public static function run($requestInstance)
    {
        //$request = request(); //used for the flash when validation fails
        $user = auth()->user();


        // >> data validation >>------------------------------------------------------------

        //validate the data
        $customMessages = [
            //'total.in' => "Item total is invalid:\nItem total = item rate x item quantity",

            'items.*.taxes.*.code.required' => "Tax code is required",
            'items.*.taxes.*.total.required' => "Tax total is required",
            //'items.*.taxes.*.exclusive.required' => "Tax exclusive amount is required",
        ];

        $rules = [
            'contact_id' => 'numeric|nullable',
            'date' => 'required|date',
            'currency' => 'required',

            'items' => 'required|array',
            'items.*.name' => 'required_without:type_id',
            'items.*.rate' => 'required|numeric',
            'items.*.quantity' => 'required|numeric|gt:0',
            //'items.*.total' => 'required|numeric|in:' . $itemTotal, //todo custom validator to check this
            'items.*.units' => 'numeric|nullable',
            'items.*.taxes' => 'array|nullable',

            'items.*.taxes.*.code' => 'required',
            'items.*.taxes.*.total' => 'required|numeric',
            //'items.*.taxes.*.exclusive' => 'required|numeric',
        ];

        $validator = Validator::make($requestInstance->all(), $rules, $customMessages);

        if ($validator->fails())
        {
            self::$errors = $validator->errors()->all();
            return false;
        }

        // << data validation <<------------------------------------------------------------

        $settings = POSOrderSetting::has('financial_account_to_debit')
            ->has('financial_account_to_credit')
            ->with(['financial_account_to_debit', 'financial_account_to_credit'])
            ->firstOrFail();
        //Log::info($this->settings);


        $contact = Contact::find($requestInstance->contact_id);

        $data['id'] = $requestInstance->input('id', null); //for updating the id will always be posted
        $data['user_id'] = $user->id;
        $data['tenant_id'] = $user->tenant->id;
        $data['created_by'] = $user->name;
        $data['app'] = 'web';
        $data['document_name'] = $settings->document_name;
        $data['number'] = $requestInstance->input('number');
        $data['date'] = $requestInstance->input('date');
        $data['debit_financial_account_code'] = $settings->financial_account_to_debit->code;
        $data['credit_financial_account_code'] = $settings->financial_account_to_credit->code;
        $data['contact_id'] = $requestInstance->contact_id;
        $data['contact_name'] = optional($contact)->name;
        $data['contact_address'] = trim(optional($contact)->shipping_address_street1 . ' ' . optional($contact)->shipping_address_street2);
        $data['currency'] =  $requestInstance->input('currency');
        $data['branch_id'] = $requestInstance->input('branch_id', null);
        $data['store_id'] = $requestInstance->input('store_id', null);
        $data['contact_notes'] = $requestInstance->input('contact_notes', null);
        $data['payment_mode'] = $requestInstance->input('payment_mode', null);
        $data['status'] = $requestInstance->input('status', null);
        $data['cash_tendered'] = $requestInstance->input('cash_tendered', null);
        $data['cash_change'] = $requestInstance->input('cash_change', null);
        $data['balances_where_updated'] = 0;
        $data['total'] = $requestInstance->input('total', null);;
        $data['taxable_amount'] = $requestInstance->input('taxable_amount', null);;


        //set the transaction total to zero
        $txnTotal = 0;
        $taxableAmount = 0;

        //Formulate the DB ready items array
        $data['items'] = [];
        foreach ($requestInstance->items as $key => $item)
        {
            $itemTaxes = $requestInstance->input('items.'.$key.'.taxes', []);

            $txnTotal           += ($item['rate']*$item['quantity']);
            $taxableAmount      += ($item['rate']*$item['quantity']);
            $itemTaxableAmount   = ($item['rate']*$item['quantity']); //calculate the item taxable amount

            foreach ($itemTaxes as $itemTax)
            {
                $txnTotal           += $itemTax['exclusive'];
                $taxableAmount      -= $itemTax['inclusive'];
                $itemTaxableAmount  -= $itemTax['inclusive']; //calculate the item taxable amount more by removing the inclusive amount
            }

            $data['items'][] = [
                'tenant_id' => $data['tenant_id'],
                'created_by' => $data['created_by'],
                'item_id' => $item['item_id'],
                'name' => $item['name'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'total' => $item['total'],
                'taxable_amount' => $itemTaxableAmount,
                'units' => $requestInstance->input('items.'.$key.'.units', null),
                'batch' => $requestInstance->input('items.'.$key.'.batch', null),
                'expiry' => $requestInstance->input('items.'.$key.'.expiry', null),
                'taxes' => $itemTaxes,
            ];
        }

        $data['taxable_amount'] = (is_null($data['taxable_amount'])) ? $taxableAmount : $data['taxable_amount'];
        $data['total'] = (is_null($data['total'])) ? $txnTotal : $data['total'];


        //DR ledger
        $data['ledgers'][] = [
            'financial_account_code' => $settings->financial_account_to_debit->code,
            'effect' => 'debit',
            'total' => $data['total'],
            'contact_id' => $data['contact_id']
        ];

        //CR ledger
        $data['ledgers'][] = [
            'financial_account_code' => $settings->financial_account_to_credit->code,
            'effect' => 'credit',
            'total' => $data['total'],
            'contact_id' => $data['contact_id']
        ];

        //print_r($data); exit;

        //Now add the default values to items and ledgers

        foreach ($data['ledgers'] as &$ledger)
        {
            $ledger['tenant_id'] = $data['tenant_id'];
            $ledger['date'] = date('Y-m-d', strtotime($data['date']));
            $ledger['base_currency'] = $data['currency'];
            $ledger['quote_currency'] = $data['currency'];
            $ledger['exchange_rate'] = 1;
        }
        unset($ledger);

        //Return the array of txns
        //print_r($data); exit;

        return $data;

    }

}
