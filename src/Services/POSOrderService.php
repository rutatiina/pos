<?php

namespace Rutatiina\POS\Services;

use Illuminate\Support\Facades\DB;
use Rutatiina\POS\Models\POSOrder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Rutatiina\POS\Models\POSOrderSetting;
use Rutatiina\GoodsDelivered\Services\GoodsDeliveredService;
use Rutatiina\GoodsDelivered\Services\GoodsDeliveredInventoryService;
use Rutatiina\FinancialAccounting\Services\AccountBalanceUpdateService;
use Rutatiina\FinancialAccounting\Services\ContactBalanceUpdateService;
use Milon\Barcode\Facades\DNS2DFacade;
use Milon\Barcode\Facades\DNS1DFacade;

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
            $Txn->discount = $data['discount'];
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
            //update the status of the txn
            if (AccountBalanceUpdateService::doubleEntry($data))
            {
                $Txn->status = $data['status'];
                $Txn->balances_where_updated = 1;
                $Txn->save();
            }

            if (GoodsDeliveredInventoryService::update($data))
            {
                //do nothing 
            }
            else
            {
                DB::connection('tenant')->rollBack();
            }

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
                
                //if (isset($e->errorInfo[1])) self::$errors[] = 'Mysql error number: ' . $e->errorInfo[1];
            }
            else
            {
                self::$errors[] = 'Fatal Internal Error: Failed to save estimate to database. Please contact Admin';
            }

            return false;
        }
        //*/

    }

    public static function find($id)
    {
        $txn = POSOrder::findOrFail($id);
        $txn->load('items.taxes', 'ledgers');
        $txn->setAppends([
            'taxes',
            'number_string',
            'total_in_words',
        ]);
        $txn->barcode_c39 = DNS1DFacade::getBarcodePNG(str_pad($txn->id, 10, "0", STR_PAD_LEFT), 'C39');
        return $txn;
    }

    public static function destroy($id)
    {
        //start database transaction
        DB::connection('tenant')->beginTransaction();

        try
        {
            $Txn = POSOrder::with(['items', 'ledgers'])->findOrFail($id);
            $txnArray = $Txn->toArray();

            GoodsDeliveredInventoryService::reverse($txnArray);

            //Update the account balances
            AccountBalanceUpdateService::doubleEntry($txnArray, true);

            //Delete the model
            $Txn->delete();

            DB::connection('tenant')->commit();

            return true;

        }
        catch (\Throwable $e)
        {
            DB::connection('tenant')->rollBack();

            Log::critical('Fatal Internal Error: Failed to delete POS order from database');
            Log::critical($e);

            //print_r($e); exit;
            if (App::environment('local'))
            {
                self::$errors[] = 'Error: Failed to delete POS order from database.';
                self::$errors[] = 'File: ' . $e->getFile();
                self::$errors[] = 'Line: ' . $e->getLine();
                self::$errors[] = 'Message: ' . $e->getMessage();
            }
            else
            {
                self::$errors[] = 'Fatal Internal Error: Failed to delete POS order from database. Please contact Admin';
            }

            return false;
        }
    }

    public static function cancel($id)
    {
        //start database transaction
        DB::connection('tenant')->beginTransaction();

        try
        {
            $Txn = POSOrder::with(['items', 'ledgers'])->findOrFail($id);
            $txnArray = $Txn->toArray();

            GoodsDeliveredInventoryService::reverse($txnArray);

            //Update the account balances
            AccountBalanceUpdateService::doubleEntry($txnArray, true);

            //Delete the model
            $Txn->canceled = 1;
            $Txn->save();

            DB::connection('tenant')->commit();

            return true;

        }
        catch (\Throwable $e)
        {
            DB::connection('tenant')->rollBack();

            Log::critical('Fatal Internal Error: Failed to cancel POS order from database');
            Log::critical($e);

            //print_r($e); exit;
            if (App::environment('local'))
            {
                self::$errors[] = 'Error: Failed to cancel POS order from database.';
                self::$errors[] = 'File: ' . $e->getFile();
                self::$errors[] = 'Line: ' . $e->getLine();
                self::$errors[] = 'Message: ' . $e->getMessage();
            }
            else
            {
                self::$errors[] = 'Fatal Internal Error: Failed to cancel POS order from database. Please contact Admin';
            }

            return false;
        }
    }

    public static function destroyMany($ids)
    {
        foreach($ids as $id)
        {
            if(!self::destroy($id)) return false;
        }
        return true;
    }

    public static function cancelMany($ids)
    {
        foreach($ids as $id)
        {
            if(!self::cancel($id)) return false;
        }
        return true;
    }

}
