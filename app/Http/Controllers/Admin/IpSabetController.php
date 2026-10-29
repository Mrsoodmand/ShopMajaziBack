<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\PhpEncrypt;
use App\Models\AppleId;
use App\Models\Category;
use App\Models\Ip;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pages;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class IpSabetController extends Controller
{
    /* لیست آی پی ها */
    public function index($type, Request $request)
    {
        $list = Category::where('type', $type)->orderby('id', 'DESC')->paginate(50);
        return view('admin-v2.ip-sabet.index', compact('list', 'type'));
    }

    /* ایجاد آی پی */
    public function create($type)
    {
        return view('admin-v2.ip-sabet.create', compact('type'));
    }

    /* ثبت آی پی */
    public function store(Request $request)
    {
        $Validate = Validator::make($request->all(), [
            'faName' => 'required',
            'enName' => 'required',
            'meta_title' => 'required',
            'meta_desc' => 'required',
            'image_gallery' => 'required',
//            'status' => 'required',
            'type' => 'required',
        ], [
            'faName.required' => 'لطفا نام فارسی را وارد نمایید',
            'enName.required' => 'لطفا  نام انگلیسی را وارد نمایید',
            'meta_title.required' => 'لطفا متا تایتل را وارد نمایید',
            'meta_desc.required' => 'لطفا متا دسکریپشن را وارد نمایید',
            'image_gallery.required' => 'لطفا عکس شاخص را وارد نمایید',
            'status.required' => 'لطفا وضعیت را وارد نمایید',
            'type.required' => 'لطفا نوع آی پی را وارد نمایید',
        ]);
        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        try {
            $Category = new Category();
            $Category->type = $request->input('type');
            $Category->name = $request->input('faName');
            $Category->slug = strtolower(str_replace(' ', '-', $request->input('enName')));
            $Category->enName = $request->input('enName');
            $Category->meta_title = $request->input('meta_title');
            $Category->meta_desc = $request->input('meta_desc');
            $Category->status = $request->input('status');
            $Category->image = GenerateImageUrl($request->input('image_gallery'));
            $Category->save();

            if ($request->has('product_id')) {
                foreach ($request->input('product_id') as $key => $value) {
                    $Detail = [
                        'day' => $request->input('product_day')[$key],
                        'volume' => $request->input('product_volume')[$key],
                        'dis' => $request->input('product_dis')[$key],
                        'percent' => $request->input('product_percent')[$key],
                        'image' => GenerateImageUrl($request->input('product_image')[$key]),
                    ];

                    $Product = new Product();
                    $Product->faName = $request->input('product_name')[$key];
                    $Product->price = $request->input('product_price')[$key];
                    $Product->detail = json_encode($Detail);
                    $Product->type = $request->input('type');
                    $Product->category_id = $Category->id;
                    $Product->save();
                }
            }

            if ($request->has('ip_id')) {
                foreach ($request->input('ip_id') as $ip_key => $value) {
                    $ip_detail = [
                        'ip_address' => $request->input('ip_address')[$ip_key],
                        'ip_domain' => $request->input('ip_domain')[$ip_key],
                        'ip_port' => $request->input('ip_port')[$ip_key],
                        'ip_user' => $request->input('ip_user')[$ip_key],
                        'ip_password' => $request->input('ip_password')[$ip_key],
                        'ip_status' => $request->input('ip_status')[$ip_key],
                        'ip_inbound' => $request->input('ip_inbound')[$ip_key],
                        'ip_limit' => $request->input('ip_limit')[$ip_key],
                        'ip_api_domain' => $request->input('ip_api_domain')[$ip_key],
                        'ip_name' => $request->input('ip_name')[$ip_key],
                        'port_inbound' => $request->input('port_inbound')[$ip_key],
                        'session' => null,
                    ];

                    if ($value == 0) {
                        $Ip = new Ip();
                    } else {
                        $Ip = Ip::find($value);
                    }

                    $Ip->type = $request->input('type');
                    $Ip->detail = json_encode($ip_detail);
                    $Ip->status = $request->input('ip_status')[$ip_key];
                    $Ip->limit = $request->input('ip_limit')[$ip_key];
                    $Ip->category_id = $Category->id;
                    $Ip->save();

                    $Data = loginToSanaeiAccount($ip_detail);

                    if ($Data['login'] == true) {

                        $ip_detail = [
                            'ip_address' => $request->input('ip_address')[$ip_key],
                            'ip_domain' => $request->input('ip_domain')[$ip_key],
                            'ip_port' => $request->input('ip_port')[$ip_key],
                            'ip_user' => $request->input('ip_user')[$ip_key],
                            'ip_password' => $request->input('ip_password')[$ip_key],
                            'ip_status' => $request->input('ip_status')[$ip_key],
                            'ip_inbound' => $request->input('ip_inbound')[$ip_key],
                            'ip_limit' => $request->input('ip_limit')[$ip_key],
                            'ip_api_domain' => $request->input('ip_api_domain')[$ip_key],
                            'ip_name' => $request->input('ip_name')[$ip_key],
                            'port_inbound' => $request->input('port_inbound')[$ip_key],
                            'session' => $Data['sessionCookie'],
                        ];
                        $Ip = Ip::find($Ip->id);
                        $Ip->detail = json_encode($ip_detail);
                        $Ip->save();
                    } else {
                        return exception_error($request, 'خطا در لاگین سرور');
                    }

                    $IpsId[] = $Ip->id;
                    $type = $request->input('type');

                    $CountRequest = intval($request->input('ip_limit')[$ip_key]);
                    if ($CountRequest != 0) {
                        $Count = Ip::orderby('id', 'DESC')->where('type', 'pu-ip-sabet-user')->count() + 1;
                        for ($i = 1; $i <= $CountRequest; $i++) {
                            $NameTwo = $request->input('ip_name')[$ip_key];
                            $Name = $NameTwo.'-' . $Count;
                            $items = [
                                'Session' => $Data['sessionCookie'],
                                'api_domain' => $ip_detail['ip_api_domain'],
                                'ip_address' => $ip_detail['ip_address'],
                                'ip_port' => $ip_detail['ip_port'],
                                'ip_inbound' => $ip_detail['ip_inbound'],
                                'port_inbound' => $ip_detail['port_inbound'],
                                'name' => $Name,
                            ];
                            $data = addClientToSanaie($items);

                            if ($data['status'] == 1) {
                                $UserIp_detail = [
                                    'user_port' => $ip_detail['ip_port'],
                                    'user_remark' => $Name,
                                    'user_code' => $data['msg'],
                                ];
                                $UserIp = new Ip();
                                $UserIp->status = 0;
                                $UserIp->type = $type . '-user';
                                $UserIp->detail = json_encode($UserIp_detail);
                                $UserIp->parent_id = $Ip->id;
                                $UserIp->UID = $data['UID'];
                                $UserIp->category_id = $Category->id;
                                $UserIp->save();

                            } else {
                                dd($data);
                            }
                            $Count++;
                        }
                    }
                    if ($request->has('user_id')) {
                        if (array_key_exists($ip_key, $request->input('user_id'))) {
                            foreach ($request->input('user_id')[$ip_key] as $user_key => $user_value) {
                                $UserIp_detail = [
                                    'user_port' => $request->input('user_port')[$ip_key][$user_key],
                                    'user_remark' => $request->input('user_remark')[$ip_key][$user_key],
                                    'user_code' => $request->input('user_code')[$ip_key][$user_key],
                                ];

                                if ($user_value == 0) {
                                    $UserIp = new Ip();
                                    $UserIp->status = 0;
                                } else {
                                    $UserIp = Ip::find($user_value);
                                }

                                $UserIp->type = $request->input('type') . '-user';
                                $UserIp->detail = json_encode($UserIp_detail);
                                $UserIp->parent_id = $Ip->id;
                                $UserIp->category_id = $Category->id;
                                $UserIp->save();
                                $UserIps[] = $UserIp->id;
                            }
                            $Delete = Ip::where('parent_id', $Ip->id)->where('status', 0)->where('category_id', $Category->id)->whereNotIn('id', $UserIps)->delete();
                        }
                    }
                }
                Ip::where('type', $type)->where('category_id', $Category->id)->wherenotIn('id', $IpsId)->delete();
            }


            return success($request, 'محصول با موفقیت ثبت شد');

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* ویرایش آی پی ثابت */
    public function edit($id)
    {
        $edit = Category::find($id);
        if (!is_null($edit)) {
            $Ip = Ip::where('category_id', $edit->id)->get();
            $Plans = Product::where('category_id', $edit->id)->get();
            return view('admin-v2.ip-sabet.create', compact('edit', 'Ip', 'Plans'));
        } else {
            return redirect('admin.ip-sabet-list');
        }
    }

    /* بروز رسانی آی پی ثابت */
    public function update(Request $request)
    {
        $Validate = Validator::make($request->all(), [
            'faName' => 'required',
            'enName' => 'required',
            'meta_title' => 'required',
            'meta_desc' => 'required',
            'image_gallery' => 'required',
//            'status' => 'required',
            'type' => 'required',
        ], [
            'faName.required' => 'لطفا نام فارسی را وارد نمایید',
            'enName.required' => 'لطفا  نام انگلیسی را وارد نمایید',
            'meta_title.required' => 'لطفا متا تایتل را وارد نمایید',
            'meta_desc.required' => 'لطفا متا دسکریپشن را وارد نمایید',
            'image_gallery.required' => 'لطفا عکس شاخص را وارد نمایید',
            'status.required' => 'لطفا وضعیت را وارد نمایید',
            'type.required' => 'لطفا نوع آی پی را وارد نمایید',
        ]);
        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        try {

            $Category = Category::find($request->input('id'));
            $Category->type = $request->input('type');
            $Category->name = $request->input('faName');
            $Category->slug = strtolower(str_replace(' ', '-', $request->input('enName')));
            $Category->enName = $request->input('enName');
            $Category->status = $request->input('status');
            $Category->meta_title = $request->input('meta_title');
            $Category->meta_desc = $request->input('meta_desc');
            $Category->image = GenerateImageUrl($request->input('image_gallery'));
            $Category->save();
            if ($request->has('product_id')) {
                foreach ($request->input('product_id') as $key => $value) {
                    $Detail = [
                        'day' => $request->input('product_day')[$key],
                        'volume' => $request->input('product_volume')[$key],
                        'dis' => $request->input('product_dis')[$key],
                        'percent' => $request->input('product_percent')[$key],
                        'image' => GenerateImageUrl($request->input('product_image')[$key]),
                    ];
                    if ($value == 0) {
                        $Product = new Product();
                    } else {
                        $Product = Product::find($value);
                    }
                    $Product->faName = $request->input('product_name')[$key];
                    $Product->price = $request->input('product_price')[$key];
                    $Product->detail = json_encode($Detail);
                    $Product->type = $request->input('type');
                    $Product->category_id = $Category->id;
                    $Product->save();
                    $ProductId[] = $Product->id;
                }
                Product::where('type', $request->input('type'))->where('category_id', $Category->id)->wherenotIn('id', $ProductId)->delete();
            }


            if ($request->has('ip_id')) {
                foreach ($request->input('ip_id') as $ip_key => $value) {
                    $ip_detail = [
                        'ip_address' => $request->input('ip_address')[$ip_key],
                        'ip_domain' => $request->input('ip_domain')[$ip_key],
                        'ip_port' => $request->input('ip_port')[$ip_key],
                        'ip_user' => $request->input('ip_user')[$ip_key],
                        'ip_password' => $request->input('ip_password')[$ip_key],
                        'ip_status' => $request->input('ip_status')[$ip_key],
                        'ip_inbound' => $request->input('ip_inbound')[$ip_key],
                        'ip_limit' => $request->input('ip_limit')[$ip_key],
                        'ip_api_domain' => $request->input('ip_api_domain')[$ip_key],
                        'ip_name' => $request->input('ip_name')[$ip_key],
                        'port_inbound' => $request->input('port_inbound')[$ip_key],
                        'session' => null,
                    ];

                    if ($value == 0) {
                        $Ip = new Ip();
                    } else {
                        $Ip = Ip::find($value);
                    }

                    $Ip->type = $request->input('type');
                    $Ip->detail = json_encode($ip_detail);
                    $Ip->status = $request->input('ip_status')[$ip_key];
                    $Ip->limit = $request->input('ip_limit')[$ip_key];
                    $Ip->category_id = $Category->id;
                    $Ip->save();

                    $Data = loginToSanaeiAccount($ip_detail);

                    if ($Data['login'] == true) {

                        $ip_detail = [
                            'ip_address' => $request->input('ip_address')[$ip_key],
                            'ip_domain' => $request->input('ip_domain')[$ip_key],
                            'ip_port' => $request->input('ip_port')[$ip_key],
                            'ip_user' => $request->input('ip_user')[$ip_key],
                            'ip_password' => $request->input('ip_password')[$ip_key],
                            'ip_status' => $request->input('ip_status')[$ip_key],
                            'ip_inbound' => $request->input('ip_inbound')[$ip_key],
                            'ip_limit' => $request->input('ip_limit')[$ip_key],
                            'ip_api_domain' => $request->input('ip_api_domain')[$ip_key],
                            'ip_name' => $request->input('ip_name')[$ip_key],
                            'port_inbound' => $request->input('port_inbound')[$ip_key],
                            'session' => $Data['sessionCookie'],

                        ];
                        $Ip = Ip::find($Ip->id);
                        $Ip->detail = json_encode($ip_detail);
                        $Ip->save();
                    } else {
                        return exception_error($request, 'خطا در لاگین سرور');
                    }

                    $IpsId[] = $Ip->id;
                    $type = $request->input('type');

                    $CountRequest = intval($request->input('ip_limit')[$ip_key]);
                    if ($CountRequest != 0) {
                        $Count = Ip::orderby('id', 'DESC')->where('type', 'pu-ip-sabet-user')->count() + 1;
                        for ($i = 1; $i <= $CountRequest; $i++) {
                            $NameTwo = $request->input('ip_name')[$ip_key];
                            $Name = $NameTwo.'-'.$Count;
                            $items = [
                                'Session' => $Data['sessionCookie'],
                                'api_domain' => $ip_detail['ip_api_domain'],
                                'ip_address' => $ip_detail['ip_address'],
                                'ip_port' => $ip_detail['ip_port'],
                                'ip_inbound' => $ip_detail['ip_inbound'],
                                'port_inbound' => $ip_detail['port_inbound'],
                                'name' => $Name,
                            ];
                            $data = addClientToSanaie($items);

                            if ($data['status'] == 1) {
                                $UserIp_detail = [
                                    'user_port' => $ip_detail['ip_port'],
                                    'user_remark' => $Name,
                                    'user_code' => $data['msg'],
                                ];
                                $UserIp = new Ip();
                                $UserIp->status = 0;
                                $UserIp->type = $type . '-user';
                                $UserIp->detail = json_encode($UserIp_detail);
                                $UserIp->parent_id = $Ip->id;
                                $UserIp->UID = $data['UID'];
                                $UserIp->category_id = $Category->id;
                                $UserIp->save();

                            } else {
                                dd($data);
                            }
                            $Count++;
                        }
                    }
                    if ($request->has('user_id')) {
                        if (array_key_exists($ip_key, $request->input('user_id'))) {
                            foreach ($request->input('user_id')[$ip_key] as $user_key => $user_value) {
                                $UserIp_detail = [
                                    'user_port' => $request->input('user_port')[$ip_key][$user_key],
                                    'user_remark' => $request->input('user_remark')[$ip_key][$user_key],
                                    'user_code' => $request->input('user_code')[$ip_key][$user_key],
                                ];

                                if ($user_value == 0) {
                                    $UserIp = new Ip();
                                    $UserIp->status = 0;
                                } else {
                                    $UserIp = Ip::find($user_value);
                                }

                                $UserIp->type = $request->input('type') . '-user';
                                $UserIp->detail = json_encode($UserIp_detail);
                                $UserIp->parent_id = $Ip->id;
                                $UserIp->category_id = $Category->id;
                                $UserIp->save();
                                $UserIps[] = $UserIp->id;
                            }
                            $Delete = Ip::where('parent_id', $Ip->id)->where('status', 0)->where('category_id', $Category->id)->whereNotIn('id', $UserIps)->delete();
                        }
                    }
                }
                Ip::where('type', $type)->where('category_id', $Category->id)->wherenotIn('id', $IpsId)->delete();
            }


            $Orders = Order::where('status', 'pending')->where('type', 5)->get();
            foreach ($Orders as $order) {
                $OrderItem = OrderItem::where('order_id', $order->id)->first();
                if (!is_null($OrderItem)) {
                    if (in_array($OrderItem->product_id, $ProductId)) {
                        $Ip = Ip::where('category_id', $Category->id)->where('status', 0)->first();
                        if (!is_null($Ip)) {
                            $Product = Product::find($OrderItem->product_id);

                            $ProductDetail = json_decode($Product->detail, true);
                            $Day = $ProductDetail['day'];
                            $ExpireTime = Carbon::now()->addDays($Day);

                            $Vol = $ProductDetail['volume'];
                            $ExtraVol = __('order.Volume-Gb.' . $OrderItem->detail);
                            $Vol = $Vol + $ExtraVol;

                            $Ip->order_id = $order->id;
                            $Ip->user_id = $order->user_id;
                            $Ip->status = 1;
                            $Ip->volume = $Vol;
                            $Ip->expire_date = $ExpireTime;
                            $Ip->product_id = $OrderItem->product_id;
                            $Ip->save();

                            $order->status = 'complete';
                            $order->save();
                        }
                    }
                }
            }
            return success($request, 'آی پی با موفقیت بروز شد.');
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* افزودن کاربر*/
    public function Addv2Ray($ServerIp, $Category, $Name, $IP, $type)
    {
        $Data = json_encode([
            "secret" => "sec",
            'remark' => $Name,
            "protocol" => "vmessTcp",
        ]);
        $ress = AddUser($Data, $ServerIp);
        if ($ress[0] == 200) {
            $UserIp_detail = [
                'user_port' => $ress[1]['Port'],
                'user_remark' => $Name,
                'user_code' => $ress[1]['Url'],
            ];

            $UserIp = new Ip();
            $UserIp->status = 0;
            $UserIp->type = $type . '-user';
            $UserIp->detail = json_encode($UserIp_detail);
            $UserIp->parent_id = $IP->id;
            $UserIp->category_id = $Category->id;
            $UserIp->save();
            return $UserIp->id;
        }
    }

    /* ویرایش صفحه */
    public function Setting()
    {
        $Setting = Pages::find(5);
        return view('admin-v2.ip-sabet.setting', compact('Setting'));
    }

    /* ثبت اپل آیدی آماده */
    public function submit(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'h1' => 'required',
                'short_detail' => 'required',
                'detail' => 'required',
            ], [

                'h1.required' => 'لطفا تگ را وارد نمایید.',
                'short_detail.required' => 'لطفا توضیحات کوتاه را وارد نمایید.',
                'detail.required' => 'لطفا جزئیات کامل را وارد نمایید.',
            ]);
        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        try {

            $Seo = Add_Seo($request);
            $Faq = Add_Faq($request);
            $Page = Pages::find(5);
            $Page->h1 = $request->input('h1');
            $Page->short_detail = $request->input('short_detail');
            $Page->long_detail = $request->input('detail');
            $Page->image = $request->input('image_gallery');
            $Page->faq = $Faq;
            $Page->seo = $Seo;
            $Page->save();

            return response()->json(['success' => 'صفحه با موفقیت ویرایش شد.']);
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

}
