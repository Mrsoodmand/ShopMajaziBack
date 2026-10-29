<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Pages;
use App\Models\Product;
use App\Models\Plan;
use App\Models\Currency;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    /* لیست اکانت ها */
    public function index(Request $request)
    {
        $Product = Product::where('type', 'accounts')->where('status', 1)->get();
        $Page = Pages::find(7);
        $Comment = Comment::where('type', 'account')->where('post_id', 1)->where('status', 1)->orderby('id', 'DESC')->select('parent_id', 'name', 'text', 'created_at', 'id')->get();
        return view('site-v2.accounts.index', compact('Page', 'Comment','Product'));

    }

    /* جزئیات سرویس های مجازی */
    public function SingleAccount($slug)
    {

        $Product = Product::where('slug', $slug)->where('status', 1)->first();
        if (!is_null($Product)) {
            $Plans = Plan::where('product_id', $Product->id)->orderby('sort', 'ASC')->where('status', 1)->get()->toarray();
            $Related = Product::where('type', 'accounts')->where('id', '!=', $Product->id)->where('status', 1)->get();
            $Currency = CalculatorSellPrice(Currency::find(20));
            return response()->view('site-v2.accounts.single', compact('Product', 'Plans', 'Related', 'Currency'));

        } else {
            abort(404);
        }

    }
}
