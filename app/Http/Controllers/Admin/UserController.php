<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\History;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\Wallet;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /* لیست کاربران */
    public function index(Request $request)
    {
        $list = User::orderby('id', 'DESC');
        if ($request->has('search') && $request->input('search') != ' ') {
            $list->where('name', 'LIKE', '%' . $request->input('search') . '&');
            $list->orwhere('mobile', 'LIKE', '%' . $request->input('search') . '%');
        }
        $list = $list->paginate(40);
        $jdf = new Jdf();
        return view('admin-v2.users.index', compact(
            'list',
            'jdf',
        ));
    }

    /* لیست تکی کاربران */
    public function single($id, Request $request)
    {
        $user = User::find($id);
        if (!is_null($user)) {
            $OrdersCount = Order::where('user_id', $user->id)->orderby('id', 'DESC')->count();


            $Tickets = Ticket::where('user_id', $user->id)->orderby('id', 'DESC')->get();
            $Orders = Order::where('user_id', $user->id)->orderby('id', 'DESC')->take(20)->get();
            $Wallet = Wallet::where('user_id', $user->id)->orderby('id', 'DESC')->get();
            $Commission = Payment::where('user_id', $user->id)->where('type', 5)->orderby('id', 'DESC')->take(20)->get();
            $Users = User::where('parent_id', $user->id)->orderby('id', 'DESC')->get();
            $jdf = new Jdf();
            $Payments = Payment::where('user_id', $user->id)->orderby('id', 'DESC')->take(20)->get();
            return view('admin-v2.users.single', compact(
                'user',
                'Payments',
                'Tickets',
                'Orders',
                'Wallet',
                'Commission',
                'Users',
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
        $User = User::find($id);
        if (!is_null($User)) {
            $OrdersCount = Order::where('user_id', $User->id)->orderby('id', 'DESC')->count();
            $jdf = new Jdf();
            return view('admin-v2.users.edit', compact('User','jdf','OrdersCount'));
        } else {
            return back();
        }
    }

    /* ثبت اطلاعات کاربر */
    public function SubmitUserChange(Request $request)
    {
        $User = User::find($request->input('id'));

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
        if ($request->input('blocked_wallet_action') == 1) {

            if ($request->input('blocked_wallet') != 0) {
                $User->blocked_wallet = $User->blocked_wallet + $request->input('blocked_wallet');
                $DataChanged['blocked wallet increment'] = $request->input('blocked_wallet');
            }
        } else {
            if ($request->input('blocked_wallet') != 0) {
                $User->blocked_wallet = $User->blocked_wallet - $request->input('blocked_wallet');
                $DataChanged['blocked wallet decrement'] = $request->input('blocked_wallet');
            }
        }
        $User->name = $request->input('user_name');
        if ($request->has('status') && $request->input('status') != ' ' && $request->input('status') != null) {
            $User->status = 1;
            $DataChanged['status'] = 1;
        } else {
            $User->status = 0;
            $DataChanged['status'] = 0;

        }
        if ($request->has('wallet_blocked') && $request->input('wallet_blocked') != ' ' && $request->input('wallet_blocked') != null) {
            $User->wallet_blocked = 1;
            $DataChanged['wallet_blocked'] = 1;
        } else {
            $User->wallet_blocked = 0;
            $DataChanged['wallet_blocked'] = 0;
        }
        if ($request->has('bank_blocked') && $request->input('bank_blocked') != ' ' && $request->input('bank_blocked') != null) {
            $User->bank_blocked = 1;
            $DataChanged['bank_blocked'] = 1;
        } else {
            $User->bank_blocked = 0;
            $DataChanged['bank_blocked'] = 0;
        }
        if ($request->has('password') && $request->input('password') != ' ' && $request->input('password') != null) {
            $User->password = bcrypt($request->input('password'));
            // Record that it changed, never the password itself.
            $DataChanged['password'] = 'changed';
        }
        $DataChanged['user_name'] = $request->input('user_name');
        if (trim((string) $request->input('detail')) !== '') {
            $DataChanged['detail'] = trim((string) $request->input('detail'));
        }

        $NewHistory = new History();
        $NewHistory->type = 'user';
        $NewHistory->post_id = $User->id;
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
