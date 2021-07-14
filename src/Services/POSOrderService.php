<?php

namespace Rutatiina\POS\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rutatiina\POS\Models\POSOrder;
use Rutatiina\FinancialAccounting\Services\AccountBalanceUpdateService;
use Rutatiina\FinancialAccounting\Services\ContactBalanceUpdateService;
use Rutatiina\POS\Models\POSOrderSetting;
use Rutatiina\RetainerInvoice\Models\RetainerInvoiceSetting;
use Rutatiina\Tax\Models\Tax;

class POSOrderService
{
    public static $errors = [];

    public function __construct()
    {
        //
    }

    public static function nextNumber()
    {
        $count = POSOrder::count();
        $settings = POSOrderSetting::first();

        return $settings->number_prefix . (str_pad(($count + 1), $settings->minimum_number_length, "0", STR_PAD_LEFT)) . $settings->number_postfix;
    }

    public static function store($requestInstance)
    {
        $data = POSOrderValidateService::run($requestInstance);
        //print_r($data); exit;
        if ($data === false)
        {
            self::$errors = POSOrderValidateService::$errors;
            return false;
        }

        //start database transaction
        DB::connection('tenant')->beginTransaction();

        try
        {
            $Txn = new POSOrder;
            $Txn->tenant_id = $data['tenant_id'];
            $Txn->created_by = Auth::id();
            $Txn->document_name = $data['document_name'];
            $Txn->number = $data['number'];
            $Txn->date = $data['date'];
            $Txn->time = date("h:i:s");
            $Txn->debit_financial_account_code = $data['debit_financial_account_code'];
            $Txn->credit_financial_account_code = $data['credit_financial_account_code'];
            $Txn->contact_id = $data['contact_id'];
            $Txn->currency = $data['currency'];
            $Txn->taxable_amount = $data['taxable_amount'];
            $Txn->total = $data['total'];
            $Txn->cash_tendered = $data['cash_tendered'];
            $Txn->cash_change = $data['cash_change'];
            $Txn->branch_id = $data['branch_id'];
            $Txn->store_id = $data['store_id'];
            $Txn->payment_mode = $data['payment_mode'];
            $Txn->status = $data['status'];
            $Txn->balances_where_updated = $data['balances_where_updated'];

            $Txn->save();

            $data['id'] = $Txn->id;

            //print_r($data['items']); exit;

            //Save the items >> $data['items']
            POSOrderItemService::store($data);

            //Save the ledgers >> $data['ledgers']; and update the balances
            POSOrderLedgerService::store($data);

            //Update the account balances
            AccountBalanceUpdateService::doubleEntry($data);

            //Update the contact balances
            //ContactBalanceUpdateService::doubleEntry($data);

            DB::connection('tenant')->commit();

            return $Txn;

        }
        catch (\Throwable $e)
        {
            DB::connection('tenant')->rollBack();

            Log::critical('Fatal Internal Error: Failed to save estimate to database');
            Log::critical($e);

            //print_r($e); exit;
            if (App::environment('local'))
            {
                self::$errors[] = 'Error: Failed to save estimate to database.';
                self::$errors[] = 'File: ' . $e->getFile();
                self::$errors[] = 'Line: ' . $e->getLine();
                self::$errors[] = 'Message: ' . $e->getMessage();
            }
            else
            {
                self::$errors[] = 'Fatal Internal Error: Failed to save estimate to database. Please contact Admin';
            }

            return false;
        }
        //*/

    }

}
