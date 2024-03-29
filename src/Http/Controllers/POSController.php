<?php

namespace Rutatiina\POS\Http\Controllers;

use Illuminate\Http\Request;
use Rutatiina\POS\Models\POSOrder;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Milon\Barcode\Facades\DNS1DFacade;
use Milon\Barcode\Facades\DNS2DFacade;
use Spatie\Activitylog\Models\Activity;
use Rutatiina\Contact\Traits\ContactTrait;
use Rutatiina\POS\Services\POSOrderService;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Rutatiina\FinancialAccounting\Traits\FinancialAccountingTrait;

class POSController extends Controller
{
    use FinancialAccountingTrait;
    use ContactTrait;

    public function __construct()
    {
        $this->middleware('permission:pos.view');
        $this->middleware('permission:pos.order.view', ['only' => ['orders']]);
        $this->middleware('permission:pos.order.create', ['only' => ['create','store']]);
        $this->middleware('permission:pos.order.update', ['only' => ['edit','update']]);
        $this->middleware('permission:pos.order.delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            //return view('ui.limitless::layout_2-ltr-default.appVue');
            return view('ui.limitless::layout_2-ltr-default.vue-pos');
        }

        $tenant = Auth::user()->tenant;

        $txnAttributes = (new POSOrder())->rgGetAttributes();

        $txnAttributes['tenant_id'] = $tenant->id;
        $txnAttributes['created_by'] = Auth::id();
        $txnAttributes['number'] = POSOrderService::nextNumber();

        $txnAttributes['cash_tendered'] = 0;
        $txnAttributes['cash_change'] = 0;

        $txnAttributes['status'] = 'approved';
        $txnAttributes['contact_id'] = '';
        $txnAttributes['contact'] = json_decode('{"currencies":[]}'); #required
        $txnAttributes['date'] = date('Y-m-d');
        $txnAttributes['currency'] = $tenant->base_currency;
        $txnAttributes['taxes'] = json_decode('{}');
        $txnAttributes['payment_mode'] = null;
        $txnAttributes['items'] = [];

        return [
            'pageTitle' => 'New order', #required
            'pageAction' => 'New order', #required
            'txnUrlStore' => '/pos', #required
            'txnAttributes' => $txnAttributes, #required
        ];
    }

    public function store(Request $request)
    {
        //return $request;

        $order = POSOrderService::store($request);

        if ($order == false)
        {
            return [
                'status' => false,
                'messages' => POSOrderService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Order completed'],
            'order' => POSOrderService::find($order->id)
        ];
    }

    public function show($id)
    {
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $txn = POSOrderService::find($id);
        return $txn->toArray();
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request)
    {
        //
    }

    public function destroy()
    {
        //
    }

    #########################################

    public function orders(Request $request)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $query = POSOrder::query();

        if ($request->contact)
        {
            $query->where(function ($q) use ($request)
            {
                $q->where('contact_id', $request->contact);
            });
        }

        $txns = $query->latest()->paginate($request->input('per_page', 20));

        return [
            'tableData' => $txns
        ];
    }

    public function routes()
    {
        return [
            'delete' => route('pos.delete'),
            'cancel' => route('pos.cancel'),
        ];
    }

    public function delete(Request $request)
    {
        if (POSOrderService::destroyMany($request->ids))
        {
            return [
                'status' => true,
                'messages' => [count($request->ids) . ' Order(s) deleted.'],
            ];
        }
        else
        {
            return [
                'status' => false,
                'messages' => POSOrderService::$errors
            ];
        }
    }

    public function cancel(Request $request)
    {
        if (POSOrderService::cancelMany($request->ids))
        {
            return [
                'status' => true,
                'messages' => [count($request->ids) . ' Order(s) canceled.'],
            ];
        }
        else
        {
            return [
                'status' => false,
                'messages' => POSOrderService::$errors
            ];
        }
    }

}
