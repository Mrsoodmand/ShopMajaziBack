<?php

namespace App\Http\Controllers;

use App\lib\Jibit;
use App\Mail\Subscribe;
use App\Models\Blog;
use App\Models\Commission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Wallet;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\lib\ZoomService;

class ApiController extends Controller
{

    /**/
    public function CheckCommissions()
    {
        $Commissions = Commission::where('status', 'progressing')->get();
    }

    /**/
    public function CheckNumbers()
    {

        $Order = Order::where('status', 'Pending')->get()->pluck('id')->toarray();
        $OrderItems = OrderItem::wherein('order_id', $Order)->where('type', 'Virtual-Number')->get();

        foreach ($OrderItems as $item) {
            if (!is_null($item->detail)) {
                $Detail = json_decode($item->detail, true);

                if ($Detail['Sms'] == 0) {

                    if (strtotime(Carbon::now()) >= strtotime($Detail['expire_time'])) {

                        $Order = Order::find($item->order_id);
                        $Order->status = 'failed';
//                        $Order->pay_status = 'failed';
                        $Order->detail = 'شماره خریداری شده منقضی شده است و مبلغ به کیف پول برگشت خورده است';
                        $Order->save();

                        $User = User::find($Order->user_id);
                        $User->wallet = $Order->price + $User->wallet;
                        $User->save();

                        $Wallet = new Wallet();
                        $Wallet->user_id = $Order->user_id;
                        $Wallet->type = 2;
                        $Wallet->value = $Order->price;
                        $Wallet->detail = "برگشت وجه سفارش شماره $Order->id به کیف پول بابت لغو شدن سفارش ";
                        $Wallet->save();


                        $Commission = Commission::where('order_id', $Order->id)->first();
                        $Commission->status = 'failed';
                        $Commission->save();

                    }
                } else {

                    $Order = Order::find($item->order_id);
                    $Order->status = 'complete';
                    $Order->save();

                    $Commission = Commission::where('order_id', $Order->id)->first();
                    if (!is_null($Commission)) {
                        $Commission->status = 'progressing';
                        $Commission->save();

                    }

                }
            }


        }
    }

    /**/
    public function blog_map()
    {
        $Blogs = Blog::where('status', 2)->where('type', 'blog')->get();
        SiteMap($Blogs, 'blog/sitemap.xml');
    }

    /**/
    public function news_map()
    {
        $Blogs = Blog::where('status', 2)->where('type', 'news')->get();
        SiteMap($Blogs, 'news.xml');
    }

    /* api */
    public function CheckApi(Request $request)
    {
        $url = 'https://www.toptal.com/developers/cssminifier/api/raw';

        // init the request, set various options, and send it
        $ch = curl_init();

        $code = $request->input('key');
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
            CURLOPT_POSTFIELDS => http_build_query(["input" => $code])
        ]);

        $minified = curl_exec($ch);

        // finally, close the request
        curl_close($ch);

        dd($minified);
        // output the $minified JavaScript
        echo $minified;
    }

    /**/
    public function mailtest()
    {
        $email = 'mahdi_sodmand@yahoo.com';

        Mail::to($email)->send(new Subscribe($email));

    }

    /**/
    public function buyFromSpotPlayer()
    {
        $apiToken = config('services.spotplayer.token');
        $data = [
            'test' => true,
            'course' => [
                "659300d9928f44f9ded284d5"
            ],
            'name' => 'Mahdi Sodmand',
            'watermark' => [
                'texts' => [
                    ['text' => '09302736430']
                ]
            ]
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://panel.spotplayer.ir/license/edit/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                '$API: ' . config('services.spotplayer.token') . '',
                '$LEVEL: -1',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        dd(json_decode($response, true));
    }

    /* check blogs */
    public function checkBlogs()
    {
        $Blogs = Blog::where('status', 1)->get();
        $Now = Carbon::now();

        foreach ($Blogs as $item) {
            if ($Now >= $item->share_at) {
                $Blog = Blog::find($item->id);
                $Blog->status = 2;
                $Blog->save();
            }
        }
    }

    public function ishostingLogin()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.ishosting.com/auth/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
  "type": "password",
  "email": "mojtaba.gharegoli@gmail.com",
  "password": "Aa1050474821@"
}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        echo $response;
    }


    public function checkSanaie(Request $request)
    {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://panels1.shopmajaziplus.ir/zerbi/panel/api/inbounds/get/6',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Cookie: session=MTcxMTM1MDA1M3xEWDhFQVFMX2dBQUJFQUVRQUFCMV80QUFBUVp6ZEhKcGJtY01EQUFLVEU5SFNVNWZWVk5GVWhoNExYVnBMMlJoZEdGaVlYTmxMMjF2WkdWc0xsVnpaWExfZ1FNQkFRUlZjMlZ5QWYtQ0FBRUVBUUpKWkFFRUFBRUlWWE5sY201aGJXVUJEQUFCQ0ZCaGMzTjNiM0prQVF3QUFRdE1iMmRwYmxObFkzSmxkQUVNQUFBQUhQLUNHUUVDQVFodFlXaGthVGt6TUFFS2JXRm9aR2xmT1RNd1FBQT18l5YE6p7fZCp1gDuQixkDLsxp12GZUI7OVS2lXm7AWJk='
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        if ($request->has('code')) {
            return sanaieCode(json_decode($response, true)['obj'], '5454654650', 'testض');
        }
        dd(json_decode($response, true));

    }

    public function getDollarPrice()
    {
        $price = file_get_contents('https://panel.ariapay.me/api/price-list');
        dd($price);
    }

    public function generateZoomMetting()
    {

        $zoom = new ZoomService();

        $meeting = $zoom->createMeeting(
            'جلسه تستی',
            '2025-08-25 14:30:00', // زمان محلی
            'Asia/Tehran',
            90 // مدت جلسه (دقیقه)
        );

        return response()->json([
            'topic' => $meeting['topic'],
            'start' => $meeting['start_time'],
            'join_url' => $meeting['join_url'],  // برای اعضا
            'start_url' => $meeting['start_url'], // مخصوص میزبان
        ]);
    }

}
