<?php

namespace Rutatiina\POS\Services;

use Illuminate\Support\Facades\DB;
use Rutatiina\POS\Models\POSOrder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Rutatiina\POS\Models\POSOrderSetting;
use Rutatiina\GoodsDelivered\Services\GoodsDeliveredService;
use Rutatiina\FinancialAccounting\Services\AccountBalanceUpdateService;
use Rutatiina\FinancialAccounting\Services\ContactBalanceUpdateService;

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

            //if the Goods delivered package is installed, update the inventory for items with inventory tracking
            if ($data['has_inventory_trackable_items'] && class_exists('Rutatiina\GoodsDelivered\Services\GoodsDeliveredService') )
            {
                $requestInstance->merge([
                    'document_name' => 'Cash sale',
                    'itemable_id' => $data['id'],
                    'itemable_key' => 'pos_order_id',
                    'itemable_type' => 'Rutatiina\POS\Models\POSOrderItem',
                ]);

                $storeGoodsDeliveredNote = GoodsDeliveredService::store($requestInstance);
                // var_dump(GoodsDeliveredService::$errors); exit;

                if ($storeGoodsDeliveredNote == false)
                {
                    DB::connection('tenant')->rollBack();
                    self::$errors = GoodsDeliveredService::$errors;
                    return false;
                }
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
                self::$errors[] = 'Mysql error number: ' . $e->errorInfo[1];
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
