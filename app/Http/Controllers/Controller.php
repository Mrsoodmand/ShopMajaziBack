<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Virtual_Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function MakeProducts()
    {
        $Countries = Virtual_Country::all();
        $Services = Virtual_Service::all();
        set_time_limit(5000);
        foreach ($Countries as $country) {

            foreach ($Services as $service) {

                $faCountryName = str_replace('شماره مجازی کشور', '', $country->faName);
                $faServiceName = str_replace('شماره مجازی', '', $service->faName);
                $faName = " خرید شماره مجازی$faServiceName از کشور$faCountryName";
                $enName = "Buy virtual number of $service->enName From $country->enName";

                $Detail = [
                    'Info' => [
                        'country_id' => $country->id,
                        'pva_country_code' => $country->code,
                        'land_country_code' => $country->land_code,

                        'service_id' => $service->id,
                        'pva_service_code' => $service->code,
                        'land_service_code' => $service->land_code,

                        'faName' => $faName,
                        'enName' => $enName,
                    ],
                    'Price' => [
                        'pva_dollar_price' => 0,
                        'pva_toman_price' => 0,
                        'land_toman_price' => 0,
                    ]
                ];

                $Product = new Product();
                $Product->type = 'virtual-number';
                $Product->detail = json_encode($Detail);
                $Product->save();

            }
        }
    }

    public function CheckPrices()
    {
        $VirtualCode = Product::all();
        set_time_limit(30000);
        foreach ($VirtualCode as $key => $code) {

            $Detail = json_decode($code->detail, true);

            $Pva_Service_Code = $Detail['Info']['pva_service_code'];
            $Pva_Country_Code = $Detail['Info']['pva_country_code'];
            if ($Pva_Service_Code != '0') {

                $ch = curl_init();
                $address = "http://smspva.com/priemnik.php?metod=get_service_price&country=$Pva_Country_Code&service=$Pva_Service_Code&apikey=" . config('services.smspva.legacy_key') . "";
                curl_setopt($ch, CURLOPT_URL, $address);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $export = json_decode(curl_exec($ch), true);

                curl_close($ch);

                if ($export['response'] == 1) {
                    $Pva_Dollar_price = $export['price'];
                    $Pva_Toman_price = CovertDollarToTomanPrice($export['price']);

                } else {
                    $Pva_Dollar_price = 0;
                    $Pva_Toman_price = 0;
                }

            } else {
                $Pva_Dollar_price = 0;
                $Pva_Toman_price = 0;
            }
            $Land_Service_Code = $Detail['Info']['land_service_code'];
            $Land_Country_Code = $Detail['Info']['land_country_code'];
            if ($Land_Service_Code != '0') {
                $ch = curl_init();
                $address = "https://api.numberland.ir/v2.php/?apikey=" . config('services.numberland.legacy_key') . "&method=getinfo&country=$Land_Country_Code&operator=any&service=$Land_Service_Code";
                curl_setopt($ch, CURLOPT_URL, $address);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $export = json_decode(curl_exec($ch), true);
                curl_close($ch);

                if (is_array($export)) {
                    $Land_Toman_Price = $export[0]['amount'];
                    $Land_Quantity = $export[0]['count'];
                } else {
                    $Land_Toman_Price = 0;
                    $Land_Quantity = 0;
                }

            } else {
                $Land_Toman_Price = 0;
                $Land_Quantity = 0;
            }
            $Price = [
                'Pva_Dollar_Price' => $Pva_Dollar_price,
                'Pva_Toman_Price' => $Pva_Toman_price,
                'Land_Toman_Price' => $Land_Toman_Price,
            ];
            $Product = Product::find($code->id);
            $Product->land_quantity = $Land_Quantity;
            $Product->price = json_encode($Price);
            $Product->save();

        }

    }

    public function CheckPvaQuantity()
    {
//        $VirtualCode = Product::get();
        $VirtualCode = Product::where('id', '>', 2201)->get();

        $Service = Virtual_Service::all();
        set_time_limit(2000);
        foreach ($VirtualCode as $code) {
            $Detail = json_decode($code->detail, true);

            $CurrentServiceSpecial = $Service->find($Detail['Info']['service_id'])->special;
            $CurrentServiceSort = $Service->find($Detail['Info']['service_id'])->sort;

            $Pva_Service_Code = $Detail['Info']['pva_service_code'];
            $Pva_Country_Code = $Detail['Info']['pva_country_code'];

            $address = "http://smspva.com/priemnik.php?metod=get_count_new&country=$Pva_Country_Code&service=$Pva_Service_Code&apikey=" . config('services.smspva.legacy_key') . "";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $address);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $export = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if (!is_null($export)) {
                $update = Product::find($code->id);
                $update->pva_quantity = $export['online'];
                $update->sort = $CurrentServiceSort;
                $update->special = $CurrentServiceSpecial;
                $update->save();
            } else {
                $update = Product::find($code->id);
                $update->pva_quantity = 0;
                $update->sort = $CurrentServiceSort;
                $update->special = $CurrentServiceSpecial;
                $update->save();
            }
        }
    }

    public function UploadUserTicketImage($file_peram)
    {
        $file_name = $file_peram->getClientOriginalName();
        $file_name = substr($file_name, 0, strrpos($file_name, '.'));
        $file_name = str_replace(' ', '', $file_name);
        $file_name = str_replace('+', '--', $file_name);
        $Random_num = rand(0, 999999);
        $New_File_Name = $file_name . '-' . $Random_num . '.' . $file_peram->guessClientExtension();
        $DestinationPath = 'tickets/';
        $file_peram->move($DestinationPath, $New_File_Name);
        $path_file = 'tickets/' . $New_File_Name;
        return $path_file;
    }


    public function sitemap()
    {
        dd(bcrypt('1qaz!QAZ'));
//        $xmp = file_get_contents('sitemap.xml');
//
//        $products = DB::table('products')
//            ->get();
//
//
//
//        foreach ($products as $value) {
//
//            $xmp .=
//                '<url>
//  <loc>' . url('service', $value->slug) . '</loc>
//  <lastmod>2020-09-06T15:01:14+00:00</lastmod>
//  <priority>0.80</priority>
//</url>
//';
//
//        }
//
//
////<!---->
////<!---->
////<!---->
////<!---->
////<!--</urlset>-->
//        file_put_contents('sitemap.xml', $xmp);
    }


    //
    public function CreateNewVirtualNumbersCountry()
    {

        $Countries = DB::table('new_virtual_countries')->get();
        foreach ($Countries as $country) {


            $faname = trim(str_replace(' شماره مجازی کشور ', '', $country->faName));


            $enName = str_replace('buy virtual number of buy virtual number of buy virtual number of buy virtual number of buy virtual number of buy virtual number of buy virtual number of buy virtual number of ', '', $country->enName);

            $fa = "  شماره مجازی کشور $faname";
//            $en = "buy virtual number of $enName";
            $slug = str_replace('buy virtual number of ', '', $enName);
            $meta_title = "خرید شماره مجازی کشور$faname با قیمت عالی و تحویل آنی | پشتیبانی 24 ساعته | شاپ مجازی ";
            $meta_desc = "وب سایت شاپ مجازی ارائه دهنده  شماره مجازی از کشورهای روسیه ، آمریکا ، آرژانتین ، آلمان ، انگلستان و بیش از 120 کشور و پشتیبانی  بیش از 200 برنامه و اپلیکشین و سایت | پشتیبانی 24 ساعته | فقط با 3 کلیک شمارتو بخر. ";

            DB::table('new_virtual_countries')->where('id', $country->id)->update([
                'faName' => trim($fa),
//                'enName' => $enName,
                'slug' => $slug,
                'meta_title' => $meta_title,
                'meta_desc' => $meta_desc,
                'updated_at' => Carbon::now(),
            ]);


        }


    }


    public function CreateNewVirtualNumbersServices()
    {

        $Countries = DB::table('new_virtual_service')->get();
        foreach ($Countries as $country) {

            $fa = "  شماره مجازی  $country->faName";
            $ffa = str_replace('شماره مجازی', '', $country->faName);
            $en = "buy virtual number of $country->enName";
            $slug = str_replace('buy virtual number of ', '', $country->enName);
            $meta_title = "خرید شماره مجازی $country->faName با قیمت عالی و تحویل آنی | پشتیبانی 24 ساعته | شاپ مجازی ";
            $meta_desc = " وب سایت شاپ مجازی ارائه دهنده شماره مجازی  $ffa ، اپل آیدی ، اینستاگرام ، واتس آپ ، تلگرام ، تیک تاک و انواع اپلیکشین های دیگر با تنوع بیش از 150 کشور سرویس دهنده.  | پشتیبانی 24 ساعته | فقط با 3 کلیک شمارتو بخر   . ";

            DB::table('new_virtual_service')->where('id', $country->id)->update([
                'faName' => $fa,
                'enName' => $en,
                'slug' => $slug,
                'meta_title' => $meta_title,
                'meta_desc' => $meta_desc,
                'updated_at' => Carbon::now(),
            ]);


        }


    }


    public function CreateNewVirtualNumbersProduct()
    {


        $Countries = DB::table('new_virtual_countries')->get();
        $Services = DB::table('new_virtual_service')->get();
        foreach ($Countries as $country) {
            foreach ($Services as $key => $service) {


                if ($key < 50) {
                    $serviceName = trim($service->faName);
                    $country_name = trim($country->faName);

                    $faName = "  خرید $serviceName از کشور $country_name";
                    $EnService = str_replace('buy virtual number of ', '', $service->enName);
                    $Encountry = str_replace('buy virtual number of', '', $country->enName);
                    $EnName = "Buy $EnService Virtual Number from $Encountry";

                    $ProductDetailInfo['Info'] = [
                        'country_code' => $country->code,
                        'service_code' => $service->code,
                        'faName' => trim($faName),
                        'enName' => trim($EnName),
                    ];

                    $slug = str_replace('  ', ' ', $faName);
                    $slug = str_replace('   ', ' ', $slug);
                    $slug = str_replace('    ', ' ', $slug);
                    $slug = str_replace('      ', ' ', $slug);
                    $slug = str_replace(' ', '-', $slug);
                    $slug = str_replace('--', '-', $slug);

                    $Product = new Product();
                    $Product->type = 'new-virtual-number';
                    $Product->slug = $slug;
                    $Product->detail = json_encode($ProductDetailInfo);
                    $Product->commission = 10;
                    $Product->service_id = $service->id;
                    $Product->country_id = $country->id;
                    $Product->save();

                }

            }
        }


    }

    public function mailtest()
    {
//        $email = 'mahdi_sodmand@yahoo.com';
//        $email = 'mahdipersia.mp@gmail.com';
//        $email = 'smsmahdi@yahoo.com';
//        $email = 'sirsabeti@gmail.com';

        Mail::to($email)->send(new Subscribe($email));
        return new JsonResponse(
            [
                'success' => true,
                'message' => "Thank you for subscribing to our email, please check your inbox"
            ],
            200
        );
    }

    public function wedding()
    {
        return view('site-v2.wedding.index');
    }

    public function weddingForm(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'name' => 'required',
            'mobile' => 'required',
        ],[
            'name.required' => 'لطفا اسم را وارد کنید.',
            'mobile.required' => 'لطفا شماره موبایل را وارد کنید.',
        ]);
        if ($validate->fails()){
            return response()->json(['error' => $validate->errors()->first()],400);
        }
        DB::table('wedding')->insert([
            'name' => $request->input('name'),
            'mobile' => $request->input('mobile'),
            'status' => $request->input('status'),
        ]);

        $UserName = $request->input('name');
        $UserMobile = $request->input('mobile');
        $status = ($request->input('status') == 1 ? 'میام' : 'نمیام');
        $Message = "
💬 فرم جدیدی ثبت شد!
👤 نام کاربر: $UserName
📞 شماره کاربر: $UserMobile
🔴 وضعیت: $status

";
        SendTelegramMessage($Message, 480038532);

        return response()->json(['status' => 1,'text' => 'فرم با موفقبت ثبت شد'],200);
    }
}
