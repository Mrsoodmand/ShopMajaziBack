<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function Transactions(Request $request)
    {
        $List = Payment::orderby('id', 'DESC');
        if ($request->has('type') && $request->input('type') != null){
            $List->wherein('type',$request->input('type'));
        }
        if ($request->has('mobile') && $request->input('mobile') != null){
            $User = User::where('mobile',$request->mobile)->first();
            if (!is_null($User)){
                $List->where('user_id',$User->id);
            }
        }
        $list = $List->paginate(50);
        $Title = 'لیست تراکنشات';
        return view('admin-v2.transaction.index',compact('list','Title'));
    }

    public function BankPayment(Request $request)
    {
        $List = Payment::orderby('id', 'DESC')->where('gate','Bank');
        if ($request->has('type') && $request->input('type') != null){
            $List->wherein('type',$request->input('type'));
        }
        $list = $List->paginate(50);
        $Title = 'پرداختی های بانکی';
        return view('admin-v2.transaction.index',compact('list','Title'));
    }

    public function Contradiction(Request $request)
    {
        $List = Payment::orderby('id', 'DESC')->where('status',-3);
        if ($request->has('type') && $request->input('type') != null){
            $List->wherein('type',$request->input('type'));
        }
        $list = $List->paginate(50);
        $Title = 'پرداختی های بانکی';
        return view('admin-v2.transaction.index',compact('list','Title'));
    }

    public function Single(Request $request)
    {
        $Payment = Payment::find($request->input('id'));
        if (!is_null($Payment)) {
            $Data = view('admin-v2.modal.payment', compact('Payment'))->render();
            return response()->json(['data' => $Data], 200);
        } else {
            return response()->json(['error' => 'تراکنش مورد نظر یافت نشد.'], 400);
        }
    }
}
