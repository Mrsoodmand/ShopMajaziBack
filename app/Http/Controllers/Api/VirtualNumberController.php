<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Virtual_Country;
use App\Models\Virtual_Service;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Virtual_Products;

class VirtualNumberController extends Controller
{
    /* چک کردن قیمت Pva */
    public function PvaCheckPrice()
    {
        $Products = Virtual_Products::where('type', 'pva')->where('is_updated', 0)->select('status', 'id', 'country_code', 'service_code')->take(200)->get();
        if (count($Products) != 0) {
            foreach ($Products as $product) {
                $Data = [
                    'metod' => 'get_service_price',
                    'country' => $product->country_code,
                    'service' => $product->service_code,
                ];
                $Result = json_decode(VirtualNumber_SmsPvaMethod($Data), true);
                $Currency = Currency::wherein('id', [1, 34])->select('sell', 'id')->get();
                $Dollar = $Currency->where('id', 1)->first()->sell * 2;

                if (is_array($Result)) {
                    if ($Result['response'] == 1) {
                        $Update = Virtual_Products::find($product->id);
                        if ($product->status == 0) {
                            $Update->status = 1;
                        }
                        $Update->is_updated = 1;
                        $Update->dollar_price = $Result['price'];
                        $Update->toman_price = $Dollar * $Result['price'];
                        $Update->save();
                    }
                }
            }
        } else {
            Virtual_Products::where('type', 'pva')->update(['is_updated' => 0]);
            $Message = "تمامی محصولات Pva بروز رسانی شد.";
            return SendTelegramMessage($Message);
        }
    }

    /* چک کردن قیمت Sms */
    public function SmsCheckPrice()
    {
        $Country = Virtual_Country::where('man', '!=', 'empty')->where('is_updated', 0)->select('id', 'man')->take(1)->get();
        $Currency = Currency::wherein('id', [1, 34])->select('sell', 'id')->get();
        $Ruble = $Currency->where('id', 34)->first()->sell * 2.5;
        if (count($Country) != 0) {
            foreach ($Country as $country) {
                $Url = "http://api.sms-man.com/control/get-prices?token=1vORE0znw6EQD-4TgUQavVXJMqW4-d9e&country_id=$country->man";
                $Result = json_decode(VirtualNumber_SmsManMethod($Url), true);
                if (is_array($Result)) {
                    $Products = Virtual_Products::where('country_id', $country->id)->where('type', 'man')->select('id', 'service_code')->get();
                    foreach ($Products as $product) {
                        if (array_key_exists($product->service_code, $Result)) {
                            $Price = $Result[$product->service_code]['cost'];
                            $Price = $Ruble * $Price;

                            $Update = Virtual_Products::find($product->id);
                            if ($product->status == 0) {
                                $Update->status = 1;
                            }
                            $Update->is_updated = 1;
                            $Update->dollar_price = $Result[$product->service_code]['cost'];
                            $Update->toman_price = $Price;
                            $Update->save();
                        }
                    }
                    $CountryUpdate = Virtual_Country::find($country->id);
                    $CountryUpdate->is_updated = 1;
                    $CountryUpdate->save();
                }
            }
        } else {
            Virtual_Country::where('man', '!=', 'empty')->update(['is_updated' => 0]);
            $Message = "تمامی محصولات Man بروز رسانی شد.";
            return SendTelegramMessage($Message);
        }
    }

    /* چک کردن قیمت Active */
    public function ActiveCheckPrice()
    {
        $Service = Virtual_Service::where('active', '!=', 'empty')->orderby('sort', 'ASC')->where('active_update', 0)->first();
        $Currency = Currency::wherein('id', [1, 34])->select('sell', 'id')->get();
        $Ruble = $Currency->where('id', 34)->first()->sell * 2.5;
        if (!is_null($Service)) {
            $Url = "https://api.sms-activate.org/stubs/handler_api.php?api_key=" . config('services.sms_activate.key') . "&action=getTopCountriesByService&service=$Service->active";
            $Result = json_decode(VirtualNumber_ActivePvaMethod($Url), true);
            if ($Result != null) {
                if (array_key_exists('status', $Result) && $Result['status'] == 'error') {
                    $Update = Virtual_Service::find($Service->id);
                    $Update->active_update = -1;
                    $Update->save();
                } else {

                    $P_Id = null;
                    foreach ($Result as $item) {
                        $Product = Virtual_Products::where('service_code', $Service->active)->where('country_code', $item['country'])->where('type', 'active')->first();
                        if (!is_null($Product)) {

                            $Price = $Ruble * $item['retail_price'];
                            $Product->dollar_price = $item['retail_price'];
                            $Product->toman_price = $Price;
                            if ($Product->status == 0) {
                                $Product->status = 1;
                            }
                            $Product->save();
                            $P_Id[] = $Product->id;
                        }
                    }
                    if (!is_null($P_Id)) {
                        Virtual_Products::wherenotIn('id', $P_Id)->where('service_code', $Service->active)->where('type', 'active')->update(['status' => -1]);
                    }
                    $Update = Virtual_Service::find($Service->id);
                    $Update->active_update = 1;
                    $Update->save();
                }
            } else {
                $Update = Virtual_Service::find($Service->id);
                $Update->active_update = 1;
                $Update->save();
            }
        } else {
            Virtual_Service::where('active', '!=', 'empty')->update(['active_update' => 0]);
            $Message = "تمامی محصولات Active بروز رسانی شد.";
            return SendTelegramMessage($Message);
        }
    }

    /* لغو سفارشاتی که زمانشان گذشته است */
    public function CheckVirtualNumberExpire()
    {
        $Orders = Order::where('type', 1)->where('status', 'pending')->where('pay_status', 'Complete')->get();

        foreach ($Orders as $order) {
            $Now = Carbon::now()->format('Y-m-d H:i:s');
            if ($order->expire_date < $Now) {
                $Detail = OrderItem::where('order_id', $order->id)->first();
                if (!is_null($Detail)) {
                    $Sms = json_decode($Detail->detail, true)['sms'];
                    if ($Sms == null) {
                        $UpdateOrder = Order::find($order->id);
                        $UpdateOrder->status = 'failed';
                        $UpdateOrder->wallet_status = 1;
                        $UpdateOrder->save();

                        $Payment = new Payment();
                        $Payment->user_id = $UpdateOrder->user_id;
                        $Payment->order_id = $UpdateOrder->id;
                        $Payment->type = 2;
                        $Payment->status = 'Complete';
                        $Payment->creator = 'system';
                        $Payment->help = "برگشت وجه سفارش شماره $UpdateOrder->id به کیف پول بابت لغو شدن سفارش ";
                        $Payment->price = $UpdateOrder->price;
                        $Payment->save();

                        $User = User::find($order->user_id);
                        $User->wallet = $User->wallet + $UpdateOrder->price;
                        $User->save();
                    }
                } else {
                    $UpdateOrder = Order::find($order->id);
                    $UpdateOrder->status = 'failed';
                    $UpdateOrder->wallet_status = 1;
                    $UpdateOrder->save();
                }
            }
        }
    }
}
