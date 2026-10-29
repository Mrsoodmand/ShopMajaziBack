<?php

/* Man */
function VirtualNumber_SmsManMethod($url)
{
    $headers = array(
        'Content-Type: application/json',
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);

    return $result;
}

/* Pva */
function VirtualNumber_SmsPvaMethod($Data)
{
    $apiUrl = 'https://smspva.com/priemnik.php?apikey=' . config('services.smspva.key') . '&';
//    $apiUrl = 'https://smspva.com/priemnik.php?apikey=' . config('services.smspva.key_alt');
    $headers = array(
        'Content-Type: application/json',
    );
    $url = $apiUrl . http_build_query($Data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    return $result;

}

/* Active */
function VirtualNumber_ActivePvaMethod($url)
{
    $headers = array(
        'Content-Type: application/json',
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    return $result;

}

/* Get Number From Pva */
function GetNumber_SmsPva($service_id, $country_id)
{
    if (DevFakeProviders()) {
        return DevFakeNumber('pva');
    }
    $params = [
        'metod' => 'get_number',
        'country' => $country_id,
        'service' => $service_id,
    ];

    $ResultOfNumber = json_decode(VirtualNumber_SmsPvaMethod($params), true);
    return $ResultOfNumber;
}

/* Get Number From Sms */
function GetNumber_SmsMan($service_id, $country_id)
{
    if (DevFakeProviders()) {
        return DevFakeNumber('man');
    }
    $Url = "http://api.sms-man.com/control/get-number?token=r-TO0zueoR8A7jAXD2fWj2KkbWbymRS2&country_id=$country_id&application_id=$service_id";
    $ResultOfNumber = json_decode(VirtualNumber_SmsManMethod($Url), true);
    return $ResultOfNumber;
}

/* Get Number From Active */
function GetNumber_Active($service_id, $country_id)
{
    if (DevFakeProviders()) {
        return DevFakeNumber('active');
    }
    $Url = "https://api.sms-activate.org/stubs/handler_api.php?api_key=" . config('services.sms_activate.key') . "&action=getNumberV2&service=$service_id&country=$country_id";
    $ResultOfNumber = json_decode(VirtualNumber_ActivePvaMethod($Url), true);
    return $ResultOfNumber;
}

/* Cancel Number */
function CancelNumber($Type, $NumberId, $service_id = null)
{
    if (DevFakeProviders()) {
        \Illuminate\Support\Facades\Cache::put("dev-fake-cancelled:$NumberId", true, 3600);
        return true;
    }
    $Cancel = false;
    if ($Type == 'pva') {
        $Data = [
            'metod' => ' ban',
            'service' => ' opt4',
            'id' => $NumberId,
        ];
        $Result = json_decode(VirtualNumber_SmsPvaMethod($Data), true);
        if (is_array($Result) && !is_null($Result) && $Result['response'] == 1) {
            $Cancel = true;
        }
    } elseif ($Type == 'man') {
        $Url = "http://api.sms-man.com/control/set-status?token=r-TO0zueoR8A7jAXD2fWj2KkbWbymRS2&request_id=$NumberId&status=reject";
        $Result = json_decode(VirtualNumber_SmsManMethod($Url), true);
        if (is_array($Result) && array_key_exists('success', $Result) && $Result['success'] == true) {

            $Cancel = true;
        }
    } elseif ($Type == 'active') {
        $Url = "https://api.sms-activate.org/stubs/handler_api.php?api_key=" . config('services.sms_activate.key') . "&action=setStatus&status=8&id=$NumberId";
        $Result = VirtualNumber_ActivePvaMethod($Url);
        if ($Result == 'ACCESS_CANCEL') {
            $Cancel = true;
        }
    }
    return $Cancel;
}

/* Get Sms From Pva */
function GetSmsFromPva($service_id, $country_id, $numberId)
{
    if (DevFakeProviders()) {
        $Code = DevFakeSms($numberId);
        return $Code ? ['response' => 1, 'sms' => $Code] : ['response' => 2];
    }
    $params = [
        'metod' => 'get_sms',
        'country' => $country_id,
        'service' => $service_id,
        'id' => $numberId,
    ];
    $ResultOfNumber = json_decode(VirtualNumber_SmsPvaMethod($params), true);
    return $ResultOfNumber;
}

/* Get Sms From Man */
function GetSmsFromMan($numberId){
    if (DevFakeProviders()) {
        return ['sms_code' => DevFakeSms($numberId)];
    }
    $Url = "http://api.sms-man.com/control/get-number?token=r-TO0zueoR8A7jAXD2fWj2KkbWbymRS2&request_id=$numberId";
    $ResultOfNumber = json_decode(VirtualNumber_SmsManMethod($Url), true);
    return $ResultOfNumber;
}

/* Get Sms From Active */
function GetSmsFromActive($numberId){
    if (DevFakeProviders()) {
        $Code = DevFakeSms($numberId);
        return $Code ? "STATUS_OK:$Code" : 'STATUS_WAIT_CODE';
    }
    $Url = "https://api.sms-activate.org/stubs/handler_api.php?api_key=" . config('services.sms_activate.key') . "&action=getStatus&id=$numberId";
    $ResultOfNumber = VirtualNumber_ActivePvaMethod($Url);
    return $ResultOfNumber;
}

/*
 * Local development only: with DEV_FAKE_PROVIDERS=true (set in .env.dev, never in production) the number providers,
 * SMS.ir and Telegram are replaced by these stand-ins, so buying can be tried without spending money or messaging
 * anyone. A fake number gets its code 20 seconds after it is issued.
 */
function DevFakeProviders(): bool
{
    return filter_var(env('DEV_FAKE_PROVIDERS', false), FILTER_VALIDATE_BOOLEAN);
}

function DevFakeNumber($Type)
{
    $Id = random_int(100000, 999999);
    \Illuminate\Support\Facades\Cache::put("dev-fake-number:$Id", time(), 3600);
    $Number = '49152' . random_int(1000000, 9999999);
    if ($Type == 'pva') {
        return ['response' => 1, 'CountryCode' => '+49', 'number' => substr($Number, 2), 'id' => $Id];
    }
    if ($Type == 'man') {
        return ['request_id' => $Id, 'number' => $Number];
    }
    return ['activationId' => $Id, 'phoneNumber' => $Number];
}

function DevFakeSms($NumberId)
{
    $IssuedAt = \Illuminate\Support\Facades\Cache::get("dev-fake-number:$NumberId");
    if (!$IssuedAt || time() - $IssuedAt < 20 || \Illuminate\Support\Facades\Cache::get("dev-fake-cancelled:$NumberId")) {
        return null;
    }
    return (string)(10000 + $NumberId % 90000);
}

/* Local stand-in for ZarinPal v4: request hands out an authority, verify succeeds once per authority. */
function DevFakeZarinPal($Url, $Data)
{
    $Cache = \Illuminate\Support\Facades\Cache::class;
    if (str_contains($Url, 'request.json')) {
        $Authority = 'DEV' . str_pad((string)random_int(0, 999999999), 33, '0', STR_PAD_LEFT);
        $Cache::put("dev-zarinpal:$Authority", ['amount' => $Data['amount'], 'callback' => $Data['callback_url']], 3600);
        return ['data' => ['code' => 100, 'message' => 'Success', 'authority' => $Authority, 'fee' => 0], 'errors' => []];
    }
    $Authority = $Data['authority'] ?? '';
    $Known = $Cache::get("dev-zarinpal:$Authority");
    if (!$Known || $Known['amount'] != $Data['amount']) {
        return ['data' => [], 'errors' => ['code' => -50, 'message' => 'Session is not valid, amounts values is not the same.']];
    }
    if ($Cache::get("dev-zarinpal-verified:$Authority")) {
        return ['data' => ['code' => 101, 'message' => 'Verified', 'ref_id' => 0, 'fee' => 0, 'card_pan' => '603799******1234'], 'errors' => []];
    }
    $Cache::put("dev-zarinpal-verified:$Authority", true, 3600);
    return ['data' => ['code' => 100, 'message' => 'Paid', 'ref_id' => random_int(100000000, 999999999), 'fee' => 0, 'card_pan' => '603799******1234'], 'errors' => []];
}

// Where the fake gateway page lives (Api\StorefrontAccountController@devBank); the authority is appended.
function DevFakeZarinPalPage(): string
{
    return StorefrontUrl('/api/v1/storefront/account/dev-bank/');
}

// The Next.js storefront's origin (STOREFRONT_URL; in production the site's own domain, APP_URL) plus `$Path`.
function StorefrontUrl($Path = ''): string
{
    return rtrim(env('STOREFRONT_URL', config('app.url')), '/') . $Path;
}

/*
 * Local stand-in for Numberland's special (Google Voice) numbers: a fixed list, and getspnumber hands out a fake
 * login once per number. Number id 90009 always fails (RESULT -990) and 90010 gives no clear answer, so the refund
 * and support-review paths can be tried.
 */
function DevFakeNumberLandSpecial($Params)
{
    $List = [
        [90001, '+1 (212) 555-0142', 240000, 0], [90002, '+1 (415) 555-0123', 240000, 0], [90003, '+1 (305) 555-0111', 260000, 0],
        [90004, '+1 (312) 555-0134', 240000, 0], [90005, '+1 (512) 555-0100', 600000, 1], [90006, '+1 (646) 555-0177', 600000, 1],
        [90007, '+1 (720) 555-0150', 730000, 2], [90008, '+1 (206) 555-0129', 250000, 0], [90009, '+1 (617) 555-0163', 240000, 0],
        [90010, '+1 (702) 555-0177', 240000, 0], [90011, '+1 (404) 555-0118', 250000, 0], [90012, '+1 (213) 555-0168', 1140000, 2],
    ];
    $Sold = \Illuminate\Support\Facades\Cache::get('dev-fake-sp-sold', []);
    $Comments = ['not round', 'half round', 'fully round'];
    if ($Params['method'] === 'spnumberslist') {
        $Left = array_filter($List, fn($n) => !in_array($n[0], $Sold));
        return ['RESULT' => 1, 'DESCRIPTION' => array_values(array_map(fn($n) => ['ID' => $n[0], 'NUMBER' => $n[1], 'PRICE' => $n[2], 'TYPE' => $n[3], 'COMMENT' => $Comments[$n[3]]], $Left))];
    }
    if ($Params['method'] === 'getspnumber') {
        $Id = (int)$Params['id'];
        $Found = current(array_filter($List, fn($n) => $n[0] === $Id));
        if (!$Found || in_array($Id, $Sold) || $Id === 90009) {
            return ['RESULT' => -990, 'DESCRIPTION' => 'number id invalid'];
        }
        if ($Id === 90010) {
            return ['oops' => true];
        }
        \Illuminate\Support\Facades\Cache::forever('dev-fake-sp-sold', [...$Sold, $Id]);
        return ['result' => 1, 'numbers' => [[
            'ID' => $Id, 'NUMBER' => $Found[1], 'PRICE' => $Found[2], 'USERNAME' => "gv.dev.$Id@example.com",
            'PASSWORD' => 'Dev-' . substr(md5((string)$Id), 0, 8), 'RECOVERY' => "recovery.$Id@example.com",
            'DATE' => '1405/8/5', 'TIME' => date('H:i:s'),
        ]]];
    }
    if ($Params['method'] === 'balance') {
        return ['RESULT' => 1, 'BALANCE' => (int)env('DEV_FAKE_NUMBERLAND_BALANCE', 50000000), 'CURRENCY' => 'Toman'];
    }
    return ['RESULT' => -902, 'DESCRIPTION' => 'method invalid'];
}
