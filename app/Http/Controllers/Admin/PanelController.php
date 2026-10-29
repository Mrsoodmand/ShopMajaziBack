<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\User;
use Carbon\Carbon;
use App\lib\SocialPanel;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    public function index()
    {
//        $api = new SocialPanel();
//        return $api->order(['service' => 4284, 'link' => 'https://t.me/PARDIS261403', 'quantity' => 1000]);

        /* All */
        $User = User::count();
        $CompleteOrders = Order::where('status', 'complete')->count();
        $TodayCompleteOrders = Order::where('status', 'complete')->whereDate('created_at', Carbon::today())->count();
        $NewUser = User::whereDate('created_at', Carbon::today())->count();


        $Ticket = Ticket::wherein('status', ['User-answer', 'User-Ticket', 'progressing'])->orderby('id', 'DESC')->get();
        $PendingOrders = Order::wherein('status', ['pending', 'progressing'])->orderby('id', 'DESC')->get();
        $Moghayerat = Payment::orderby('id', 'DESC')->where('status', -3)->take(20)->get();

        $comments = Comment::orderby('id', 'DESC')->where('parent_id', '0')->where('status', 0)->take(20)->get();
        return view('admin-v2.home', compact('User',
            'CompleteOrders',
            'TodayCompleteOrders',
            'NewUser',
            'PendingOrders',
            'Ticket',
            'Moghayerat',
            'comments'
        ));
    }
}
