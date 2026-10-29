<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\Wallet;
use App\User;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $UserId = UserID()->id;
        $Jdf = new Jdf();
        $Wallet = Wallet::where('user_id',$UserId)->orderby('id','DESC')->get();
        $Orders = Order::where('user_id',$UserId)->orderby('id','DESC')->take(10)->get();
        $Tickets = Ticket::where('user_id',$UserId)->orderby('id','DESC')->take(10)->get();
        $TicketsCount = Ticket::where('user_id',$UserId)->orderby('id','DESC')->count();
        $Commissions = Commission::where('to',$UserId)->orderby('id','DESC')->where('status','complete')->get();
        $identifier = User::where('identifier',$UserId)->get();
        return view('user-v2.index',compact(
            'Wallet',
            'Orders',
            'Tickets',
            'Commissions',
            'Jdf',
            'identifier',
            'TicketsCount'
        ));
    }


    public function profile()
    {
        $Commissions = Commission::where('to',UserID()->id)->orderby('id','DESC')->where('status','complete')->get();

        return view('user-v2.profile.index',compact('Commissions'));
    }


    public function ChangeTheme(Request $request)
    {
        $User = User::find(UserID()->id);
        $User->theme = $request->input('id');
        $User->save();
        return success($request,'تم با موفقیت تغییر پیدا کرد');
    }


    public function Referral()
    {
        $SumPayments = Payment::where('user_id',Auth::guard('user')->user()->id)->where('type',5)->sum('price');
        $Payments = Payment::where('user_id',Auth::guard('user')->user()->id)->where('type',5)->get();
        $Users = User::where('parent_id',Auth::guard('user')->user()->id)->get();
        $jdf = new Jdf();
        return view('user-v2.referral.index',compact('SumPayments','Payments','Users','jdf'));
    }
}
