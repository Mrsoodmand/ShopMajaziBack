<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Virtual_Country;
use App\Models\Virtual_Products;
use App\Models\Virtual_Service;
use App\Services\VirtualNumberOffers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VirtualServiceController extends Controller
{
    public function CountriesList(Request $request)
    {
        $list = Virtual_Service::orderby('sort', 'asc');
        if ($request->has('search') && $request->input('search') != ' ') {
            $list->where('faName', 'like', '%' . $request->input('search') . '%');
            $list->orwhere('enName', 'like', '%' . $request->input('search') . '%');
            $list->orwhere('code', 'like', '%' . $request->input('search') . '%');
        }
        $list = $list->paginate(40);
        return view('admin-v2.virtual-number.service.index', compact('list'));
    }


    public function Create()
    {
        return view('admin-v2.virtual-number.service.create');
    }

    /* ایجاد سرویس */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'faName' => 'required|string',
                'enName' => 'required|string',
                'slug' => 'required|string',
                'sort' => 'required',
//                'meta_key' => 'required',
                'meta_title' => 'required',
                'meta_desc' => 'required',
//                'detail' => 'required',
            ]);
        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {

            $Service = new Virtual_Service();
            $Service->faName = $request->input('faName');
            $Service->enName = $request->input('enName');
            $Service->slug = $request->input('slug');
            $Service->land = $request->input('land');
            $Service->man = $request->input('man');
            $Service->pva = $request->input('pva');
            $Service->active = $request->input('active');
            $Service->status = $request->input('status');
            $Service->sort = $request->input('sort');
            $Service->meta_title = $request->input('meta_title');
            $Service->meta_desc = $request->input('meta_desc');
            $Service->detail = $request->input('detail');
            $Service->seo = $request->input('seo');
            $Service->image = $request->input('image_gallery');
            $Service->save();

            return success($request, 'با موفقیت ویرایش شد');

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* single country */
    public function Edit($id)
    {
        $edit = Virtual_Service::find($id);
        return view('admin-v2.virtual-number.service.create', compact('edit'));
    }

    /* به روز رسانی سرویس */
    public function update(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'faName' => 'required|string',
                'enName' => 'required|string',
                'slug' => 'required|string',
                'sort' => 'required',
                'meta_title' => 'required',
                'meta_desc' => 'required',
//                'detail' => 'required',
            ]);
        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {

            $Service = Virtual_Service::find($request->input('id'));
            $Service->faName = $request->input('faName');
            $Service->enName = $request->input('enName');
            $Service->slug = $request->input('slug');
            $Service->land = $request->input('land');
            $Service->man = $request->input('man');
            $Service->pva = $request->input('pva');
            $Service->active = $request->input('active');
            $Service->status = $request->input('status');
            $Service->sort = $request->input('sort');
            $Service->meta_title = $request->input('meta_title');
            $Service->meta_desc = $request->input('meta_desc');
            $Service->detail = $request->input('detail');
            $Service->seo = $request->input('seo');
            $Service->image = $request->input('image_gallery');
            $Service->save();

            return success($request, 'با موفقیت ویرایش شد');

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }


    public function FastUpdate(Request $request)
    {
        $Service = Virtual_Service::find($request->input('id'));
        $Service->land = trim($request->input('land'));
        $Service->man = trim($request->input('man'));
        $Service->pva = trim($request->input('pva'));
        $Service->active = trim($request->input('active'));
        $Service->save();

        return success($request, 'با موفقیت ویرایش شد');
    }

    /* Create Service */
    public function CreateService()
    {
        $image = [
            'opt28' => '1668-min',
            'opt97' => '32red-min',
            'opt22' => '888-min',
            'opt86' => 'Adidas-&-Nike',
            'opt46' => 'Airbnb-min',
            'opt61' => 'Alibaba',
            'opt44' => 'amazon-min',
            'opt10' => 'aol-min',
            'opt59' => 'avito-min',
            'opt56' => 'badoo-min',
            'opt25' => 'bet-fair-min',
            'opt78' => 'blizzard-min',
            'opt81' => 'bolt-min',
            'opt89' => 'careem-min',
            'opt98' => 'bet-fair-min',
            'opt112' => 'coinbase-min',
            'opt26' => 'carigslist-min',
            'opt53' => 'deliveroo-min',
            'opt92' => 'didi-min',
            'opt45' => 'discord-min',
            'opt40' => 'door-dash-min',
            'opt21' => 'easy-pay-min',
            'opt2' => 'facebook-min',
            'opt6' => 'fiverr-min',
            'opt68' => 'g2a-min',
            'opt77' => 'game-flip-min',
            'opt35' => 'get-taxi-min',
            'opt108' => 'glovo-min',
            'opt1' => 'gmail-min',
            'opt111' => 'imo-min',
            'opt16' => 'instagram-min',
            'opt128' => 'talk-min',
            'opt37' => 'line-messenger-min',
            'opt8' => 'linked-in-min',
            'opt42' => 'live-score-min',
            'opt105' => 'bit-money-min',
            'opt17' => 'meetme-min',
            'opt96' => 'mi-chat-min',
            'opt7' => 'office-365-min',
            'opt15' => 'microsoft-min',
            'opt95' => 'net-bet-min',
            'opt101' => 'netflix-min',
            'opt83' => 'paypal-min',
            'opt127' => 'signal-min',
            'opt49' => 'skout-min',
            'opt117' => 'skrill-min',
            'opt90' => 'snap-chat-min',
            'opt119' => 'sneakersnstuff-min',
            'opt58' => 'steam-min',
            'opt29' => 'telegram-min',
            'opt104' => 'tiktok-min',
            'opt9' => 'tinder-min',
            'opt66' => 'twilio-min',
            'opt41' => 'twitter-min',
            'opt11' => 'viber-min',
            'opt69' => 'vk-min',
            'opt24' => 'web-money&enum-min',
            'opt67' => 'we-chat-min',
            'opt20' => 'whats-app-min',
            'opt106' => 'wing-money-min',
            'opt65' => 'yahoo-min',
//            'opt23' => '',
        ];

        $Code = [

            'opt28' => '1668.com',
            'opt97' => '32red.com',
            'opt22' => '888casino',
            'opt86' => 'Adidas & Nike',
            'opt46' => 'Airbnb',
            'opt61' => 'Alibaba | Taobao',
            'opt44' => 'Amazon',
            'opt10' => 'AOL',
            'opt59' => 'Avito',
            'opt56' => 'Badoo',
            'opt25' => 'BetFair',
            'opt78' => 'Blizzard',
            'opt81' => 'Bolt',
            'opt89' => 'Careem',
            'opt122' => 'CashWalk',
            'opt76' => 'CityMobil',
            'opt98' => 'Clubhouse',
            'opt112' => 'CoinBase',
            'opt51' => 'CONTACT',
            'opt26' => 'Craigslist',
            'opt124' => 'Credit Karma',
            'opt123' => 'Dabbl',
            'opt53' => 'Deliveroo',
            'opt92' => 'DiDi',
            'opt45' => 'Discord',
            'opt27' => 'Dodopizza + PapaJohns',
            'opt40' => 'DoorDash',
            'opt32' => 'Drom.RU',
            'opt31' => 'Drug Vokrug',
            'opt21' => 'EasyPay',
            'opt2' => 'Facebook',
            'opt43' => 'FastMail',
            'opt6' => 'Fiverr',
            'opt115' => 'foodpanda',
            'opt13' => 'Fotostrana',
            'opt68' => 'G2A.COM',
            'opt77' => 'Gameflip',
            'opt35' => 'GetTaxi',
            'opt3' => 'Glo&iPOST.ua',
            'opt108' => 'Glovo | Raketa',
            'opt1' => 'GMail, YTube',
            'opt30' => 'GrabTaxi',
            'opt420' => 'Grailed',
            'opt110' => 'Grindr',
            'opt120' => 'Hinge',
            'opt103' => 'ICard',
            'opt55' => 'ifood',
            'opt111' => 'IMO',
            'opt118' => 'Inboxdollars',
            'opt16' => 'Instagram',
            'opt94' => 'JD.com',
            'opt128' => 'Kaggle',
            'opt71' => 'KakaoTalk',
            'opt60' => 'Lazada',
            'opt37' => 'Line Messenger',
            'opt8' => 'LinkedIn',
            'opt42' => 'LiveScore',
            'opt105' => 'LocalBitcoins',
            'opt114' => 'Locanto.com',
            'opt75' => 'Lyft',
            'opt38' => 'LYKA',
            'opt126' => 'Magnit',
            'opt33' => 'Mail.RU',
            'opt4' => 'Mail.ru Group',
            'opt100' => 'Mamba',
            'opt17' => 'MeetMe',
            'opt96' => 'MiChat',
            'opt99' => 'Momo',
            'opt121' => 'Monese',
            'opt47' => 'MoneyLion',
            'opt7' => 'MS Office 365',
            'opt15' => 'MS, Bing, HotMail, Azure',
            'opt0' => 'myopinions & erewards',
            'opt73' => 'Naver',
            'opt95' => 'NetBet',
            'opt116' => 'Neteller',
            'opt101' => 'Netflix',
            'opt5' => 'OD',
            'opt113' => 'OfferUp',
            'opt70' => 'OLX + goods.ru',
            'opt19' => 'OTHER',
            'opt109' => 'Paddy Power',
            'opt129' => 'Payactiv',
            'opt83' => 'PayPal + Ebay',
            'opt84' => 'POF.com',
            'opt107' => 'Prom.UA',
            'opt57' => 'Proton Mail',
            'opt48' => 'Shopee',
            'opt127' => 'Signal',
            'opt49' => 'Skout',
            'opt117' => 'Skrill',
            'opt90' => 'Snapchat',
            'opt119' => 'Sneakersnstuff',
            'opt58' => 'Steam',
            'opt125' => 'Swagbucks',
            'opt82' => 'Tango',
            'opt74' => 'Taxi Maksim',
            'opt29' => 'Telegram',
            'opt34' => 'Tencent QQ',
            'opt14' => 'The Insiders',
            'opt52' => 'Ticketmaster',
            'opt104' => 'TikTok',
            'opt9' => 'Tinder',
            'opt66' => 'Twilio',
            'opt41' => 'Twitter',
            'opt72' => 'Uber',
            'opt85' => 'Venmo',
            'opt39' => 'Verse',
            'opt11' => 'Viber',
            'opt69' => 'VK',
            'opt24' => 'WebMoney&ENUM',
            'opt67' => 'WeChat',
            'opt54' => 'Weebly',
            'opt80' => 'WESTSTEIN',
            'opt20' => 'WhatsAPP',
            'opt106' => 'Wing Money',
            'opt91' => 'Wise',
            'opt65' => 'Yahoo',
            'opt88' => 'Yalla.live',
            'opt23' => 'Yandex&YooMoney',
            'opt93' => 'Zoho',


        ];

        foreach ($image as $key => $value) {
            if (array_key_exists($key, $Code)) {
                $Virtual = new Virtual_Service();
                $Virtual->enName = $Code[$key];
                $Virtual->code = $key;
                $Virtual->image = "image/services/$value.jpg";
                $Virtual->save();
            }

        }

    }

    /* Products */
    public function Products()
    {
//        $a = 10;
//        if ($a >= 0 && $a <= 10) {
//            dd('asdas');
//        }
//        dd('asd');
        $Countries = Virtual_Country::all();
        $Service = Virtual_Service::all();

        foreach ($Countries as $key => $country) {
            if ($key >= 51 && $key <= 60) {
                foreach ($Service as $keys => $service) {
                    $Virtual = new Virtual_Products();
                    $Virtual->service_id = $service->id;
                    $Virtual->country_id = $country->id;
                    $Virtual->service_code = $service->code;
                    $Virtual->country_code = $country->code;
                    $Virtual->save();
                }
            }

        }
    }

    /* Get Count */
    public function UpdateCount()
    {

        $VirtualCode = Virtual_Products::get();
        set_time_limit(2000);
        foreach ($VirtualCode as $code) {
            $address = "http://smspva.com/priemnik.php?metod=get_count_new&country=$code->country_code&service=$code->service_code&apikey=" . config('services.smspva.legacy_key') . "";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $address);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $export = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if (!is_null($export)) {
                $update = Virtual_Products::find($code->id);
                $update->quantity = $export['online'];
                $update->save();
            } else {
                $update = Product::find($code->id);
                $update->quantity = 0;
                $update->save();
            }
        }
    }

    /* «بروز رسانی»: builds this service's offers in every country (App\Services\VirtualNumberOffers). */
    public function GenerateProduct($id)
    {
        $Service = Virtual_Service::findOrFail($id);
        $Result = app(VirtualNumberOffers::class)->rebuildService($Service);

        return redirect()->route('admin.edit-Virtual-Services', $Service->id)
            ->with('success', "پیشنهادها بازسازی شد: {$Result['created']} ردیف تازه، {$Result['updated']} ردیف به‌روز.");
    }

    /* Get Count */
    public function UpdatePrice()
    {
        $VirtualCode = Virtual_Products::get();

        set_time_limit(1200);

        foreach ($VirtualCode as $key => $code) {
            if ($key >= 2001 && $key <= 3000) {
                $ch = curl_init();
                $address = "http://smspva.com/priemnik.php?metod=get_service_price&country=$code->country_code&service=$code->service_code&apikey=" . config('services.smspva.legacy_key') . "";
                curl_setopt($ch, CURLOPT_URL, $address);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $export = json_decode(curl_exec($ch), true);
                curl_close($ch);
                if ($export['response'] == 1) {
                    $New = Virtual_Products::find($code->id);
                    $New->dollar_price = $export['price'];
                    $New->save();


                } else {
                    $update = VirtualCode::find($code->id);
                    $update->status = 0;
                    $update->save();
                }
            }
        }
    }

    public function UpdateTomanPrice()
    {
        $VirtualCode = Virtual_Products::get();
        $Setting = Setting::find(1);

        set_time_limit(2000);

        foreach ($VirtualCode as $key => $code) {

            $ch = curl_init();
            $address = "http://smspva.com/priemnik.php?metod=get_service_price&country=$code->country_code&service=$code->service_code&apikey=" . config('services.smspva.legacy_key') . "";
            curl_setopt($ch, CURLOPT_URL, $address);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $export = json_decode(curl_exec($ch), true);
            curl_close($ch);
            if ($export['response'] == 1) {
                $New = Virtual_Products::find($code->id);
                $New->toman_price = ($code->dollar_price * $Setting->value) * 3;
                $New->save();


            } else {
                $update = VirtualCode::find($code->id);
                $update->status = 0;
                $update->save();
            }

        }
    }


}
