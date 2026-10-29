<?php

use App\Models\PartnerServices;
use App\Models\Partner;
use App\Models\OrderProfit;
use App\Models\PartnerProfit;


// Send Exception Error
function exception_error($request, $error)
{
    if ($request->ajax()) {
        return response()->json(['error' => [$error]], 400);
    } else {
        \Illuminate\Support\Facades\Session::flash('error', [$error]);
        return back();
    }
}

// Send Validation Errors
function validation_error($request, $error)
{
    if ($request->ajax()) {
        return response()->json(['error' => $error], 400);
    } else {
        \Illuminate\Support\Facades\Session::flash('error', $error);
        return back();
    }
}

// Send Success Message
function success($request, $error)
{
    if ($request->ajax()) {
        return response()->json(['success' => $error], 200);
    } else {
        \Illuminate\Support\Facades\Session::flash('success', $error);
        return back();
    }
}

// Send Verify Code
function SendCode($mobile, $code)
{
    $client = new \SoapClient('http://188.0.240.110/class/sms/wsdlservice/server.php?wsdl');
    $user = "mahdi-930";
    $pass = "persianeagle_930";
    $fromNum = "+9850005040858085";
    $toNum = array("$mobile");
    $pattern_code = "7yev7tayz3";
    $url = url('/');
    $input_data = array(
        'code' => $code,
        'url' => $url,
    );
    $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);
}

// Encode
function Encode($code)
{
    $code = \Vinkla\Hashids\Facades\Hashids::encode($code);
    return $code;
}

// Decode
function Decode($code)
{
    $hash = \Vinkla\Hashids\Facades\Hashids::decode($code);
    if ($hash == null) {
        return 'error';
    } else {
        return $hash[0];
    }
}

// User ID
function UserID()
{
    $id = \Illuminate\Support\Facades\Auth::guard('user')->user();
    return $id;
}

// SmsPva.com Api Method
function SmsPvaApiMethod($params)
{
    $apiUrl = 'http://smspva.com/priemnik.php?';
    $headers = array(
        'Content-Type: application/json',
    );
    $url = $apiUrl . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    return $result;
}

// Number Land Api Method
function SmsNumberLandMethod($params)
{
    $apiUrl = 'https://api.numberland.ir/v2.php/?';
    $headers = array(
        'Content-Type: application/json',
    );
    $url = $apiUrl . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    return $result;
}

// Convert Toman Price
function CovertDollarToTomanPrice($dollar)
{
    switch ($dollar) {
        case $dollar <= 0.50 && $dollar >= 0:
            return 20000;
            break;
        case $dollar <= 0.59 && $dollar >= 0.51 :
            return 23500;
            break;
        case $dollar <= 0.69 && $dollar >= 0.60:
            return 32000;
            break;
        case $dollar <= 0.79 && $dollar >= 0.70:
            return 36500;
            break;
        case $dollar <= 0.89 && $dollar >= 0.80:
            return 40000;
            break;
        case $dollar <= 0.99 && $dollar >= 0.90:
            return 50000;
            break;
        case $dollar <= 1.50 && $dollar >= 1:
            return 75000;
            break;
        case $dollar >= 2 && $dollar >= 1.51:
            return 85000;
            break;
        case $dollar >= 3 && $dollar >= 2:
            return 110000;
            break;
        case $dollar >= 4 && $dollar >= 3:
            return 150000;
            break;


        default:
            return "error";
    }

}

// Show Product Price
function ShowProductPrice($product)
{
    $Price = json_decode($product->price, true);

    if ($product->pva_quantity != 0) {
        $Result['price'] = $Price['Pva_Toman_Price'];
        $Result['quantity'] = $product->pva_quantity;
        return $Result;
    } elseif ($product->land_quantity != 0) {
        $Result['price'] = $Price['Land_Toman_Price'] * 2;
        $Result['quantity'] = $product->land_quantity;
        return $Result;
    } else {
        $Result['price'] = $Price['Pva_Toman_Price'];
        $Result['quantity'] = $product->pva_quantity;
        return $Result;
    }

}

// Send To Bank
function SendToBankZarinPal($Order)
{
    $Order = json_encode($Order);
    $headers = array(
        'Content-Type: application/json',
    );
    $params =
        [
            'order' => $Order,
        ];

    $url = "http://ashkeashura.ir/api/SendToBankWithApi";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
//    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $result = curl_exec($ch);
    return $result;
}

// Admin Order Status
function AdminOrderStatus($status)
{
    if ($status == 'complete') {
        echo '<span class="badge badge-light-success">   ' . __('order.status.' . $status) . ' </span>';
    } elseif ($status == 'Waiting to pay' || $status == 'waiting to pay') {
        echo '   <span class="badge badge-light-warning ">  ' . __('order.status.' . $status) . '</span>';
    } elseif ($status == 'progressing') {
        echo '   <span class="badge badge-light-info "> ' . __('order.status.' . $status) . ' </span>';
    } elseif ($status == 'failed' || $status == 'Error' || $status == 'back money to wallet') {
        echo '  <span class="badge badge-light-danger">  ' . __('order.status.' . $status) . '  </span>';
    } elseif ($status == 'Pending' || $status == 'pending') {
        echo '<span class="badge badge-light-primary"> ' . __('order.status.' . $status) . '  </span>';
    } elseif ($status == 'suspended') {
        echo '<span style="background-color: #a03aff" class="badge"> ' . __('order.status.' . $status) . '  </span>';
    }
}

// User Order Status
function UserOrderStatus($status)
{
    if ($status == 'complete') {
        echo '<span class="badge bg-success m-0">   ' . __('order.status.' . $status) . ' </span>';
    } elseif ($status == 'Waiting to pay' || $status == 'waiting to pay') {
        echo '   <span class="badge bg-warning m-0 ">  ' . __('order.status.' . $status) . '</span>';
    } elseif ($status == 'progressing') {
        echo '   <span class="badge bg-info m-0 "> ' . __('order.status.' . $status) . ' </span>';
    } elseif ($status == 'failed' || $status == 'Error' || $status == 'back money to wallet') {
        echo '  <span class="badge bg-danger m-0">  ' . __('order.status.' . $status) . '  </span>';
    } elseif ($status == 'Pending' || $status == 'pending') {
        echo '<span class="badge bg-primary m-0"> ' . __('order.status.' . $status) . '  </span>';
    } elseif ($status == 'suspended') {
        echo '   <span style="background-color: #a03aff" class="badge m-0 "> ' . __('order.status.' . $status) . ' </span>';
    }
}

// Admin Payment Status
function AdminOrderPayStatus($status)
{
    if ($status == 'Complete') {
        echo '<span class="badge badge-light-success">   ' . __('payments.status.' . $status) . ' </span>';
    } elseif ($status == 'Waiting to pay' || $status == 'waiting to pay') {
        echo '   <span class="badge badge-light-warning ">  ' . __('payments.status.' . $status) . '</span>';
    } elseif ($status == 'Progressing') {
        echo '   <span class="badge badge-light-info "> ' . __('payments.status.' . $status) . ' </span>';
    } elseif ($status == 'failed' || $status == 'Error') {
        echo '  <span class="badge badge-light-danger">  ' . __('payments.status.' . $status) . '  </span>';
    } elseif ($status == 'Pending') {
        echo '<span class="badge badge-light-primary"> ' . __('payments.status.' . $status) . '  </span>';
    }
}


function UserPaymentStatus($status)
{
    if ($status == 'Complete') {
        echo '<span class="badge bg-success">   ' . __('payments.status.' . $status) . ' </span>';
    } elseif ($status == 'Waiting to pay' || $status == 'waiting to pay') {
        echo '   <span class="badge bg-warning">  ' . __('payments.status.' . $status) . '</span>';
    } elseif ($status == 'Progressing') {
        echo '   <span class="badge bg-info"> ' . __('payments.status.' . $status) . ' </span>';
    } elseif ($status == 'failed' || $status == 'Error') {
        echo '  <span class="badge bg-danger">  ' . __('payments.status.' . $status) . '  </span>';
    } elseif ($status == 'Pending') {
        echo '<span class="badge bg-info"> ' . __('payments.status.' . $status) . '  </span>';
    } elseif ($status == '-3' || $status == '-4') {
        echo '<span class="badge bg-primary"> ' . __('payments.status.' . $status) . '  </span>';
    }
}

// Files of services
function Filed($product)
{
    $ProductFileds = json_decode($product->filds, true);

    $Result = '<div class="row">';
    foreach ($ProductFileds as $Filed) {

        if ($Filed['type'] == 'select_box') {
            $values = explode(',', $Filed['select_value']);
            $Result .= '
            <div class="col-' . $Filed['col'] . '">

            </div>
            ';
            return $Filed;
        } else {
            $Result .= '
            <div class="col-' . $Filed['col'] . '">

            </div>
            ';

        }


    }
    $Result .= '</div>';


    echo $Result;
}

// Check Test
function Test()
{

    set_time_limit(30000);

    $VirtualCode = \Illuminate\Support\Facades\DB::table('products')->where('type', 'Virtual-Number')->get();
//    foreach ($VirtualCode as $code) {
//
//        $detail = json_decode($code->data);
//        $ch = curl_init();
//        $address = "http://smspva.com/priemnik.php?metod=get_service_price&country=$detail->country_code&service=$detail->service_code&apikey=" . config('services.smspva.legacy_key');
//        curl_setopt($ch, CURLOPT_URL, $address);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        $export = json_decode(curl_exec($ch), true);
//        curl_close($ch);
//        if (is_array($export) && $export['response'] == 1) {
//
//            $detail = json_encode([
//                'service_id' => $detail->service_id,
//                'country_id' => $detail->country_id,
//                'service_code' => $detail->service_code,
//                'country_code' => $detail->country_code,
//                'dollar_price' => $export['price'],
//                'toman_price' => CovertDollarToTomanPrice($export['price']),
//                'En_name' => $detail->En_name,
//                'Fa_name' => $detail->Fa_name,
//                'service_image' => $detail->service_image,
//                'country_image' => $detail->country_image
//            ]);
//            $update = \Illuminate\Support\Facades\DB::table('products')->where('id', $code->id)->update([
//                'data' => $detail,
//                'status' => 1,
//            ]);
//
//
//        } else {
//            $update = VirtualCode::find($code->id);
//            $update->status = 0;
//            $update->save();
//        }
//    }

    foreach ($VirtualCode as $code) {

        $detail = json_decode($code->data);
        $address = "http://smspva.com/priemnik.php?metod=get_count_new&country=$detail->country_code&service=$detail->service_code&apikey=" . config('services.smspva.legacy_key') . "";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $address);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $export = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!is_null($export)) {
            $update = \Illuminate\Support\Facades\DB::table('products')->where('id', $code->id)
                ->update([
                    'count' => $export['online'],
                    'status' => 1
                ]);

        } else {
            $update = \Illuminate\Support\Facades\DB::table('products')->where('id', $code->id)
                ->update([
                    'status' => 0
                ]);
        }
    }
}

// Ticket Status
function TicketStatus($status)
{
    if ($status == 'Admin-answer') {
        echo '<span class="badge light badge-success">   ' . __('all.tickets.' . $status) . ' </span>';
    } elseif ($status == '') {
        echo ' <span class="badge light badge-warning ">  ' . __('all.tickets.' . $status) . '</span>';
    } else {
        echo ' <span class="badge light badge-pill badge-info "> ' . __('all.tickets.' . $status) . ' </span>';
    }
}

// Ticket Status
function UserTicketStatus($status)
{
    if ($status == 'Admin-answer') {
        echo '<span class="badge bg-success">   ' . __('all.tickets.' . $status) . ' </span>';
    } elseif ($status == 'User-answer') {
        echo ' <span class="badge bg-info">  ' . __('all.tickets.' . $status) . '</span>';
    } elseif ($status == 'User-Ticket') {
        echo ' <span class="badge bg-warning"> ' . __('all.tickets.' . $status) . ' </span>';
    } elseif ($status == 'close') {
        echo ' <span class="badge bg-danger"> ' . __('all.tickets.' . $status) . ' </span>';
    } elseif ($status == 'progressing') {
        echo ' <span class="badge bg-info"> ' . __('all.tickets.' . $status) . ' </span>';
    }
}

// Send Code From Sms
function SendVerifySms($code, $mobile)
{
    $client = new \SoapClient('http://188.0.240.110/class/sms/wsdlservice/server.php?wsdl');
//    $user = "mahdi-930";
//    $pass = "persianeagle_930";
//    $fromNum = "+9850005040858085";
    $fromNum = "+3000505";
    $toNum = array("$mobile");
//    $pattern_code = "7yev7tayz3";
    $url = url('/');
    $user = "mojtaba.gharegoli";
    $pass = "Aa1050474821";
    $pattern_code = "sek4lhwdjppq2ry";

    $input_data = array(
        "code" => "$code",
    );
    return $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);
}

// Custom Fileds
function GenerateHtmlFilds($Filed)
{
    $ProductTable = null;
    if ($Filed['type'] == 'select_box') {
        $values = explode(',', $Filed['select_value']);
        $ProductTable .= '
                            <div class="col-' . $Filed['col'] . '">
                                <label> ' . $Filed['title'] . ' </label> <br>
                                <input type="hidden" name="filed_name[]" value="' . $Filed['filed_name'] . '">
                                <input type="hidden" name="filed_title[]" value="' . $Filed['title'] . '">
                                 <select name="filed_value[]" id="" class="form-control  ml-3 mb-2">';
        foreach ($values as $keys) {
            $ProductTable .= ' <option value="' . $keys . '"> ' . $keys . '  </option> ';
        }
        $ProductTable .= '</select>
                         </div>  ';
    }
    if ($Filed['type'] == 'text') {

        $ProductTable .= '
                                <div class="col-' . $Filed['col'] . '">
                                      <label> ' . $Filed['title'] . ' </label> <br>
                                      <input type="hidden" name="filed_name[]" value="' . $Filed['filed_name'] . '">
                                     <input type="hidden" name="filed_title[]" value="' . $Filed['title'] . '">
                                     <input type="text" name="filed_value[]"  class="form-control ml-3 mb-2">
                                </div> ';
    }
    return $ProductTable;
}

// Add Seo
function Add_Seo($request)
{
    $Seo = [
        'meta_title' => $request->input('meta_title'),
        'meta_desc' => $request->input('meta_desc'),
        'meta' => $request->input('seo'),
        'meta_image' => $request->input('SeoImage'),
    ];
    return $Seo;
}

// Add Faq
function Add_Faq($request)
{
    $faq = null;
    if ($request->has('question')) {
        foreach ($request->input('question') as $key => $value) {
            if ($value != null) {
                $faq[] = [
                    'question' => $value,
                    'answer' => $request->input('answer')[$key]
                ];
            }
        }
    }
    return $faq;
}

// Send Telegram Message
function SendTelegramMessage($message, $Chatid = -890336339)
{
    if (DevFakeProviders()) { // local development: log instead of posting to the admin channel
        \Illuminate\Support\Facades\Log::info("[dev] Telegram to $Chatid: $message");
        return null;
    }
    $apiToken = config('services.telegram.token');
    $data = [
        'chat_id' => $Chatid,
        'text' => $message,
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

// Round Virtual Number Price
function CovertVirtualNumberPrice($price)
{
    $count = strlen($price) - 2;
    $price_check = substr($price, $count, 2);
    if ($price_check >= 50) {
        $price = substr($price, 0, $count) . '50';
    } else {
        $price = substr($price, 0, $count) . '00';
    }
    return $price;
}

// Convert Persian Numbers to English
function PersianNumToEn($input)
{
    $persian = ['۰', '۱', '۲', '۳', '۴', '٤', '۵', '٥', '٦', '۶', '۷', '۸', '۹'];
    $english = [0, 1, 2, 3, 4, 4, 5, 5, 6, 6, 7, 8, 9];
    return str_replace($persian, $english, $input);
}

// Decimals of Number
function countDecimals($fNumber)
{
    $fNumber = floatval($fNumber);
    for ($iDecimals = 0; $fNumber != round($fNumber, $iDecimals); $iDecimals++) {
        $Num = round($fNumber, $iDecimals);
        if ($Num > 0) {
            $iDecimals = $iDecimals + 2;
            return $iDecimals;
        }
    }
    return $iDecimals;
}

// قیمت فروش دلار
function CalculatorSellPrice($item)
{
    $Currency = App\Models\Currency::find($item->type);
    if ($item->buy != 0) {
        if ($item->sell_cal == 'static') {
            $CurrencyPrice_Sell = $Currency->sell + $item->sell;
            return $CurrencyPrice_Sell;
        } else {
            $CurrencyPrice_Sell = $Currency->sell / 100;

            if ($item->sell_action == false) {
                $CurrencyPrice_Sell = $Currency->sell - ($CurrencyPrice_Sell * $item->sell);
            } else {
                $CurrencyPrice_Sell = $Currency->sell + ($CurrencyPrice_Sell * $item->sell);
            }
        }
    } else {
        $CurrencyPrice_Sell = $Currency->sell;
    }
    return $CurrencyPrice_Sell;
}

// قیمت خرید دلار
function CalculatorBuyPrice($item)
{
    $Currency = App\Models\Currency::find($item->type);
    if ($item->buy != 0) {
        if ($item->buy_cal == 'static') {
            $CurrencyPrice_Buy = $Currency->buy - $item->buy;
        } else {
            $CurrencyPrice_Buy = $Currency->buy / 100;
            if ($item->buy_action == false) {
                $CurrencyPrice_Buy = $Currency->buy - ($CurrencyPrice_Buy * $item->buy);
            } else {
                $CurrencyPrice_Buy = $Currency->buy + ($CurrencyPrice_Buy * $item->buy);
            }
        }
    } else {
        $CurrencyPrice_Buy = $Currency->buy;
    }
    return intval($CurrencyPrice_Buy);
}


function ZarinPalMethod($url, $data)
{
    if (DevFakeProviders()) {
        return DevFakeZarinPal($url, $data);
    }
    $url = $url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'accept: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response = json_decode($response, true);
}

/* ساخت لینک عکس */
function GenerateImageUrl($url)
{
    $env = env('APP_URL');
    $url = str_replace('http://localhost:8000/', '', $url);
    $url = str_replace($env, '', $url);
    return $url;
}

/*  */
function AddUser($data, $ServerIp)
{

    $headers[] = 'Content-Type: application/json';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ServerIp . "api/addUser");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = json_decode(curl_exec($ch), true);
    return $response;
}

/*  */
function Updatev2Ray($data, $ServerIp)
{

    $headers[] = 'Content-Type: application/json';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ServerIp . "api/changeExpirationDate");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = json_decode(curl_exec($ch), true);
    return $response;
}

/*  */
function UpdateVolume($data, $ServerIp)
{

    $headers[] = 'Content-Type: application/json';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ServerIp . "api/changeUserTraffic");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = json_decode(curl_exec($ch), true);
    return $response;
}

/*  */
function DeleteUser($data, $ServerIp)
{
    $headers[] = 'Content-Type: application/json';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ServerIp . "api/deleteUser");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = json_decode(curl_exec($ch), true);
    return $response;

}

/*  */
function ResetV2Ray()
{
    $Ips = DB::table('ips')->where('type', 'public v2ray')->get();
    foreach ($Ips as $ip) {
        $headers[] = 'Content-Type: application/json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ip->domain . "api/resetTrafficForALL");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['secret' => 'sec']));
        $response = json_decode(curl_exec($ch), true);
        return $response;
    }
}

/* دریافت اطلاعات حجم */
function GetData($data, $ServerIp)
{
    $headers[] = 'Content-Type: application/json';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ServerIp . "api/usedTraffic");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = json_decode(curl_exec($ch), true);
    return $response;
}

/* Generate Code */
function GenerateApiKey()
{
    $length = 5;
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    $User = \App\User::where('token', $randomString)->first();
    if (is_null($User)) {
        return $randomString;
    } else {
        return GenerateApiKey();
    }
}

/* بررسی کد رفرال */
function CheckRef($type, $order, $user_id, $percent)
{
    $Orders = \App\Models\Order::where('user_id', $user_id->id)->where('status', 'complete')->get();
    if ($user_id->parent_id != 0) {
        $RefUser = \App\User::find($user_id->parent_id);
        if (count($Orders) >= 1) {
            $percent = 5;
        }
        if ($RefUser->id == 10) {
            $percent = 5;
        };

        $Price = $order->price / 100;
        $Price = $Price * $percent;

        if (!is_null($RefUser)) {
            $Help = "پورسانت سفارش شماره: $order->id";
            $Payment = new \App\Models\Payment();
            $Payment->price = $Price;
            $Payment->type = 5;
            $Payment->order_id = $order->id;
            $Payment->user_id = $RefUser->id;
            $Payment->status = 'Complete';
            $Payment->creator = 'System';
            $Payment->help = $Help;
            $Payment->save();

            $RefUser->blocked_wallet = $RefUser->blocked_wallet + $Price;
            $RefUser->save();
        }
    }

}

/* */
function CheckWallet()
{
    $Wallet = \Illuminate\Support\Facades\Auth::guard('user')->user()->wallet + \Illuminate\Support\Facades\Auth::guard('user')->user()->blocked_wallet;
    return $Wallet;
}

/* */
// Spend blocked_wallet first, then wallet. Runs on a locked row so concurrent purchases can't overwrite each other.
function DecrementWallet($Price, $User)
{
    \Illuminate\Support\Facades\DB::transaction(function () use ($Price, $User) {
        $User = \App\User::lockForUpdate()->find($User->id);
        $Price = (float)$Price;
        $Blocked = (float)$User->blocked_wallet;

        if ($Price <= $Blocked) {
            $User->blocked_wallet = $Blocked - $Price;
        } else {
            $User->wallet = $User->wallet - ($Price - $Blocked);
            $User->blocked_wallet = 0;
        }
        $User->save();
    });
}

// One wallet purchase at a time per user: CheckWallet() and DecrementWallet() are seconds apart
// (provider calls in between), so parallel requests could otherwise all pass the balance check.
function WalletPurchaseLock($UserId)
{
    return \Illuminate\Support\Facades\Cache::lock('wallet-purchase:' . $UserId, 60);
}

/* */
function SiteMap($Posts, $Filename)
{
    $data = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($Posts as $blog) {
        $Date = \Carbon\Carbon::parse($blog->created_at)->format('Y-m-d');
        $Time = \Carbon\Carbon::parse($blog->created_at)->format('H:i:s');
        $data .= '<url>
                <loc>' . secure_url('blog/' . $blog->slug) . '</loc>
                <lastmod>' . $Date . '</lastmod>
                <changefreq>yearly</changefreq>
</url>';
    }
    $data .= '</urlset>';


    file_put_contents("$Filename", "");
    file_put_contents("$Filename", $data);
}

/* Sms Verify */
function SendVerifySmsSmsIr($mobile = '9302736430', $code = 11111)
{
    if (DevFakeProviders()) { // local development: log instead of texting
        \Illuminate\Support\Facades\Log::info("[dev] SMS code for $mobile: $code");
        return null;
    }
    $Params = [
        'mobile' => "$mobile",
        'templateId' => 880532,
        'parameters' =>
            [
                [
                    'name' => 'CODE',
                    'value' => "$code"
                ],
                [
                    'name' => 'CODE2',
                    'value' => "#$code"
                ]
            ]
    ];
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.sms.ir/v1/send/verify',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($Params),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept: text/plain',
            'x-api-key: ' . config('services.smsir.key') . ''
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

/* Get V2ray */
function Getv2Rayv2($data, $ServerIp)
{
    $headers[] = 'Content-Type: application/json';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ServerIp . "/api/changeUser");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = json_decode(curl_exec($ch), true);
    return $response;
}

/* Log in to Sanaie */
function loginToSanaeiAccount($Data)
{
    $url = $Data['ip_api_domain'];
    $port = $Data['ip_port'];
    $ip_user = $Data['ip_user'];
    $ip_password = $Data['ip_password'];

    $headers[] = 'Content-Type: application/json';
    $data = json_encode(
        [
            'username' => $ip_user,
            'password' => $ip_password,
        ]
    );
    if ($port == 80 || $port == 443) {
        $url = $url . "/login";
    } else {
        $url = $url . ':' . $port . "/login";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
    $cookies = array();
    foreach ($matches[1] as $item) {
        parse_str($item, $cookie);
        $cookies = array_merge($cookies, $cookie);
    }

    $res = json_decode($body, true);
    if (is_array($res) && array_key_exists('success', $res) && $res['success'] == true) {
        if (array_key_exists('session', $cookies)) {
            $myData['sessionCookie'] = $cookies['session'];
        } elseif (array_key_exists('3x-ui', $cookies)) {
            $myData['sessionCookie'] = $cookies['3x-ui'];
        }
        $myData['login'] = true;
    } else {
        $myData['sessionCookie'] = false;
        $myData['login'] = false;
    }

    return $myData;
}

/* addClientToSanaie */
function addClientToSanaie($data)
{
    $Time = array_key_exists('time', $data) ? $data['time'] : 0;
    $Hajm = array_key_exists('hajm', $data) ? $data['hajm'] : 0;
    $Session = $data['Session'];
    $name = $data['name'];
    if (array_key_exists('method', $data)) {
        $url = $data['apiUrl'];
        $serverUrl = $data['serverUrl'];
        $inboundId = $data['inboundId'];
        $inboundPort = $data['inboundPort'];
    } else {
        $url = $data['api_domain'];
        $port = $data['ip_port'];
        $inboundId = $data['ip_inbound'];
        $inboundPort = $data['port_inbound'];
        $serverUrl = $data['ip_address'];
    }


    if (array_key_exists('code', $data)) {
        $code = $data['code'];
    }

    $UID = generateUid();
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url . '/panel/api/inbounds/addClient',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{"id": ' . $inboundId . ',
"settings": "{\\"clients\\":[{\\"id\\":\\"' . $UID . '\\",\\"alterId\\":0,\\"email\\":\\"' . $name . '\\",\\"limitIp\\":0,\\"totalGB\\":' . $Hajm . ',\\"expiryTime\\":' . $Time . ',\\"enable\\":true,\\"tgId\\":\\"\\",\\"subId\\":\\"\\"}]}"
}',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            "session=$Session",
            "Cookie: session=$Session",
            "Cookie: 3x-ui=$Session",
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    $url = $serverUrl;
    $response = json_decode($response, true);

    if ($response['success'] == true) {
        if (isset($code)) {
            if ($data['type'] == 'vless') {

                $code = str_replace("UID", $UID, $code);
                $code = str_replace("URL", $serverUrl, $code);
                $code = str_replace("INBOUNDPORT", $inboundPort, $code);
                $code = str_replace("NAME", $name, $code);

                $Status['status'] = 1;
                $Status['msg'] = $code;
                $Status['UID'] = $UID;
            }
        } else {
            $port = str_replace(":$port", '', $url);
            $Ip = "vless://$UID@$serverUrl:$inboundPort?type=tcp&path=/&host=ponisha.ir&headerType=http&security=tls&fp=firefox&alpn=h2,http/1.1&allowInsecure=1#$name";
            $Status['status'] = 1;
            $Status['msg'] = $Ip;
            $Status['UID'] = $UID;
        }
        return $Status;
    } else {
        $Status['status'] = -1;
        $Status['msg'] = $response;
        return $Status;
    }
}


function generateCode($data)
{

    $code = $data['code'];
    $UID = $data['UID'];
    $serverUrl = $data['serverUrl'];
    $inboundPort = $data['inboundPort'];
    $name = $data['name'];

    if ($data['type'] == 'vless') {

        $code = str_replace("UID", $UID, $code);
        $code = str_replace("URL", $serverUrl, $code);
        $code = str_replace("INBOUNDPORT", $inboundPort, $code);
        $code = str_replace("NAME", $name, $code);

        $Status['status'] = 1;
        $Status['msg'] = $code;
        $Status['UID'] = $UID;
    } else {

        $Ip = "vless://$UID@$serverUrl:$inboundPort?type=tcp&path=/&host=ponisha.ir&headerType=http&security=tls&fp=firefox&alpn=h2,http/1.1&allowInsecure=1#$name";
        $Status['status'] = 1;
        $Status['msg'] = $Ip;
        $Status['UID'] = $UID;
    }
    return $Status;
}

function addClientToSanaieOnline($data)
{
    $Time = $data['zaman'];
    $Hajm = $data['hajm'];
    $url = $data['api_domain'];
    $port = $data['ip_port'];
    $Session = $data['Session'];
    $name = $data['name'];
    $inboundId = $data['ip_inbound'];
    $inboundPort = $data['port_inbound'];
    $type = $data['type'];


    $UID = generateUid();

    if ($port == 80 || $port == 443) {
        $url = $url;
    } else {
        $url = $url . ':' . $port;
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url . '/panel/api/inbounds/addClient',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{"id": ' . $inboundId . ',
"settings": "{\\"clients\\":[{\\"id\\":\\"' . $UID . '\\",\\"alterId\\":0,\\"email\\":\\"' . $name . '\\",\\"limitIp\\":0,\\"totalGB\\":' . $Hajm . ',\\"expiryTime\\":' . $Time . ',\\"enable\\":true,\\"tgId\\":\\"\\",\\"subId\\":\\"\\"}]}"
}',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            "session=$Session",
            "Cookie: session=$Session",
            "Cookie: 3x-ui=$Session",
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);

    $url = $data['ip_address'];
    $response = json_decode($response, true);

    if ($response['success'] == true) {
        $port = str_replace(":$port", '', $url);
        $Ip = "vless://$UID@$port:$inboundPort?type=tcp&path=/&host=ponisha.ir&headerType=http&security=tls&fp=firefox&alpn=h2,http/1.1&allowInsecure=1#$name";
        $Status['status'] = 1;
        $Status['msg'] = $Ip;
        $Status['UID'] = $UID;

        return $Status;
    } else {
        $Status['status'] = -1;
        $Status['msg'] = $response;
        return $Status;
    }
}

/*  */
function generateUid()
{
    $Numbers = rand(1111, 9999);
    $Numbers2 = rand(1111, 9999);
    $UID = "95e4e8bb-$Numbers-47e7-e8a7-f4123222$Numbers2";
    $check = DB::table('ips')->where('UID', $UID)->first();
    if (is_null($check)) {
        return $UID;
    } else {
        return generateUid();
    }
}

/* update client from sanaie panel */
function updateSanaiePanel($data)
{

    $Time = $data['time'];
    $Hajm = $data['hajm'];
    $url = $data['ip_address'];
    $port = $data['ip_port'];
    $Session = $data['Session'];
    $name = $data['name'];
    $UID = $data['uid'];
    $apiDomain = $data['ip_api_domain'];
    $Inbound = $data['Data']['ip_inbound'];

    if ($port == 80 || $port == 443) {
        $apiDomain = $apiDomain;
    } else {
        $apiDomain = $apiDomain . ':' . $port;
    }

    $apiDomain = $apiDomain . "/panel/api/inbounds/updateClient/$UID";
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $apiDomain,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{"id": ' . $Inbound . ',
"settings": "{\\"clients\\":[{\\"id\\":\\"' . $UID . '\\",\\"alterId\\":0,\\"email\\":\\"' . $name . '\\",\\"limitIp\\":0,\\"totalGB\\":' . $Hajm . ',\\"expiryTime\\":' . $Time . ',\\"enable\\":true,\\"tgId\\":\\"\\",\\"subId\\":\\"\\"}]}"
}',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            "session=$Session",
            "Cookie: session=$Session",
            "Cookie: 3x-ui=$Session",
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response, true);

    if ($response['success'] == true) {
        $Status['status'] = 1;
        return $Status;
    } else {
        $Status['status'] = -1;
        $Status['msg'] = $response;
        return $Status;
    }
}

function getSanaieInboundsClient($data)
{

    $url = $data['ip_address'];
    $port = $data['ip_port'];
    $Session = $data['session'];
    $apiDomain = $data['ip_api_domain'];
    $Inbound = $data['ip_inbound'];

    if ($port == 80 || $port == 443) {
        $apiDomain = $apiDomain;
    } else {
        $apiDomain = $apiDomain . ':' . $port;
    }

    $apiDomain = $apiDomain . "/panel/api/inbounds/get/$Inbound";
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $apiDomain,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            "session=$Session",
            "Cookie: session=$Session",
            "Cookie: 3x-ui=$Session",
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response, true);

    if ($response['success'] == true) {
        $Status['status'] = 1;
        $Status['response'] = $response;
        return $Status;
    } else {
        $Status['status'] = -1;
        $Status['msg'] = $response;
        return $Status;
    }
}

/* Get used Traffic by user */
function getClientTraffics($data)
{

    $url = $data['ip_address'];
    $port = $data['ip_port'];
    $Session = $data['Session'];
    $name = $data['name'];
    $UID = $data['uid'];
    $apiDomain = $data['ip_api_domain'];
    $Inbound = $data['Data']['ip_inbound'];


    if ($port == 80 || $port == 443) {
        $url = $url;
    } else {
        $url = $url . ':' . $port;
    }

    $Url = $url . '/panel/api/inbounds/getClientTraffics/' . $name;

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $Url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            "session=$Session",
            "Cookie: session=$Session",
            "Cookie: 3x-ui=$Session",
        ),
    ));
    $response = curl_exec($curl);
    $Data = json_decode($response, true);
    if (is_array($Data) && array_key_exists('success', $Data) && $Data['success'] == true) {
        if (!is_null($Data['obj'])) {
            $Total = $Data['obj']['up'] + $Data['obj']['down'];

            $Total = $Total * 0.00000095367432;
            $Data['success'] = true;
            $Data['hajm'] = $Total;
        } else {
            $Data['success'] = false;

        }
    } else {
        $Data['success'] = false;
    }
    return $Data;
}

/* Send Sms */
function sendSmsWithSmsir($mobile, $Data, $template)
{
    if (DevFakeProviders()) { // local development: log instead of texting
        \Illuminate\Support\Facades\Log::info("[dev] SMS template $template for $mobile: " . json_encode($Data));
        return null;
    }
    $param = null;
    foreach ($Data as $key => $value) {
        $param[] =
            [
                'name' => "$key",
                'value' => "$value"
            ];
    }

    $Params = [
        'mobile' => "$mobile",
        'templateId' => $template,
        'parameters' => $param
    ];


    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.sms.ir/v1/send/verify',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($Params),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept: text/plain',
            'x-api-key: ' . config('services.smsir.key') . ''
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;

}

/* Partner */
function checkPartnerProfit($ServiceId, $Payment, $buyPrice = null)
{
    $PartnerServices = PartnerServices::where('service_id', $ServiceId)->pluck('partner_id')->toArray();
    $Partners = Partner::wherein('id', $PartnerServices)->get();
    foreach ($Partners as $partner) {
        if ($partner->status == 1) {
            if ($partner->percent_type == 1) {

                $Price = $Payment->price / 100;
                $Price = $Price * $partner->percent;

                $updatePartner = Partner::find($partner->id);
                $updatePartner->wallet = $updatePartner->wallet + $Price;
                $updatePartner->save();

            } elseif ($partner->percent_type == 2) {
                $price = $Payment->price - $buyPrice;

                $Price = $price / 100;
                $Price = $Price * $partner->percent;

                $updatePartner = Partner::find($partner->id);
                $updatePartner->wallet = $updatePartner->wallet + $Price;
                $updatePartner->save();
            }
        }
    }
}

/*  */
function FactorProfit($Data)
{
    $OrderId = $Data['orderId'];
    $PaymentId = $Data['paymentId'];
    $userId = $Data['userId'];
    $serviceType = $Data['serviceType'];
    $paymentType = $Data['paymentType'];
    $PaymentAmount = $Data['amount'];
    $buyPrice = $Data['buyPrice'];

    // No buy price: an active partner on this service takes their percent. With none, the whole payment is profit.
    $partner = $buyPrice == 0
        ? Partner::wherein('id', PartnerServices::where('service_id', $serviceType)->pluck('partner_id'))->where('status', 1)->first()
        : null;
    if ($partner) {
        $buyPrice = $PaymentAmount / 100;
        $buyPrice = $buyPrice * $partner->percent;
        $profitPrice = $PaymentAmount - $buyPrice;

        $PartnerProfit = new PartnerProfit();
        $PartnerProfit->partner_id = $partner->id;
        $PartnerProfit->order_id = $OrderId;
        $PartnerProfit->payment_id = $PaymentId;
        $PartnerProfit->service_type = $serviceType;
        $PartnerProfit->payment_type = $paymentType;
        $PartnerProfit->order_price = $PaymentAmount;
        $PartnerProfit->profit = $buyPrice;
        $PartnerProfit->status = 1;
        $PartnerProfit->save();

        $partner->wallet = $partner->wallet + $buyPrice;
        $partner->save();


    } else {
        $profitPrice = $PaymentAmount - $buyPrice;
    }

    $Profit = new OrderProfit();
    $Profit->user_id = $userId;
    $Profit->order_id = $OrderId;
    $Profit->payment_id = $PaymentId;
    $Profit->service_type = $serviceType;
    $Profit->payment_type = $paymentType;
    $Profit->order_price = $PaymentAmount;
    $Profit->buy_price = $buyPrice;
    $Profit->profit = $profitPrice;
    $Profit->save();
}

function jalali_to_gregorian($date)
{
    $jdf = new \App\lib\Jdf();
    $year = substr(convertPersianNumbersToEnglish($date), '0', '4');
    $month = substr(convertPersianNumbersToEnglish($date), '5', '2');
    $day = substr(convertPersianNumbersToEnglish($date), '8', '2');
    $convert_to_miladi = $jdf->jalali_to_gregorian($year, $month, $day, '-');
    return $convert_to_miladi;
}

function convertPersianNumbersToEnglish($input)
{
    $persian = ['۰', '۱', '۲', '۳', '۴', '٤', '۵', '٥', '٦', '۶', '۷', '۸', '۹'];
    $english = [0, 1, 2, 3, 4, 4, 5, 5, 6, 6, 7, 8, 9];
    return str_replace($persian, $english, $input);
}


function SendTelegramMessageForCustomBot($message, $apiToken)
{
    $curl = curl_init('https://api.telegram.org/bot' . $apiToken . '/sendMessage');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($message));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function ForwardTelegramMessageForCustomBot($message, $apiToken)
{
    $curl = curl_init('https://api.telegram.org/bot' . $apiToken . '/forwardMessage');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($message));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function EditTelegramMessageForCustomBot($message, $apiToken)
{
    $curl = curl_init('https://api.telegram.org/bot' . $apiToken . '/editMessageText');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($message));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function deleteTelegramMessageForCustomBot($message, $apiToken)
{
    $curl = curl_init('https://api.telegram.org/bot' . $apiToken . '/deleteMessage');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($message));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function sendPhotoTelegramMessageForCustomBot($message, $apiToken)
{
    $curl = curl_init('https://api.telegram.org/bot' . $apiToken . '/sendPhoto');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($message));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function BotOrderStatus($status)
{
    if ($status == 2) {
        echo '<span class="badge badge-light-success">  تکمیل شده </span>';
    } elseif ($status == 0) {
        echo '<span class="badge badge-light-warning "> درانتظار پرداخت </span>';
    } elseif ($status == 1) {
        echo '<span class="badge badge-light-success">  تکمیل شده </span>';
    } elseif ($status == -1) {
        echo '<span class="badge badge-light-danger"> لغو شده </span>';
    } elseif ($status == -2) {
        echo '<span class="badge badge-light-info"> معلق </span>';
    } elseif ($status == 'suspended') {
        echo '<span style="background-color: #a03aff" class="badge"> ' . __('order.status.' . $status) . '  </span>';
    }
}

function BotPaymentStatus($status)
{
    if ($status == 1) {
        echo '<span class="badge badge-light-success">  تکمیل شده </span>';
    } elseif ($status == 0) {
        echo '<span class="badge badge-light-warning "> درانتظار پرداخت </span>';
    } elseif ($status == -1) {
        echo '<span class="badge badge-light-danger"> لغو شده</span>';
    } elseif ($status == -2) {
        echo '<span class="badge badge-light-info"> عودت به کیف پول </span>';
    } elseif ($status == -3) {
        echo '<span class="badge badge-light-primary"> درانتظار کارت به کارت </span>';
    }
}

function sanaieCode($data, $Uid, $name)
{

    $id = $data['id'];
    $remark = $data['remark'];
    $enable = $data['enable'];
    $port = $data['port'];
    $protocol = $data['protocol'];

    $clients = json_decode($data['settings'], true)['clients'];
    $decryption = json_decode($data['settings'], true)['decryption'];
    $fallbacks = json_decode($data['settings'], true)['fallbacks'];

    $streamSettings = json_decode($data['streamSettings'], true);
    $sniffing = json_decode($data['sniffing'], true);
    $tag = $data['tag'];

    if ($protocol == 'vless') {
        $BaseLink = "vless://$Uid@";
        if ($streamSettings['network'] == 'tcp') {
            $params = '?type=tcp';
            if ($streamSettings['security'] == 'tls') {
                $params = $params . '&security=tls';
                $tlsSettings = $streamSettings['tlsSettings'];
                if (!is_null($tlsSettings['serverName']) && $tlsSettings['serverName'] != "") {
                    $mainLink = $BaseLink . '' . $tlsSettings['serverName'] . ":$port";
                }
                if ($tlsSettings['settings']['allowInsecure'] == true) {
                    $params = $params . '&allowInsecure=1';
                }
                if ($tlsSettings['settings']['fingerprint'] != null) {
                    $params = $params . '&fp=' . $tlsSettings['settings']['fingerprint'];
                }
                if (count($tlsSettings['alpn']) != 0) {
                    $params = $params . '&alpn=' . implode(',', $tlsSettings['alpn']);
                }
            }
            $tcpSettings = $streamSettings['tcpSettings'];
            if ($tcpSettings['header']['type'] != 'none') {
                $params = $params . '&headerType=http';

                if ($tcpSettings['header']['request']['path'][0] != '/') {
                    $params = $params . '&path=' . $tcpSettings['header']['request']['path'][0];
                } else {
                    $params = $params . '&path=/';
                }
                if (count($tcpSettings['header']['request']['headers']) != 0) {
                    $headers = $tcpSettings['header']['request']['headers'];
                    if (array_key_exists('Host', $headers)) {
                        $firstHeader = $headers['Host'][0];
                        $params = $params . '&host=' . $firstHeader;
                    }
                }
            }
        }

    }
    $link = $mainLink . '' . $params . '#' . $name;
    return $link;
}
