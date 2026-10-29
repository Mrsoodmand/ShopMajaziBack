<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\History;
use App\Models\HegzaOrder;
use App\Models\HegzaPayment;
use App\Models\HegzaUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BotUserController extends Controller
{
    /* لیست کاربران */
    public function index(Request $request)
    {
        $list = HegzaUser::orderby('id', 'DESC');
        if ($request->has('search') && $request->input('search') != ' ') {
            $list->where('username', 'LIKE', '%' . $request->input('search') . '&');
            $list->orwhere('user_id', 'LIKE', '%' . $request->input('search') . '%');
        }
        $list = $list->paginate(40);
        $jdf = new Jdf();
        return view('admin-v2.hegza.users.index', compact(
            'list',
            'jdf'
        ));
    }

    /* لیست تکی کاربران */
    public function singleUser($id, Request $request)
    {
        $user = HegzaUser::find($id);
        if (!is_null($user)) {
            $jdf = new Jdf();

            $OrdersCount = HegzaOrder::where('user_id', $user->id)->orderby('id', 'DESC')->count();
            $Orders = HegzaOrder::where('user_id', $user->id)->orderby('id', 'DESC')->take(20)->get();
            $Commission = HegzaPayment::where('user_id', $user->id)->where('type', 5)->orderby('id', 'DESC')->take(20)->get();
            $Payments = HegzaPayment::where('user_id', $user->id)->orderby('id', 'DESC')->take(20)->get();
            return view('admin-v2.hegza.users.single', compact(
                'user',
                'Payments',
                'Orders',
                'Commission',
                'jdf',
                'OrdersCount'
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
        $User->name = $request->input('user_name');
        $User->save();
        $DataChanged['user_name'] = $request->input('user_name');

//        $NewHistory = new History();
//        $NewHistory->type = 'user';
//        $NewHistory->post_id = $User->id;
//        $NewHistory->detail = json_encode($DataChanged);
//        $NewHistory->admin_id = Auth::guard('admin')->user()->id;
//        $NewHistory->save();
//        $User->save();

        return success($request, 'اطلاعات کاربر با موفقیت ویرایش گردید');
    }

    /* پنل کاربر */
    public function UserPanel($id)
    {
        Auth::guard('user')->loginUsingId($id);
        return redirect('/user/home');
    }
}
