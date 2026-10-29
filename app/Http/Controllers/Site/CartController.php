<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    // صفحه سبد خرید
    public function Cart()
    {
        $Products = Session::get('Cart');
        return view('user.checkout', compact('Products'));
    }

    // افزودن به سبد خرید
    public function AddToCart(Request $request)
    {
        $Product = Decode($request->input('product_id'));
        $Plane = Decode($request->input('plane_id'));
        if ($Product != 'error') {
            if ($Plane != 'error') {
                $CheckProduct = Product::find($Product);
                $CheckPlan = Plan::where('product_id', $Product)->where('id', $Plane)->first();
                if (!is_null($CheckPlan)) {
                    $ProductDetail = json_decode($CheckProduct->detail, true);
                    $Price = $CheckPlan->price;
                    if ($CheckPlan->discount != null && $CheckPlan->discount != 0) {
                        $Price = $CheckPlan->discount;
                    }
                    $Files = json_decode($CheckProduct->filds, true);
                    foreach ($Files as $value) {
                        $Title = $value['title'];
                        if ($value['type'] == 'text') {
                            $Validate['filed_value.' . $value['filed_name']] = 'required|max:150|min:3';
                            $Message['filed_value.' . $value['filed_name'] . '.required'] = "لطفا $Title را وارد نمایید";
                            $Message['filed_value.' . $value['filed_name'] . '.max'] = "حداکثر کاراکتر مجاز 150 کاراکتر برای $Title میباشد ";
                            $Message['filed_value.' . $value['filed_name'] . '.min'] = "حداقل کاراکتر مجاز 3 کاراکتر برای $Title میباشد ";
                        } elseif ($value['type'] == 'select_box') {
                            $SelectData = $value['select_value'];
                            $Validate['filed_value.' . $value['filed_name']] = "required|in:$SelectData";
                            $Message['filed_value.' . $value['filed_name'] . '.required'] = "لطفا $Title را وارد نمایید";
                            $Message['filed_value.' . $value['filed_name'] . '.in'] = "مقدار وارد شده برای $Title نامعتبر میباشد ";
                        }
                    }
                    $Validator = Validator::make($request->all(), $Validate, $Message);
                    if ($Validator->fails()) {
                        return validation_error($request, $Validator->errors()->all());
                    }

                    foreach ($request->input('filed_name') as $key => $value) {
                        $Detail[] = [
                            'filed_name' => $value,
                            'filed_type' => $request->input('filed_title')[$key],
                            'filed_value' => $request->input('filed_value')[$key],
                        ];
                    }
                    $Data = [
                        'product_id' => $Product,
                        'product_image' => $ProductDetail['image_gallery'],
                        'plan_id' => $Plane,
                        'price' => $Price,
                        'product_name' => $ProductDetail['name'],
                        'plan_name' => $CheckPlan->name,
                        'options' => json_encode($Detail),
                    ];
                    Session::put("Cart.$CheckPlan->id", $Data);
                    $total = 0;
                    foreach (\Illuminate\Support\Facades\Session::get('Cart') as $key => $value) {
                        $Card[] = [
                            'name' => $value['product_name'],
                            'product_image' => $value['product_image'],
                            'price' => $value['price'],
                            'plan_name' => $value['plan_name'],
                            'product_id' => Encode($value['product_id']),
                        ];
                        $total = $value['price'];
                    }
                    if ($request->ajax()){
                        return response(['success' => 'محصول با موفقیت به سبد خرید افزوده شد.','Card' => $Card,'total' => $total],200);
                    }else{
                        return back();
                    }
                }
                return exception_error($request, 'پلن مورد نظر یافت نشد');
            } else {
                return exception_error($request, 'شناسه پلن نا معبتر میباشد');
            }
        } else {
            return exception_error($request, 'شناسه محصول نا معبتر میباشد');
        }
    }

    // حذف از سبد خرید
    public function DeleteFromCart($id)
    {
        $id = Decode($id);
        if ($id != 'error') {
            Session::forget('Cart.' . $id);
            return back();
        }
        return back();
    }


}
