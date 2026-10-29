<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\AppleId;
use App\Models\HegzaHistory;
use App\Models\HegzaIp;
use App\Models\HegzaOrder;
use App\Models\HegzaPayment;
use App\Models\HegzaUser;
use App\Models\History;
use App\Models\Order;
use App\Models\OrderChat;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Wallet;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
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
        $jdf = new Jdf();
        return view('admin-bot.orders.index', compact('list', 'jdf'));
    }

    /* جزئیات سفارش */
    public function single($id)
    {
        $jdf = new Jdf();
        $Order = HegzaOrder::find($id);

        $Payment = HegzaPayment::where('order_id', $Order->id)->orderby('id', 'ASC')->first();
        $Payments = HegzaPayment::where('order_id', $Order->id)->orderby('id', 'DESC')->get();
        $User = HegzaUser::find($Order->user_id);
        $Ip = HegzaIp::where('order_id', $Order->id)->orderby('id', 'DESC')->first();
        $UsedVolume = 0;

        if (!is_null($Ip)) {
            $Domain = HegzaIp::find($Ip->parent_id);

            $ServerIp = json_decode($Domain->detail, true);
            $IpDetail = json_decode($Ip->detail, true);
            $GetData['ip_address'] = $ServerIp['ip_api_domain'];
            $GetData['ip_port'] = $ServerIp['ip_port'];
            $GetData['Session'] = $ServerIp['session'];
            $GetData['uid'] = $Ip->UID;
            $GetData['name'] = $IpDetail['user_remark'];
            $GetData['ip_api_domain'] = $ServerIp['ip_api_domain'];
            $GetData['Data'] = $ServerIp;
            $UsedVolume = getClientTraffics($GetData);
            if ($UsedVolume['success'] == false) {
                $UsedVolume = 00;
            } else {
                $UsedVolume = $UsedVolume['hajm'];
            }
        }

        $Servers = HegzaIp::where('status', 1)->where('type', 'pu-ip-sabet')->where('status', 1)->get();

        return view('admin-bot.orders.single',
            compact('Order',
                'Payments',
                'Payment',
                'User',
                'jdf',
                'Ip',
                'UsedVolume',
                'Servers'
            ));
    }

    /* تغییر وضعیت سفارش */
    public function changeOrderStatus(Request $request)
    {
        $order = HegzaOrder::find($request->input('id'));
        $status = $request->input('status');
        $oldStatus = $order->status;
        $newStatus = $status;
        if ($request->status == '-1') {
            $Ip = HegzaIp::where('order_id', $order->id)->first();
            $ipDetail = json_decode($Ip->detail, true);
            $ServerIp = HegzaIp::find($Ip->parent_id);
            $ServerIp = json_decode($ServerIp->detail, true);


            $timeInMilliseconds = 1;
            $Volume = 1;

            $Data['time'] = $timeInMilliseconds;
            $Data['hajm'] = $Volume;
            $Data['ip_address'] = $ServerIp['ip_api_domain'];
            $Data['ip_port'] = $ServerIp['ip_port'];
            $Data['Session'] = $ServerIp['session'];
            $Data['uid'] = $Ip->UID;
            $Data['name'] = $ipDetail['user_remark'];
            $Data['ip_api_domain'] = $ServerIp['ip_api_domain'];
            $Data['Data'] = $ServerIp;
            $sanaie = updateSanaiePanel($Data);
            if ($sanaie['status'] == 1) {
                $status = -1;

                $Ip->status = -1;
                $Ip->save();
            }

        } elseif ($request->status == 1) {

            $Ip = HegzaIp::where('order_id', $order->id)->first();
            $ipDetail = json_decode($Ip->detail, true);
            $ServerIp = HegzaIp::find($Ip->parent_id);
            $ServerIp = json_decode($ServerIp->detail, true);


            $date = Carbon::parse($Ip->expire_date);
            $timeInMilliseconds = $date->valueOf();
            $Volume = $Ip->volume * 1073741824;

            $Data['time'] = $timeInMilliseconds;
            $Data['hajm'] = $Volume;
            $Data['ip_address'] = $ServerIp['ip_api_domain'];
            $Data['ip_port'] = $ServerIp['ip_port'];
            $Data['Session'] = $ServerIp['session'];
            $Data['uid'] = $Ip->UID;
            $Data['name'] = $ipDetail['user_remark'];
            $Data['ip_api_domain'] = $ServerIp['ip_api_domain'];
            $Data['Data'] = $ServerIp;
            $sanaie = updateSanaiePanel($Data);
            if ($sanaie['status'] == 1) {
                $status = 1;

                $Ip->status = 1;
                $Ip->save();
            }
        }

        $DataChanged = [
            'msg' => "تغییر وضعیت سفارش از $oldStatus به $newStatus",
        ];

        $order->status = $status;
        $order->save();

        $NewHistory = new HegzaHistory();
        $NewHistory->type = 'change-order-status';
        $NewHistory->post_id = $order->id;
        $NewHistory->user_id = $order->user_id;
        $NewHistory->detail = json_encode($DataChanged);
        $NewHistory->admin_id = Auth::guard('admin')->user()->id;
        $NewHistory->save();
        return response()->json(['msg' => 'وضعیت سفارش با موفقیت تغییر پیدا کرد'], 200);
    }

    /**/
    public function changeIpStatus(Request $request)
    {
        $Ip = HegzaIp::find($request->id);
        $detail = json_decode($Ip->detail, true);
        $detail['user_remark'] = $request->remark;
        $Ip->UID = $request->input('UID');
        $Ip->detail = json_encode($detail);
        $Ip->expire_date = $request->input('expire_date');

        $Ip->volume = $request->input('total');
        $Ip->save();

        $order = HegzaOrder::find($Ip->order_id);
        $order->total_bandwidth = $request->total;
        $order->save();


        $ipDetail = json_decode($Ip->detail, true);

        $ServerIp = HegzaIp::find($Ip->parent_id);
        $ServerIp = json_decode($ServerIp->detail, true);

        $date = Carbon::parse($request->input('expire_date'));
        $timeInMilliseconds = $date->valueOf();
        $Volume = $request->input('total') * 1073741824;

        $newData = [
            'code' => null,
            'type' => 'asdas',
            'UID' => $request->UID,
            'serverUrl' => $ServerIp['ip_address'],
            'inboundPort' => $ServerIp['port_inbound'],
            'name' => $request->remark,
        ];
        $code = generateCode($newData);
        $newIpDetail = [
            'user_port' => $request->port,
            'user_remark' => $request->remark,
            'user_code' => $code,
        ];

        $Data['time'] = $timeInMilliseconds;
        $Data['hajm'] = $Volume;
        $Data['ip_address'] = $ServerIp['ip_api_domain'];
        $Data['ip_port'] = $ServerIp['ip_port'];
        $Data['Session'] = $ServerIp['session'];
        $Data['uid'] = $Ip->UID;
        $Data['name'] = $newIpDetail['user_remark'];
        $Data['ip_api_domain'] = $ServerIp['ip_api_domain'];
        $Data['Data'] = $ServerIp;
        $sanaie = updateSanaiePanel($Data);

        $DataChanged = [
            'msg' => "تغییر اطلاعات آی پی",
            'oldIp' => $Ip->id,
        ];

        $NewHistory = new HegzaHistory();
        $NewHistory->type = 'change-ip-setting';
        $NewHistory->post_id = $order->id;
        $NewHistory->user_id = $order->user_id;
        $NewHistory->detail = json_encode($DataChanged);
        $NewHistory->admin_id = Auth::guard('admin')->user()->id;
        $NewHistory->save();

        return response()->json(['msg' => 'ویرایش آی پی با موفقیت انجام شد'], 200);

    }

    /**/
    public function changeServer(Request $request)
    {
        $id = $request->input('id');
        $to = $request->input('to');

        DB::beginTransaction();
        $Order = Order::find($id);
        $Ip = HegzaIp::where('order_id', $Order->id)->orderby('id', 'DESC')->where('status', 1)->first();

        if (!is_null($Ip)) {
            $Domain = HegzaIp::find($Ip->parent_id);

            $ServerIp = json_decode($Domain->detail, true);
            $IpDetail = json_decode($Ip->detail, true);
            $oldRemark = $IpDetail['user_remark'];
            $GetData['ip_address'] = $ServerIp['ip_api_domain'];
            $GetData['ip_port'] = $ServerIp['ip_port'];
            $GetData['Session'] = $ServerIp['session'];
            $GetData['uid'] = $Ip->UID;
            $GetData['name'] = $IpDetail['user_remark'];
            $GetData['ip_api_domain'] = $ServerIp['ip_api_domain'];
            $GetData['Data'] = $ServerIp;
            $UsedVolume = getClientTraffics($GetData);

            if ($UsedVolume['success'] == false) {
                $UsedVolume = $Ip->last_used;
            } else {
                $UsedVolume = $UsedVolume['hajm'];
            }

            $Data['time'] = 1;
            $Data['hajm'] = 1;
            $Data['ip_address'] = $ServerIp['ip_api_domain'];
            $Data['ip_port'] = $ServerIp['ip_port'];
            $Data['Session'] = $ServerIp['session'];
            $Data['uid'] = $Ip->UID;
            $Data['name'] = $IpDetail['user_remark'];
            $Data['ip_api_domain'] = $ServerIp['ip_api_domain'];
            $Data['Data'] = $ServerIp;
            $sanaie = updateSanaiePanel($Data);

        }
        $Ip->status = -1;
        $Ip->last_used = $UsedVolume / 1024;
        $Ip->save();

        $newServer = HegzaIp::find($to);

        $ip_detail = json_decode($newServer->detail, true);
        $Data = loginToSanaeiAccount($ip_detail);

        if ($Data['login'] == true) {
            $ip_detail['session'] = $Data['sessionCookie'];

            $newServer->detail = json_encode($ip_detail);
            $newServer->save();

            $Count = HegzaIp::orderby('id', 'DESC')->where('type', 'pu-ip-sabet-user')->count() + 1;

            $NameTwo = $ip_detail['ip_name'];
            $Name = $NameTwo . '-' . $Count . '-' . strtotime(Carbon::now());
            $items = [
                'Session' => $Data['sessionCookie'],
                'apiUrl' => $ip_detail['ip_api_domain'],
                'serverUrl' => $ip_detail['ip_address'],
                'inboundId' => $ip_detail['ip_inbound'],
                'inboundPort' => $ip_detail['port_inbound'],
                'name' => $Name,
                'code' => $ip_detail['code'],
                'type' => $ip_detail['type'],
                'method' => 'new',
            ];
            $data = addClientToSanaie($items);
            if ($data['status'] == 1) {

                $UserIp_detail = [
                    'user_port' => $ip_detail['ip_port'],
                    'user_remark' => $Name,
                    'user_code' => $data['msg'],
                ];

                $UserIp = new HegzaIp();
                $UserIp->status = 1;
                $UserIp->type = $Ip->type;
                $UserIp->detail = json_encode($UserIp_detail);
                $UserIp->parent_id = $Ip->parent_id;
                $UserIp->UID = $data['UID'];
                $UserIp->category_id = $Ip->category_id;
                $UserIp->volume = $Ip->volume;
                $UserIp->expire_date = $Ip->expire_date;
                $UserIp->order_id = $Ip->order_id;
                $UserIp->user_id = $Ip->user_id;
                $UserIp->product_id = $Ip->product_id;
                $UserIp->sms = $Ip->sms;
                $UserIp->from = $Ip->from;
                $UserIp->last_used = $Ip->last_used;
                $UserIp->save();

                $date = Carbon::parse($UserIp->expire_date);
                $timeInMilliseconds = $date->valueOf();

                if ($UsedVolume == 0) {
                    $Volume = $UserIp->volume * 1073741824;
                } else {
                    $Volume = $UsedVolume * 1073741824;
                }

                $Data['time'] = $timeInMilliseconds;
                $Data['hajm'] = $Volume;
                $Data['ip_address'] = $ip_detail['ip_api_domain'];
                $Data['ip_port'] = $ip_detail['ip_port'];
                $Data['Session'] = $ip_detail['session'];
                $Data['uid'] = $UserIp->UID;
                $Data['name'] = $UserIp_detail['user_remark'];
                $Data['ip_api_domain'] = $ip_detail['ip_api_domain'];
                $Data['Data'] = $ip_detail;
                $sanaie = updateSanaiePanel($Data);

                $newRemark = $UserIp_detail['user_remark'];

                $DataChanged = [
                    'msg' => "تغییر $oldRemark به $newRemark",
                    'oldIp' => $Ip->id,
                    'newIp' => $UserIp->id
                ];

                $NewHistory = new HegzaHistory();
                $NewHistory->type = 'change-server';
                $NewHistory->post_id = $Order->id;
                $NewHistory->user_id = $Order->user_id;
                $NewHistory->detail = json_encode($DataChanged);
                $NewHistory->admin_id = Auth::guard('admin')->user()->id;
                $NewHistory->save();

                DB::commit();

                return response()->json(['msg' => 'ویرایش آی پی با موفقیت انجام شد'], 200);

            } else {
                DB::rollBack();
                return exception_error($request, 'خطا در ایجاد کاربر');
            }
        } else {
            DB::rollBack();
            return exception_error($request, 'خطا در لاگین سرور');
        }
    }
}
