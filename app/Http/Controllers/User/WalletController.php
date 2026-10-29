<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Payment;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $id = UserID();
        $jdf = new Jdf();
        $list = Payment::where('user_id',$id->id)->wherein('type',[3,5])->orderby('id','DESC');
        if ($request->has('search') && $request->input('search') != null){
            $list->where('detail','LIKE','%'.$request->input('search').'%');
            $list->orwhere('value',$request->input('search'));
            $list->orwhere('id',$request->input('search'));
        }
        $list = $list->paginate(10);
        $Cards = Card::where('user_id',UserId()->id)->get();
        return view('user-v2.wallet.index',compact('list','jdf','Cards'));
    }


    public function CheckTransactionDetail(Request $request)
    {
        $Payment = Payment::find($request->input('id'));
        if (!is_null($Payment)){
            if (Auth::guard('user')->user()->id == $Payment->user_id){
                $View = view('user-v2.layout.modal-transaction',compact('Payment'))->render();
                return response()->json(['success' => $View],200);
            }else{

            }
        }else{

        }
    }
}
