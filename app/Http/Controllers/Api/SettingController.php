<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /* دریافت قیمت ارز — the old URL (api/update-dollar-price) runs the AriaPay update; the schedule runs it too. */
    public function GetCurrencyPrice()
    {
        $code = \Illuminate\Support\Facades\Artisan::call('currency:update');
        return response()->json(['success' => $code === 0]);
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
