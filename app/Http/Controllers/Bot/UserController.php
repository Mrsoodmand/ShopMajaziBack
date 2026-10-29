<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\History;
use App\Models\HegzaOrder;
use App\Models\HegzaPayment;
use App\Models\HegzaHistory;
use App\Models\HegzaUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /* لیست کاربران */
    public function index(Request $request)
    {
        $list = HegzaUser::orderby('id', 'DESC');
        if ($request->has('search') && !is_null($request->search) && $request->input('search') != ' ') {
            $list->where('username',$request->input('search'));
        }
        if ($request->has('tel_id') && !is_null($request->tel_id) && $request->input('tel_id') != ' '){
            $list->where('user_id', $request->input('tel_id')) ;
        }
        $list = $list->paginate(40);
        $jdf = new Jdf();
        return view('admin-bot.users.index', compact(
            'list',
            'jdf'
        ));
    }

    /* لیست تکی کاربران */
    public function single($id, Request $request)
    {
        $user = HegzaUser::find($id);
        if (!is_null($user)) {
            $jdf = new Jdf();
            $OrdersCount = HegzaOrder::where('user_id', $user->id)->orderby('id', 'DESC')->count();
            $Orders = HegzaOrder::where('user_id', $user->id)->orderby('id', 'DESC')->take(20)->get();
            $Commission = HegzaPayment::where('user_id', $user->id)->where('type', 5)->orderby('id', 'DESC')->take(20)->get();
            $Payments = HegzaPayment::where('user_id', $user->id)->orderby('id', 'DESC')->take(20)->get();
            $Users = HegzaUser::where('parent',$user->id)->get();
            return view('admin-bot.users.single', compact(
                'user',
                'Payments',
                'Orders',
                'Commission',
                'jdf',
                'OrdersCount',
                'Users'
            ));
        } else {
            return exception_error($request, 'کاربر مورد نظر یافت نشد');
        }
    }

    /* ویرایش کاربر */
    public function editUser($id, Request $request)
    {
        $User = HegzaUser::find($id);
        if (!is_null($User)) {
            $OrdersCount = HegzaOrder::where('user_id', $User->id)->orderby('id', 'DESC')->count();
            $jdf = new Jdf();
            return view('admin-v2.hegza.users.edit', compact('User', 'jdf', 'OrdersCount'));
        } else {
            return back();
        }
    }

    /* ثبت اطلاعات کاربر */
    public function SubmitUserChange(Request $request)
    {
        $User = HegzaUser::find($request->input('id'));
        $type = 'user';
        $valueId = $User->id;

        if ($request->has('payment_id')) {
            $payment = HegzaPayment::find($request->input('payment_id'));
            $payment->status = -2;
            $payment->save();
            $type = 'payment';
            $valueId = $payment->id;

            $User->wallet = $User->wallet + $payment->price;
            $DataChanged['wallet increment'] = $request->input('wallet');

        }else{
            if ($request->input('wallet_action') == 1) {
                if ($request->input('wallet') != 0) {
                    $User->wallet = $User->wallet + $request->input('wallet');
                    $DataChanged['wallet increment'] = $request->input('wallet');
                }
            } else {
                if ($request->input('wallet') != 0) {
                    $User->wallet = $User->wallet - $request->input('wallet');
                    $DataChanged['wallet decrement'] = $request->input('wallet');
                }
            }
        }

        $NewHistory = new HegzaHistory();
        $NewHistory->type = $type;
        $NewHistory->post_id = $valueId;
        $NewHistory->user_id = $User->id;
        $NewHistory->detail = json_encode($DataChanged);
        $NewHistory->admin_id = Auth::guard('admin')->user()->id;
        $NewHistory->save();
        $User->save();

        return success($request, 'اطلاعات کاربر با موفقیت ویرایش گردید');
    }

    /* پنل کاربر */
    public function UserPanel($id)
    {
        Auth::guard('user')->loginUsingId($id);
        return redirect('/user/home');
    }
}
