<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\HegzaPayment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function Transactions(Request $request)
    {
        $List = HegzaPayment::orderby('id', 'DESC');
        if ($request->has('type') && $request->input('type') != null){
            $List->wherein('type',$request->input('type'));
        }
        $list = $List->paginate(50);
        $Title = 'لیست تراکنشات';
        $jdf = new Jdf();
        return view('admin-bot.payments.index',compact('list','Title','jdf'));
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

    public function single(Request $request)
    {
        $Payment = HegzaPayment::find($request->input('id'));
        if (!is_null($Payment)) {
            $Data = view('admin-bot.payments.modal', compact('Payment'))->render();
            return response()->json(['data' => $Data], 200);
        } else {
            return response()->json(['error' => 'تراکنش مورد نظر یافت نشد.'], 400);
        }
    }
}
