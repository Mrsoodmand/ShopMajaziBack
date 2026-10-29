<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /* دریافت قیمت ارز */
    public function GetCurrencyPrice()
    {
        $address = "http://api.navasan.tech/latest/?api_key=" . config('services.navasan.key') . "";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $address);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
        $export = json_decode(curl_exec($ch), true);
        curl_close($ch);


        if (!is_null($export)) {
            $Euro = $export['eur']['value'];
            $Dollar = $export['usd_sell']['value'];
            $RUB = $export['rub']['value'];

            // قیمت دلار
            $sell = $Dollar;
            $buy = $Dollar;
            $setting = Currency::find(1);
            $setting->sell = $sell;
            $setting->buy = $buy;
            $setting->updated_at = Carbon::now();
            $setting->save();

            // قیمت یورو
            $sell = $Euro;
            $buy = $Euro;
            $setting = Currency::find(4);
            $setting->sell = $sell;
            $setting->buy = $buy;
            $setting->updated_at = Carbon::now();
            $setting->save();

            $sell = $RUB;
            $buy = $RUB;
            $setting = Currency::find(34);
            $setting->sell = $sell;
            $setting->buy = $buy;
            $setting->updated_at = Carbon::now();
            $setting->save();


            // قمیت تتر
            $data = [
                'srcCurrency' => 'usdt',
                'dstCurrency' => 'rls',
            ];
            $ch = curl_init();
            $url = 'https://api.nobitex.ir/market/stats';
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 'content-type: application/json');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $export = json_decode(curl_exec($ch), true);
            curl_close($ch);

            $setting = Currency::find(26);
            $setting->sell = $export['stats']['usdt-rls']['bestSell'] / 10;
            $setting->buy = $export['stats']['usdt-rls']['bestBuy'] / 10;
            $setting->updated_at = Carbon::now();
            $setting->save();
            $Teter = $export['stats']['usdt-rls']['bestSell'] / 10;
            // Send Telegram Message //
            $Message = "قیمت ارز ها به روز شد
Dollar = $Dollar
Euro = $Euro
Ruble = $RUB
Teter = $Teter
#بروزرسانی_ارزها";

        } else {
            $Message = "مدت اعتبار Api Key برای ارز به پایان رسیده";
        }
        SendTelegramMessage($Message);
    }

    /* ارسال پیام در تلگرام */
    public function SendTelegramMessage(Request $request,$token)
    {
        $apiToken = $token;
        $data = [
            'chat_id' => $request->input('chat_id'),
            'text' => $request->input('text'),
            'parse_mode' => 'HTML'
        ];
        $curl = curl_init('https://api.telegram.org/bot' . $apiToken . '/sendMessage');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($curl);
        curl_close($curl);

    }
}
