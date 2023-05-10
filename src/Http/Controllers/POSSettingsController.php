<?php

namespace Rutatiina\POS\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Rutatiina\Sales\Models\SalesSetting;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Rutatiina\POS\Models\POSOrderSetting;
use Rutatiina\Item\Traits\ItemsVueSearchSelect;
use Rutatiina\FinancialAccounting\Models\Account;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Rutatiina\FinancialAccounting\Traits\FinancialAccountingTrait;

class POSSettingsController extends Controller
{
    use FinancialAccountingTrait;
    use ItemsVueSearchSelect;

    private  $txnEntreeSlug = 'offer';

    public function __construct()
    {
		$this->middleware('permission:pos.view');
		$this->middleware('permission:pos.create', ['only' => ['create','store']]);
		$this->middleware('permission:pos.update', ['only' => ['edit','update']]);
		$this->middleware('permission:pos.delete', ['only' => ['destroy']]);
	}

    public function index()
	{
        //load the vue version of the app
        if (!FacadesRequest::wantsJson()) {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        return [
            'financial_accounts' => Account::all(),
            'settings' => POSOrderSetting::first()
        ];
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
	{
	    //print_r($request->all()); exit;

        //validate data posted
        $validator = Validator::make($request->all(), [
            'document_name' => ['required', 'string', 'max:50'],
            'number_prefix' => ['string', 'max:20', 'nullable'],
            'number_postfix' => ['string', 'max:20', 'nullable'],
            'minimum_number_length' => ['required', 'numeric'],
            'minimum_number' => ['required', 'numeric'],
            //'maximum_number' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return ['status' => false, 'messages' => $validator->errors()->all()];
        }

        //save data posted
        $settings = POSOrderSetting::first();
        $settings->document_name = $request->document_name;
        $settings->number_prefix = $request->number_prefix;
        $settings->number_postfix = $request->number_postfix;
        $settings->minimum_number_length = $request->minimum_number_length;
        $settings->minimum_number = $request->minimum_number;
        //$settings->maximum_number = $request->maximum_number;
        $settings->debit_financial_account_code = $request->debit_financial_account_code;
        $settings->credit_financial_account_code = $request->credit_financial_account_code;
        $settings->print_receipt = $request->print_receipt ? true : false;
        $settings->save();

        return [
            'status'    => true,
            'messages'  => ['Settings updated'],
        ];

    }

    public function show($id)
	{
	    //
    }

    public function edit($id)
	{
	    //
    }

    public function update(Request $request)
	{
	    //
	}

    public function destroy($id)
	{
	    //
	}

	#-----------------------------------------------------------------------------------
}
