<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ZibalController extends Controller
{
    protected $ZibalMerchentCode = '63d3055218f9342f181892e7';

    public function sendToZibal(Request $request)
    {
        $path = $request->input('url');
        $Payment = $request->input('paymentId');
        $CallBackURL = $request->input('callBackUrl');
        $parameters = [
            "merchant" => $this->ZibalMerchentCode,//required
            "callbackUrl" => $CallBackURL . "user/transactions/v2/call-back",//required
            "amount" => $request->input('price'),//required
            "orderId" => $request->input('paymentId'),//optional
            "mobile" => $request->input('userMobile'),//optional
            "percentMode" => 0,//optional
            "feeMode" => 0,//optional
            "description" => $request->input('detail'),//optional
        ];
        $url = 'https://gateway.zibal.ir/v1/' . $path;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);

        if ($response->result == 100) {

            $params = [
                'id' => $Payment,
                'trackId' =>  $response->trackId,
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://panel.ariapay.io/api/shop-majazi/updatePaymentId?' . http_build_query($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $Payment = Payment::find($Payment->id);
            $Payment->authority = $response->trackId;
            $Payment->save();


            $startGateWayUrl = "https://gateway.zibal.ir/start/" . $response->trackId;
            header('location: ' . $startGateWayUrl);
        } else {
            echo "result: " . $response->result . "<br>";
            echo "message: " . $response->message;
        }


    }
}
