<?php

namespace App\Http\Controllers;

use App\lib\Jdf;
use App\lib\Jibit;
use App\Models\BankHistory;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class BankApiController extends Controller
{

    public function stepOne(Request $request)
    {

        $paymentId = $request->paymentId;
        $mobile = $request->mobile;
        $amount = $request->amount;
        $site = $request->site;
        $callback = $request->callback;

        $history = new BankHistory();
        $history->mobile = $mobile;
        $history->amount = $amount;
        $history->callback_url = $callback;
        $history->payment_id = $paymentId;
        $history->site = $site;
        $history->status = 0;
        $history->save();

        $total_price = $amount;
        $id = $history->id;

        $merchent = '64aeaec5cbbc27000ec9327d';
        $parameters = array(
            "merchant" => $merchent,//required
            "callbackUrl" => url('/') . '/api/bank-api/step-three',//required
            "amount" => $total_price,//required
            "orderId" => $id,//optional
            "mobile" => $mobile,//optional
            "percentMode" => 0,//optional
            "feeMode" => 0,//optional
            "description" => "سفارش شماره: $id",//optional
        );
        $response = $this->postToZibal('request', $parameters);

        if ($response->result == 100) {
            //save trackId into database if you want or do something else
            $history->track_id = $response->trackId;
            $history->gate = 'zibal';
            $history->save();

            $startGateWayUrl = "https://gateway.zibal.ir/start/" . $response->trackId;
            return response()->json([
                'track_id' => $response->trackId,
                'id' => $history->id,
                'url' => $startGateWayUrl
            ], 200);

        } else {
            return exception_error($request, 'دوره مورد نظر یافت نشد.');
        }


    }

    public function stepTwo(Request $request)
    {
        $History = BankHistory::find($request->id);
        if (!is_null($History)) {
            if ($History->status == 0) {
                $History->status = 1;
                $History->save();

                $startGateWayUrl = "https://gateway.zibal.ir/start/" . $History->track_id;

                return redirect($startGateWayUrl);
            }
        }
    }

    public function stepThree(Request $request)
    {
        $lock = Cache::lock('Order-' . $_GET['orderId'], 60);
        $lock->release();
        if ($lock->get()) {
            $Orders = BankHistory::where('id', $_GET['orderId'])->first();
            if (isset($_GET['success']) && $_GET['success'] == 1) {
                try {
                    if (!is_null($Orders)) {

                        if ($Orders->status == 1) {

                            if (isset($_GET['trackId'])) {
                                $merchent = '64aeaec5cbbc27000ec9327d';
                                $Track_id = intval($_GET['trackId']);
                                $parameters = array(
                                    "merchant" => $merchent, //required
                                    "trackId" => $Track_id, //required
                                );
                                $response = $this->postToZibal('verify', $parameters);
                                if ($response->result == 100 ) {
                                    $price = $response->amount / 10;

                                    if (true) {

                                        $Orders->status = 2;
                                        $Orders->card = $response->cardNumber;
                                        $Orders->ref_id = $response->refNumber;
                                        $Orders->save();

                                        $Data = [
                                            'status' => 1,
                                            'ref_id' => $response->refNumber,
                                            'card' => $response->cardNumber,
                                            'payment_id' => $Orders->payment_id,
                                        ];

                                    } else {
                                        $Orders->status = -1;
                                        $Orders->error = "مبلغ تراکنش کمتر از مبلغ فاکتور میباشد.";
                                        $Orders->save();

                                        $Data = [
                                            'status' => -1,
                                            'msg' => "مبلغ تراکنش کمتر از مبلغ فاکتور میباشد.",
                                            'payment_id' => $Orders->payment_id,

                                        ];
                                    }
                                } elseif ($response->result == 201) {

                                    $Orders->status = 2;
                                    $Orders->save();

                                    $Data = [
                                        'status' => -1,
                                        'ref_id' => $Orders->ref_id,
                                        'card' => $Orders->card,
                                        'payment_id' => $Orders->payment_id,
                                    ];

                                } else {
                                    $Orders->status = -1;
                                    $Orders->error = "خطا در انجام تراکنش کد:  $response->result";
                                    $Orders->save();

                                    $Data = [
                                        'status' => -1,
                                        'msg' => "خطا در انجام تراکنش کد:  $response->result",
                                        'payment_id' => $Orders->payment_id,
                                    ];
                                }
                            } else {
                                $Orders->status = -1;
                                $Orders->error = 'شماره تراکنش نامعتبر میباشد.';
                                $Orders->save();

                                $Data = [
                                    'status' => -1,
                                    'msg' => 'شماره تراکنش نامعتبر میباشد.',
                                    'payment_id' => $Orders->payment_id,];

                            }
                        } else {
                            $Orders->status = -1;
                            $Orders->error = 'وضعیت سفارش نامعتبر می باشد.';
                            $Orders->save();

                            $Data = [
                                'status' => -1,
                                'msg' => 'وضعیت سفارش نامعتبر می باشد.',
                                'payment_id' => $Orders->payment_id,
                            ];
                        }
                    } else {

                        $Orders->status = -1;
                        $Orders->error = 'سفارش مورد نظر یافت نشد.';
                        $Orders->save();

                        $Data = [
                            'status' => -1,
                            'msg' => 'سفارش مورد نظر یافت نشد.',
                            'payment_id' => $Orders->payment_id,
                        ];
                    }
                } catch (\Exception $exception) {
                    dd($exception->getMessage());

                    $Orders->status = -1;
                    $Orders->error = $exception->getMessage();
                    $Orders->save();
                    $Data = [
                        'status' => -1,
                        'msg' => $exception->getMessage(),
                        'payment_id' => $Orders->payment_id,
                    ];
                }

                return $this->sendCallback($Orders->callback_url, $Data);
            } else {

                $Data = [
                    'status' => -1,
                    'msg' => 'این تراکنش در حال انجام میباشد کد خطا 603',
                    'payment_id' => $Orders->payment_id,
                ];
                return $this->sendCallback($Orders->callback_url, $Data);

            }
        } else {

            $Data = [
                'status' => -1,
                'msg' => 'این تراکنش در حال انجام می باشد.',
            ];
            dd($Data);
        }


    }

    public function sendToBank($request, $Data, $type = null)
    {
        $total_price = $Data['total_price'];
        $id = $Data['order'];
        $mobile = $Data['mobile'];

        $merchent = '64aeaec5cbbc27000ec9327d';
        $parameters = array(
            "merchant" => $merchent,//required
            "callbackUrl" => url('/') . '/payment/callback',//required
            "amount" => $total_price,//required
            "orderId" => $id,//optional
            "mobile" => $mobile,//optional
            "percentMode" => 0,//optional
            "feeMode" => 0,//optional
            "description" => "سفارش شماره: $id",//optional
        );
        $Order = Orders::find($id);
        $response = $this->postToZibal('request', $parameters);

        if ($response->result == 100) {
            //save trackId into database if you want or do something else
            $Order->track_id = $response->trackId;
            $Order->gate = 'zibal';
            $Order->save();

            $startGateWayUrl = "https://gateway.zibal.ir/start/" . $response->trackId;
            if ($type == null) {
                if ($request->ajax()) {
                    return response()->json(['url' => $startGateWayUrl], 200);
                } else {
                    return redirect($startGateWayUrl);
                }
            } elseif ($type == 'api') {
                return $startGateWayUrl;
            }

        } else {
            return exception_error($request, 'دوره مورد نظر یافت نشد.');
        }
    }

    /**/
    public function postToZibal($path, $parameters)
    {
        $url = 'https://gateway.zibal.ir/v1/' . $path;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }

    public function sendCallback($url, $Data)
    {
        $url = "$url?".http_build_query($Data);
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($Data));
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        $response = curl_exec($ch);
//        curl_close($ch);
//        return $response;

        return redirect($url);
    }


    public function checkTransaction(Request $request){
        $bankHistory = BankHistory::where('track_id',$request->track_id)->first();
        if (!is_null($bankHistory)){
            if ($bankHistory->status == 2){
                return response()->json(['status' => 1],200);
            }
            return response()->json(['status' => -1],200);
        }
        return response()->json(['status' => -1],200);
    }
}