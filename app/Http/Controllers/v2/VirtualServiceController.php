<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Pages;
use Illuminate\Http\Request;

class VirtualServiceController extends Controller
{
    /* لیست دسته بندی های سرویس های مجازی */
    public function index(Request $request)
    {
        $Page = Pages::find(6);
        return view('site-v2.virtual-service.index',compact('Page'));
    }

    /* دسته بندی سرویس های مجازی */
    public function ServiceCategory($slug)
    {
        $Category = Category::where('type','product')->where('slug', $slug)->first();
        if (!is_null($Category)){
            $Products = Product::where('country_id', $Category->id)->orderby('id', 'DESC')->where('status',1)->where('type', 'service')->get();
            $Title = $Category->meta_title;
            $Currency = CalculatorSellPrice(Currency::find(20));

            return view('site-v2.virtual-service.service-category', compact('Category', 'Products', 'Title','Currency'));
        }else{
            abort(404);
        }
    }

    /* جزئیات سرویس های مجازی */
    public function SingleService($category = null, $slug)
    {
        $Category = Category::where('slug', $category)->first();
        if (!is_null($Category)) {
            $Product = Product::where('slug', $slug)->where('status',1)->first();
            if (!is_null($Product)) {
                if ($Product->country_id == $Category->id) {
                    $Plans = Plan::where('product_id', $Product->id)->get()->toarray();
                    $Related = Product::where('country_id',$Category->id)->where('id','!=',$Product->id)->where('status',1)->get();
                    $Currency = CalculatorSellPrice(Currency::find(20));

                    $Comment = Comment::where('type', 'service')->where('post_id', $Product->id)->where('status', 1)->orderby('id', 'DESC')->select('parent_id', 'name', 'text', 'created_at', 'id')->get();

                    return response()->view('site-v2.virtual-service.single', compact('Product','Category', 'Plans','Related','Currency','Comment'));
                } else {
                    abort(404);
                }
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }
    }
}
