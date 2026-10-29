<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Partner;
use App\Models\Ticket;
use App\Models\PartnerServices;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PartnerController extends Controller
{
    /* لیست همکاران */
    public function index()
    {
        $list = Partner::orderby('id', 'DESC')->paginate();
        return view('admin-v2.partner.index', compact('list'));
    }

    /* ایجاد همکار */
    public function create(Request $request)
    {
        return view('admin-v2.partner.create');
    }

    /* ثبت همکار */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required',
            'percent' => 'required',
            'percent_type' => 'required',
            'status' => 'required',
            'deposit_type' => 'required',
        ], [
            'name.required' => 'لطفا مقدار نام را وارد نمایید.',
            'mobile.required' => 'لطفا مقدار موبایل را وارد نمایید.',
            'percent.required' => 'لطفا مقدار درصد را وارد نمایید.',
            'percent_type.required' => 'لطفا مقدار نوع درصد را وارد نمایید.',
            'status.required' => 'لطفا مقدار وضعیت را وارد نمایید.',
//            'expire_date.required' => 'لطفا مقدار زمان اتمام همکاری را وارد نمایید.',
//            'wallet.required' => 'لطفا مقدار  را وارد نمایید.',
            'deposit_type.required' => 'لطفا مقدار  را وارد نمایید.',
        ]);
        if ($validate->fails()) {
            return response()->json(['error' => $validate->errors()->all()], 400);
        }
        try {

            $newPartner = new Partner();
            $newPartner->name = $request->input('name');
            $newPartner->mobile = $request->input('mobile');
            $newPartner->percent = $request->input('percent');
            $newPartner->percent_type = $request->input('percent_type');
            $newPartner->status = $request->input('status');
            $newPartner->expire_date = $request->input('expire_date');
            $newPartner->wallet = $request->input('wallet');
            $newPartner->deposit_type = $request->input('deposit_type');
            $newPartner->deposit_type = $request->input('deposit_type');
            $newPartner->save();

            foreach ($request->input('services') as $key) {
                $PartnerServices = new PartnerServices();
                $PartnerServices->partner_id = $newPartner->id;
                $PartnerServices->service_id = $key;
                $PartnerServices->save();
            }

            return response()->json(['success' => 'همکار با موفقیت ثبت شد.'], 200);

        } catch (\Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }
    }

    /* ایجاد همکار */
    public function edit(Request $request,$id)
    {
        $edit = Partner::find($id);
        return view('admin-v2.partner.create',compact('edit'));
    }

}
