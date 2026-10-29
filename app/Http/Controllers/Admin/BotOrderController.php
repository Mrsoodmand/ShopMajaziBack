<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\HegzaPayment;
use App\Models\HegzaUser;
use App\Models\Order;
use App\Models\OrderChat;
use App\Models\OrderItem;
use App\Models\HegzaOrder;
use App\Models\HegzaCountry;
use App\Models\HegzaProduct;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\AppleId;
use App\Models\History;
use App\Models\HegzaIp;
use App\lib\PhpEncrypt;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BotOrderController extends Controller
{
    /* صفحه سفارشات */
    public function index(Request $request)
    {
        $list = HegzaOrder::orderby('id', 'DESC');
        if ($request->has('search') && $request->input('search') != null) {
            $User = User::where('mobile', 'LIKE', '%' . $request->input('search') . '%')->get()->pluck('id')->toArray();
            $list->orwherein('user_id', $User);
            $list->orwhere('id', $request->input('search'));
        }
        if ($request->input('hash') != null && $request->input('hash') != '') {
            $id = Decode($request->input('hash'));
            $Ip = HegzaIp::find($id);
            $list->where('id', $Ip->id);
        }

        if ($request->input('id') != null && $request->input('id') != '') {
            $list->orwhere('id', $request->input('id'));
        }
        $list = $list->paginate(50);

        return view('admin-v2.hegza.orders.index', compact('list'));
    }

    public function single($id)
    {
        $Jdf = new Jdf();
        $Order = HegzaOrder::find($id);
        $Payments = HegzaPayment::where('order_id', $Order->id)->get();
        $User = HegzaUser::find($Order->user_id);
        $Ip = HegzaIp::where('order_id', $Order->id)->first();
       
        return view('admin-v2.hegza.orders.single',
            compact('Order',
                'Payments',
                'User',
                'Jdf',
                'Ip'
            ));
    }

    public function changeOrderStatus(Request $request)
    {
        $order = HegzaOrder::find($request->input('id'));
        $order->status = $request->input('status');
        $order->save();

        return response()->json(['msg' => 'وضعیت سفارش با موفقیت تغییر پیدا کرد'], 200);
    }
}
