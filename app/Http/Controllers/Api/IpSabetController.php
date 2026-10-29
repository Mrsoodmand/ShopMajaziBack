<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Ip;

class IpSabetController extends Controller
{

    public function checkSuspendIps()
    {
        $Now = Carbon::now()->addDays(3)->format('Y-m-d H');
        $Ips = Ip::whereDate('expire_date', '<=', $Now)->where('status', 1)->orderby('id', 'ASC')->take(30)->get();
        foreach ($Ips as $ip) {
            $updateIp = Ip::find($ip->id);
            $updateIp->status = -1;
            $updateIp->sms = 1;
            $updateIp->save();

            $updateOrder = Order::find($ip->order_id);
            $updateOrder->status = 'suspended';
            $updateOrder->save();

            $oldPayment = Payment::where('order_id', $ip->order_id)->wherein('type', [1, 8])->orderby('id', 'DESC')->first();
            $oldPaymentData = json_decode($oldPayment->help, true);
            $Help =
                [
                    'ip_id' => $ip->id,
                    'volume' => $oldPaymentData['volume'],
                    'product_id' => $oldPaymentData['product_id']
                ];

            $Product = Product::find($oldPaymentData['product_id']);
            $Data = json_decode($Product->detail, true);
            if ($Data['dis'] == 0) {
                $Plan_price = $Product->price;
            } else {
                $Plan_price = $Data['dis'];
            }

            $price = $oldPaymentData['volume'] * 1500;
            $Price = $price + $Plan_price;

            $Payment = new Payment();
            $Payment->order_id = $ip->order_id;
            $Payment->user_id = $ip->user_id;
            $Payment->type = 8;
            $Payment->status = 'waiting to pay';
            $Payment->gate = 'Bank';
            $Payment->creator = 'System';
            $Payment->price = $Price;
            $Payment->help = json_encode($Help);
            $Payment->save();
        }
    }


    public function checkFailed()
    {
        $Now = Carbon::now()->subDays(1)->format('Y-m-d');
        $Ips = Ip::whereDate('expire_date', '<=', $Now)->where('status', -1)->orderby('id', 'ASC')->take(30)->get();
        foreach ($Ips as $ip) {
            $updateIp = Ip::find($ip->id);
            $updateIp->status = -2;
            $updateIp->sms = 2;
            $updateIp->save();

            $updateOrder = Order::find($ip->order_id);
            $updateOrder->status = 'failed';
            $updateOrder->save();
        }
    }

    
    public function sendSms()
    {
        $Ips = Ip::where('sms', '!=', 0)->orderby('id', 'ASC')->take(50)->get();
        foreach ($Ips as $ip) {
            $User = User::find($ip->user_id);
            if ($ip->sms == 1) {
                $Pat = 507031;
                $Data = [
                    'NUM' => $ip->order_id,
                ];
            } elseif ($ip->sms == 2) {
                $Pat = 138554;
                $Data = [
                    'NUM' => $ip->order_id,
                    'STATUS' => 'لغو شده',
                ];
            }
            $item = Ip::find($ip->id);
            $item->sms = 0;
            $item->save();

            sendSmsWithSmsir($User->mobile,$Data,$Pat);
        }
    }
}
