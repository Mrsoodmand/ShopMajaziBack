<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Ip;
use App\Models\Pages;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class IpSabetController extends Controller
{
    /* لیست کشور ها */
    public function index(Request $request)
    {
        $Countries = Category::where('type', 'pu-ip-sabet')->get();
        $Access = [];
        foreach ($Countries as $country){
            $IP = Ip::where('category_id',$country->id)->where('type','pu-ip-sabet-user')->where('status',0)->first();
            if (!is_null($IP)){
                $Access[] = $country->id;
            }
        }
        $Products = Product::where('type', 'pu-ip-sabet')->where('category_id', $Countries->first()->id)->get();
        $Session = null;
        if (Auth::guard('user')->check() && Session::has('IpSabetData')) {
            $Session = Session::get('IpSabetData');
            Session::forget('IpSabetData');
        }
        $Page = Pages::find(5);
        $Comment = Comment::where('type','page')->where('post_id',5)->where('status',1)->orderby('id','DESC')->select('parent_id','name','text','created_at','id')->get();
        return view('site-v2.ip-sabet.index', compact('Countries', 'Products','Session','Page','Access','Comment'));
    }

    /* جزئیات تعرفه کشور */
    public function IpSabetProductDetailJson(Request $request)
    {
        $Countries = Category::where('type', 'pu-ip-sabet')->where('id', $request->input('id'))->first();
        if (!is_null($Countries)) {
            $Products = Product::where('type', 'pu-ip-sabet')->where('category_id', $Countries->id)->get();
            $Data = view('site-v2.ip-sabet.products-json', compact('Products'))->render();
            return response()->json(['data' => $Data], 200);
        } else {
            return response()->json(['error' => ['کشور مورد نظر یافت نشد']], 400);
        }
    }

    /* ثبت درخواست */
    public function SubmitOrder(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'country_id' => 'required|numeric',
                'plan_id' => 'required|numeric',
                'volume_id' => 'required|numeric',
            ],
            [
                'country_id.required' => 'لطفا کشور را انتخاب نمایید.',
                'plan_id.required' => 'لطفا پلن را انتخاب نماید.',
                'volume_id.required' => 'لطفا حجم را انتخاب نمایید.',
                'country_id.numeric' => 'فرمت کشور نامعتبر میباشد.',
                'plan_id.numeric' => 'فرمت تعرفه نامعتبر میباشد.',
                'volume_id.numeric' => 'فرمت حجم نامعتبر میباشد.',
            ]);
        if ($Validate->fails()) {
            return response()->json(['status' => 2, 'error' => $Validate->errors()->all()], 400);
        }
        try {
            $Country = Category::where('id', $request->input('country_id'))->where('type', 'pu-ip-sabet')->first();
            if (!is_null($Country)) {
                $Plan = Product::where('id', $request->input('plan_id'))->where('category_id', $Country->id)->first();
                if (!is_null($Plan)) {
                    if ($request->input('volume_id') >= 0 && $request->input('volume_id') <= 5) {
                        if (Auth::guard('user')->check()) {

                            $Data = json_decode($Plan->detail,true);
                            if($Data['dis'] == 0){
                                $Plan_price = $Plan->price;
                            }else{
                                $Plan_price = $Data['dis'];
                            }
                            $Price = __('order.Volume-Gb.'.$request->input('volume_id')) * 1500;
                            $Price = $Plan_price + $Price;
                            if (CheckWallet() >= $Price) {
                                $BTN = '<button type="button" id="second-btn" data-type="virtual-number" class="buy-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet" data-id="0" style="width: 47%">پرداخت با کیف پول</a>';
                            } else {
                                $BTN = '<a id="second-btn" href="'.url('user/wallet').'" class="login-btn btn btn-info btn-shadow d-block pe-3" style="width: 47%" type="submit">شارژ کیف پول</a>';
                            }
                            $Data['serviceName'] = $Country->name;
                            $Data['PlanName'] = $Plan->faName;
                            $Data['VolumeName'] = __('order.Volume-Name.'.$request->input('volume_id'));
                            $Data['price'] = number_format($Price);
                            $Data['image'] = url($Country->image);
                            $Data['btn'] = $BTN;
                            return response()->json(['data' => $Data], 200);
                        } else {
                            $Data = [
                                'country_id' => $request->input('country_id'),
                                'plan_id' => $request->input('plan_id'),
                                'volume_id' => $request->input('volume_id')
                            ];
                            Session::put('IpSabetData', $Data);
                            return response()->json(['status' => 1, 'error' => ['برای ادامه خرید، لطفا وارد حساب کاربری خود شوید.']], 400);
                        }
                    } else {
                        return response()->json(['status' => 2, 'error' => ['حجم مورد نظر یافت نشد.']], 400);
                    }
                } else {
                    return response()->json(['status' => 2, 'error' => ['پلن مورد نظر یافت نشد.']], 400);
                }
            } else {
                return response()->json(['status' => 2, 'error' => ['کشور مورد نظر یافت نشد.']], 400);
            }
        } catch (\Exception $exception) {
            return response()->json(['status' => 2, 'error' => [$exception->getMessage()]], 400);
        }
    }
}
