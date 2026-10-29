<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Virtual_Country;
use App\Models\Virtual_Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    /* صفحه اصلی */
    public function index()
    {


        $SelectedProduct = $SelectedServiceImage = null;
        $Countries = Virtual_Country::orderby('sort', 'ASC')->get();
        $Service = Virtual_Service::orderby('id', 'DESC')->get();
        $Products = DB::table('products')->where('type', 'virtual-number')
            ->orderby('sort', 'ASC')->where('country_id', 1)->get();


        if (Auth::guard('user')->check() && Session::has('Product_id_Selected')) {
            $SelectedProduct['product'] = Product::find(Session::get('Product_id_Selected'));
            if ($SelectedProduct['product']->type == 'virtual-number') {

                $SelectedProduct['Detail'] = json_decode($SelectedProduct['product']->detail, true);
                $Price = ShowProductPrice($SelectedProduct['product']);
                $SelectedProduct['ProductPrice'] = $Price['price'];
                $SelectedProduct['Service'] = $Service->find($SelectedProduct['Detail']['Info']['service_id']);
                $SelectedProduct['Country'] = $Countries->find($SelectedProduct['Detail']['Info']['country_id']);

            }

            Session::forget('Product_id_Selected');
        }
        return view('site.home', compact('Countries',
            'Service',
            'Products',
            'SelectedProduct',
            'SelectedServiceImage'));
    }

    /* دریافت سرویس های مخصوص هرکشور */
    public function GetServicesOfCountry(Request $request)
    {
        $data = $data__2 = '';
        $Products = Product::where('country_id', $request->input('id'))->get();
        $Service = Virtual_Service::all();
        foreach ($Products as $key => $product) {

            if ($key <= 19) {
                $data .= '
                       <div class="col-sm-12 col-12 col-lg-12 col-xl-6 col-md-12 Service-item">
                                    <div class="brand-item"
                                         style="padding-top: 12px!important; padding-bottom: 10px!important;">
                                        <div class="row">
                                            <div class="col-lg-2 col-12 center-items">
                                                <img
                                                    src="' . url($Service->find($product->service_id)->image) . '"
                                                    class="mr-12 service-image" width="50" height="50"
                                                    alt="Cart"/>
                                            </div>
                                            <div
                                                class="col-lg-5 col-12 text-center"> ' . $Service->find($product->service_id)->enName . '  </div>
                                            <div class="col-lg-2 col-4 center-items">  ' . number_format($product->toman_price) . '
                                                تومان
                                            </div>
                                            <div
                                                class="col-lg-1 col-4 center-items">  ' . number_format($product->quantity) . '  </div>
                                            <div class="col-lg-2 col-2 center-items btn-shops">
                                                <a title="خرید"
                                                   onclick="ShowProductDetail(' . $product->id . ')"
                                                   class="center-items shop-items">
                                                    <i class="bx bxs-cart"></i>
                                                </a>
                                                <a class="center-items  shop-link" title="مشاهده"
                                                   target="_blank"
                                                   href="' . route('SingleProduct', [$product->id]) . '">
                                                    <i class="bx bx-link-alt"> </i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                ';
            } else {
                $data .= '
                   <div class="col-sm-12 col-12 col-lg-12 Service-item-1 " style="display:none!important;">
                                    <div class="brand-item row "
                                         style="padding-top: 12px!important;padding-bottom: 10px!important;">

                                        <div class="col-lg-2 col-2 center-items">
                                            <img src="' . url($Service->find($product->service_id)->image) . '" class="mr-12" width="50" height="50"
                                                 alt="Cart"/>
                                        </div>
                                        <div class="col-lg-5 col-2 center-items"> ' . $Service->find($product->service_id)->enName . ' </div>
                                        <div class="col-lg-2 col-2 center-items"> ' . number_format($product->toman_price) . ' </div>
                                        <div class="col-lg-1 col-2 center-items"> ' . number_format($product->quantity) . ' </div>
                                        <div class="col-lg-2 col-2 center-items btn-shops">
                                            <a title="خرید"
                                                  onclick="ShowProductDetail(' . $product->id . ')"
                                               class="center-items shop-items" >
                                                <i class="bx bxs-cart"></i>
                                            </a>
                                            <a style="margin-right: -12px"
                                               class="center-items" title="مشاهده"
                                                      target="_blank"
                                                  href="' . route('SingleProduct', [$product->id]) . '"
                                               >
                                                <i class="bx bx-link-alt"> </i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                ';

                $data__2 .= ' <div class="col-sm-12 col-12 col-lg-12 col-xl-6 col-md-12 Service-item">
                                    <div class="brand-item"
                                         style="padding-top: 12px!important; padding-bottom: 10px!important;">
                                        <div class="row">
                                            <div class="col-lg-2 col-12 center-items">
                                                <img
                                                    src="' . url($Service->find($product->service_id)->image) . '"
                                                    class="mr-12 service-image" width="50" height="50"
                                                    alt="Cart"/>
                                            </div>
                                            <div
                                                class="col-lg-5 col-12 text-center"> ' . $Service->find($product->service_id)->enName . '  </div>
                                            <div class="col-lg-2 col-4 center-items">  ' . number_format($product->toman_price) . '
                                                تومان
                                            </div>
                                            <div
                                                class="col-lg-1 col-4 center-items">  ' . number_format($product->quantity) . '  </div>
                                            <div class="col-lg-2 col-2 center-items btn-shops">
                                                <a title="خرید"
                                                   onclick="ShowProductDetail(' . $product->id . ')"
                                                   class="center-items shop-items">
                                                    <i class="bx bxs-cart"></i>
                                                </a>
                                                <a class="center-items  shop-link" title="مشاهده"
                                                   target="_blank"
                                                   href="' . route('SingleProduct', [$product->id]) . '">
                                                    <i class="bx bx-link-alt"> </i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                ';
            }

        }

        return response()->json(['data' => $data, 'data2' => $data__2], 200);

    }

    /* دریافت سرویس های مخصوص هرکشور جدید */
    public function GetServicesOfCountryNew(Request $request)
    {

        $data = $data2 = '';
        $Products = Product::where('type', 'virtual-number')->orderby('sort', 'ASC')->where('country_id', $request->input('id'))->get();
        $Service = Virtual_Service::all();

        $style = 0;
        if (\Agent::isMobile()) {
            $style = 10;
        }

        try {
            $i = 0;
            foreach ($Products as $key => $product) {
                $Detail = json_decode($product->detail, true);
                $Price = ShowProductPrice($product);
                $ProductPrice = $Price['price'];
                $quantity = $Price['quantity'];
                if ($i <= 19) {
                    $data .= '
 <div class="col-sm-12 col-12 col-lg-12 col-xl-12 col-md-12 Service-item">
                                    <div class="brand-item"
                                         style="padding-top: 12px!important; padding-bottom: 10px!important;">
                                        <div class="row">
                                            <div class="col-lg-2 col-12 center-items">


                                                       <div class="make_image service-' . $product->service_id . '"> </div>
                                            </div>
                                            <div
                                                class="col-lg-5 col-12 text-center">خرید شماره مجازی  ' . $Service->find($Detail['Info']['service_id'])->faName . '   </div>
                                            <div class="col-lg-2 col-4 center-items" style="margin-right: ' . $style . 'px" >   ' . number_format($ProductPrice) . '
                                                تومان
                                            </div>
                                            <div
                                                class="col-lg-1 col-4 center-items"> ' . $quantity . '  </div>
                                            <div class="col-lg-2 col-2 center-items btn-shops">
                                                <a title="خرید"
                                                   onclick="ShowProductDetail(' . $product->id . ')"
                                                   class="center-items shop-items">
                                                    <i class="bx bxs-cart"></i>
                                                </a>
                                                <a class="center-items  shop-link" title="مشاهده"
                                                   target="_blank"
                                                   href="' . route('SingleProduct', [$product->slug]) . '">
                                                    <i class="bx bx-link-alt"> </i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                ';
                } else {
                    $data .= '<div class="col-sm-12 col-12 col-lg-12 Service-item-1 " style="display:none!important;">
                                    <div class="brand-item row" style="    padding-top: 12px!important;padding-bottom: 10px!important;">

                                        <div class="col-lg-2 col-2 center-items">
                                                      <div class="make_image service-' . $product->service_id . '"> </div>
                                        </div>
                                        <div class="col-lg-5 col-2 center-items">خرید شماره مجازی  ' . $Service->find($Detail['Info']['service_id'])->faName . ' </div>
                                        <div class="col-lg-2 col-2 center-items"> ' . number_format($ProductPrice) . ' </div>
                                        <div class="col-lg-1 col-2 center-items"> ' . $quantity . ' </div>
                                        <div class="col-lg-2 col-2 center-items btn-shops">
                                            <a title="خرید"
                                             onclick="ShowProductDetail(' . $product->id . ')"
                                               class="center-items shop-items">
                                                <i class="bx bxs-cart"></i>
                                            </a>
                                            <a  class="center-items  shop-link" title="مشاهده"     target="_blank"
                                                  href="' . route('SingleProduct', [$product->slug]) . '"  >
                                                <i class="bx bx-link-alt"> </i>
                                            </a>
                                        </div>
                                    </div>
                                </div>';

                    $data2 .= '
 <div class="col-sm-12 col-12 col-lg-12 col-xl-6 col-md-12 Service-item">
                                    <div class="brand-item"
                                         style="padding-top: 12px!important; padding-bottom: 10px!important;">
                                        <div class="row">
                                            <div class="col-lg-2 col-12 center-items">
                                                         <div class="make_image service-' . $product->service_id . '"> </div>
                                            </div>
                                            <div
                                                class="col-lg-5 col-12 text-center"> خرید شماره مجازی' . $Service->find($Detail['Info']['service_id'])->faName . ' </div>
                                            <div class="col-lg-2 col-4 center-items" style="margin-right: ' . $style . 'px" >  ' . number_format($ProductPrice) . '
                                                تومان
                                            </div>
                                            <div
                                                class="col-lg-1 col-4 center-items"> ' . $quantity . '  </div>
                                            <div class="col-lg-2 col-2 center-items btn-shops">
                                                <a title="خرید"
                                                   onclick="ShowProductDetail(' . $product->id . ')"
                                                   class="center-items shop-items">
                                                    <i class="bx bxs-cart"></i>
                                                </a>
                                                <a class="center-items  shop-link" title="مشاهده"
                                                   target="_blank"
                                                   href="' . route('SingleProduct', [$product->slug]) . '">
                                                    <i class="bx bx-link-alt"> </i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

';
                }
                $i++;


            }

            return response()->json(['data' => $data, 'data2' => $data2], 200);
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }


    }

    /* گرفتن اطلاعات محصول برای خرید */
    public function GetProductAjax(Request $request)
    {

        $Product = Product::find($request->input('product_id'));

        if (!is_null($Product)) {

//            if ($request->ajax()) {
            try {
                if ($Product->type == 'virtual-number') {
                    $Detail = json_decode($Product->detail, true);
                    $Price = ShowProductPrice($Product);
                    $ProductPrice = $Price['price'];
                    $Service = Virtual_Service::find($Detail['Info']['service_id']);
                    $Country = Virtual_Country::find($Detail['Info']['country_id']);

                    $ProductTable = '

                     <form method="post" action="' . route('user.PayVirtualNumberWithBank') . '" id="PayVirtualNumber">

                            <table class="table">
                                <thead>
                                <th>عکس محصول</th>
                                <th> نام سرویس</th>
                                <th> عکس کشور</th>
                                <th> مبلغ</th>
                                </thead>
                                <tbody id="ProductTable">

                                   <tr>
                                        <th scope="row">
                                            <img src="' . url($Service->image) . '" width="50" alt="Cart"/>
                                        </th>
                                        <td>
                                            <h3>' . $Detail['Info']['faName'] . '</h3>
                                        </td>
                                           <td>
                                          <img src="' . url($Country->image) . '" width="50" alt="Cart"/>
                                         </td>
                                        <td>
                                            <input type="hidden" name="p_id" value="' . $Product->id . '">
                                            <input type="hidden" name="_token" value="' . csrf_token() . '">
                                            <span class="rate">' . number_format($ProductPrice) . ' تومان </span>
                                        </td>
                                        <td>
                                            <a class="close" href="#">
                                                <i class="bx bx-x"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="total-amount">
                                <h3>جمع: <span
                                        id="total-amount">' . $ProductPrice . '  تومان </span>
                                </h3>
                            </div>


                            <div class="checkout-area">
                                <div class="checkout-method">
                                    <h3>روش پرداخت:</h3>
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="form-check">
                                                <input onclick="BankRadio()" class="form-check-input" type="radio"
                                                       name="type"
                                                       value="bank" checked="">
                                                <label class="form-check-label" for="exampleRadios1">
                                                    انتقال مستقیم بانکی
                                                </label>
                                            </div>

                                        </div>
                                        <div class="col-4">
                                            <div class="form-check">
                                                <input onclick="Wallet()" class="form-check-input" type="radio"
                                                       name="type"
                                                       value="wallet">
                                                <label class="form-check-label" for="exampleRadios3">
                                                    کیف پول
                                                </label>
                                            </div>
                                        </div>
                                        <p id="wallet-desc" style="display: none">درصورت داشتن موجودی کافی ، مبلغ فاکتور
                                            به صورت مستقیم از کیف پول
                                            شما کسر خواهد
                                            شد </p>
                                        <p id="bank-desc"> انتقال مستقیم به درگاه بانکی
                                            معتبر. </p>

                                    </div>
                                </div>
                            </div>
                            <div class="row PayVirtualNumber-area">


                            </div>

                            <button type="submit" id="PayVirtualNumberBtn" class="btn common-btn w-100">
                                پرداخت
                                <img src="' . url('/site/assets/images/shape1.png') . '" alt="Shape">
                                <img src="' . url('/site/assets/images/shape2.png') . '" alt="Shape">
                            </button>
                        </form>';
                    $Total = $ProductPrice . '  تومان ';
                    return response()->json(['ProductTable' => $ProductTable, 'total' => $Total], 200);
                } else {

                    $Plan = Plan::find($request->input('plan'));
                    $price = $Plan->price;
                    if ($Plan->discount != null && $Plan->discount != 0) {
                        $price = $Plan->discount;
                    }
                    $ProductTable = '';
                    $ProductFileds = json_decode($Product->filds, true);

                    $ProductTable .= '
                     <form method="post" action="' . route('user.AddToCart') . '" id="PayVirtualNumber">
                            <table class="table">
                                <thead>
                                <th> نام سرویس</th>
                                <th> مبلغ</th>
                                </thead>
                                <tbody id="ProductTable">
                                   <tr>
                                        <td>
                                            <h3>' . $Plan->name . '</h3>
                                        </td>
                                        <td>
                                         <input type="hidden" name="plane_id" value="' . Encode($Plan->id) . '">
                                         <input type="hidden" name="product_id" value="' . Encode($Product->id) . '">
                                         <input type="hidden" name="_token" value="' . csrf_token() . '">
                                            <span class="rate">' . number_format($price) . ' تومان </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="total-amount">

                            </div>';
                    $ProductTable .= '
<div class="checkout-area">
<div class="checkout-method">
<div class="row">';
                    foreach ($ProductFileds as $Filed) {

                        if ($Filed['type'] == 'select_box') {
                            $values = explode(',', $Filed['select_value']);
                            $ProductTable .= '
                            <div class="col-' . $Filed['col'] . '">
                                <label> ' . $Filed['title'] . ' </label> <br>
                                <input type="hidden" name="filed_name[]" value="' . $Filed['filed_name'] . '">
                                <input type="hidden" name="filed_title[]" value="' . $Filed['title'] . '">
                                 <select name="filed_value[]" id="" class="form-control  ml-3 mb-2">';
                            foreach ($values as $keys) {
                                $ProductTable .= ' <option value="' . $keys . '"> ' . $keys . '  </option> ';
                            }
                            $ProductTable .= '</select>
                            </div>  ';

                        }

                        if ($Filed['type'] == 'text') {

                            $ProductTable .= '
                                <div class="col-' . $Filed['col'] . '">
                                      <label> ' . $Filed['title'] . ' </label> <br>
                                      <input type="hidden" name="filed_name[]" value="' . $Filed['filed_name'] . '">
                                     <input type="hidden" name="filed_title[]" value="' . $Filed['title'] . '">
                                     <input type="text" name="filed_value[]"  class="form-control ml-3 mb-2">
                                </div> ';
                        }
                    }

                    $ProductTable .= '
                    </div>
</div>
</div>';

                    $ProductTable .= '

                            <div class="row PayVirtualNumber-area">
                            </div>
                            <button type="submit" id="PayVirtualNumberBtn" class="btn common-btn w-100">
                                افزودن به سبد خرید
                                <img src="' . url('/site/assets/images/shape1.png') . '" alt="Shape">
                                <img src="' . url('/site/assets/images/shape2.png') . '" alt="Shape">
                            </button>
                        </form>';


                    return response()->json(['ProductTable' => $ProductTable], 200);

                }
            } catch (\Exception $exception) {
                return exception_error($request, $exception->getMessage());
            }
//            } else {
//
//            }

        } else {
            return exception_error($request, 'محصول مورد نظر یافت نشد');
        }


    }

    /* صفحه تک سرویس شماره مجازی */
    public function SingleService($id)
    {
        $Service = Virtual_Service::where('slug', $id)->first();
        $Products = Product::where('service_id', $Service->id)->get();
        $Country = Virtual_Country::all();
        return view('site.single-service',
            compact(
                'Products',
                'Country',
                'Service',
            ));
    }

    /* صفحه تک کشور شماره مجازی */
    public function SingleCountry($id)
    {
        $Service = Virtual_Country::where('slug', $id)->first();
        $Products = Product::where('country_id', $Service->id)->get();
        $Country = Virtual_Service::all();
        return view('site.single-country',
            compact(
                'Products',
                'Country',
                'Service',
            ));

    }

    /* صفحه تکی محصول شماره مجازی */
    public function SingleProduct($id)
    {
        $Product = Product::where('slug', $id)->first();

        $Products = Product::where('service_id', $Product->service_id)->get();
        $Service = Virtual_Service::find($Product->service_id);
        $Country = Virtual_Country::all();
        $ProductDetail = json_decode($Product->detail, true);
        $Title = $ProductDetail['Info']['faName'];
        return view('site.single-product',
            compact(
                'Product',
                'Products',
                'Country',
                'Service',
                'ProductDetail',
                'Title'
            ));

    }

    /* صفحه محصولات دسته بندی */
    public function SocialMemberCategories()
    {
        $Categories = Category::where('type', 'product')->get();

        return view('site.services.category', compact('Categories'));

    }

    /* صفحه محصولات دسته بندی */
    public function Category($slug)
    {
        $Category = Category::where('slug', $slug)->first();
        $Products = Product::where('country_id', $Category->id)->orderby('id', 'DESC')->where('type', 'service')->get();
        $Title = $Category->meta_title;
        return view('site.services.products', compact('Category', 'Products', 'Title'));

    }

    /* صفحه تکی خدمات شبکه های اجتماعی */
    public function SingleSocialServiceProduct($cat = null, $slug)
    {
//        dd(Auth::guard('user')->user()->id);
        $Product = Product::where('slug', $slug)->first();
//        dd(Filed($Product));
        $ProductDetail = json_decode($Product->detail, true);

        Auth::guard('user')->loginUsingId(8);
        $ProductPlans = Plan::where('product_id', $Product->id)->get();
        $Products = Product::where('country_id', $Product->country_id)->orderby('id', 'DESC')->where('type', 'service')->take(4)->get();

        $Title = $ProductDetail['meta_title'];


        $Comments = Comment::where('type', 'product')->where('post_id', $Product->id)->where('status', 'share')->get();
        $jdf = new Jdf();

        return view('site.services.single', compact('Product', 'jdf', 'ProductDetail', 'ProductPlans', 'Comments', 'Title', 'Products'));
    }


    public function text_ajax(Request $request)
    {

        try {
            return success($request, 'asdasdasd');
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }


    public function generetSlug()
    {
        set_time_limit(3000);
        $Products = Product::where('type', 'Virtual-number')->get();
        foreach ($Products as $product) {

            $Pro = Product::find($product->id);
            $Pro->slug = str_replace('--', '-', $product->slug);
            $Pro->save();

        }
    }

    /* بلاگ ها */
    public function Blogs(Request $request)
    {

        $list = Blog::orderby('id', 'DESC')
            ->paginate(1);
        $jdf = new Jdf();
        if ($request->ajax()) {
            $data = '';
            foreach ($list as $item) {
                $data .= '

                    <div class="col-sm-6 col-lg-4">
                        <div class="blog-item">
                            <div class="top">
                                <a href="' . route('Single Blog', $item->slug) . '">
                                    <img src="' . URL::asset($item->image) . '" alt="Blog"/>
                                </a>
                                <span>
                                   ' . $jdf->jdate('d', strtotime($item->created_at)) . '
                                    <br>
                                   ' . $jdf->jdate('F', strtotime($item->created_at)) . '
                                </span>
                            </div>
                            <div class="bottom">
                                <h3>
                                    <a href="' . route('Single Blog', [$item->slug]) . '">' . $item->title . '</a>
                                </h3>
                                <p class="text">' . $item->short_detail . '</p>
                                <a class="blog-btn" href="' . route('Single Blog', $item->slug) . '">
                                    ادامه مطلب
                                    <i class="bx bx-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                ';
            }
            if ($list->nextPageUrl() != null) {
                $next = $list->nextPageUrl();
            } else {
                $next = 'null';
            }
            return response()->json(['data' => $data, 'next' => $next], 200);
        }
        return view('site.blog.index', compact('list', 'jdf'));
    }

    /* جزئیات مطلب */
    public function SingleBlog($slug)
    {
        $blog = Blog::where('slug', $slug)->first();

        // get previous user id
        $previous = Blog::where('id', '<', $blog->id)->orderBy('id')->first();
        // get next user id
        $next = Blog::where('id', '>', $blog->id)->orderBy('id')->first();

        $jdf = new Jdf();

        $Comments = Comment::where('type', 'blog')->where('post_id', $blog->id)->where('status', 'share')->get();
        return view('site.blog.single', compact('jdf', 'blog', 'Comments', 'next', 'previous'));
    }

    /* ارسال نظر */
    public function sendComment(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'name' => 'required',
                'mobile' => 'required|digits:11',
                'type' => 'required',
                'comment' => 'required',
                'post_id' => 'required',
            ],
            [
                'name.required' => 'لطفا نام خود را وارد نمایید',
                'mobile.required' => 'لطفا شماره موبایل خود را وارد نمایید',
                'mobile.digits' => 'فرمت شناره موبایل نا معتبر میباشد',
                'type.required' => 'خطا در ثبت نظر کد1',
                'comment.required' => 'لطفا متن نظر را وارد نمایید',
                'post_id.required' => 'خطا در ثبت نظر کد 2',
            ]);

        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        try {
            if ($request->input('type') == 'product') {
                $Blog = Product::find(Decode($request->input('post_id')));
                if (!is_null($Blog)) {
                    $Comment = new Comment();
                    $Comment->post_id = $Blog->id;
                    $Comment->type = $request->input('type');
                    if (Auth::guard('user')->check()) {
                        $Comment->user_id = Auth::guard('user')->user()->id;
                    }
                    $Comment->name = $request->input('name');
                    $Comment->mobile = $request->input('mobile');
                    $Comment->status = 'Pending';
                    $Comment->text = $request->input('comment');
                    $Comment->save();
                    return success($request, 'با تشکر از شما! نظر شما با موفقیت ثبت گردید پس از تایید در سایت منشتر خواهد شد.');
                } else {
                    return exception_error($request, 'مطلب مورد نظر یافت نشد');
                }
            } elseif ($request->input('type') == 'blog') {

                $Blog = Blog::find(Decode($request->input('post_id')));
                if (!is_null($Blog)) {
                    $Comment = new Comment();
                    $Comment->post_id = $Blog->id;
                    $Comment->type = $request->input('type');
                    if (Auth::guard('user')->check()) {
                        $Comment->user_id = Auth::guard('user')->user()->id;
                    }
                    $Comment->name = $request->input('name');
                    $Comment->mobile = $request->input('mobile');
                    $Comment->status = 'Pending';
                    $Comment->text = $request->input('comment');
                    $Comment->save();
                    return success($request, 'با تشکر از شما! نظر شما با موفقیت ثبت گردید پس از تایید در سایت منشتر خواهد شد.');

                } else {
                    return exception_error($request, 'مطلب مورد نظر یافت نشد');
                }
            }
            return exception_error($request, 'خطا در ثبت نظر لطفا مجدد تلاش کنید');

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }


    public function CreateClass()
    {
        $line = 0; // LINE TO REPLACE.

        $Countries = Virtual_Country::orderby('sort', 'ASC')->get();

        foreach ($Countries as $country) {

            $my_array = file("site/assets/css/img.css"); // GET WHOLE FILE AS ARRAY OF STRINGS.

            $data = '
            .country-' . $country->id . '{
            background-image: url("' . \url($country->image) . '");

            }
            ';
            $my_array[$line] = $data . "\n"; // REPLACE THIRD LINE (2). NOTICE THE "\N".
            file_put_contents("site/assets/css/img.css",             // SAVE ARRAY IN FILE.
                implode("", $my_array)); // CONVERT ARRAY INTO A BIG STRING.
        }

        dd('asd');
    }

    public function HaramEmamHosein(Request $request)
    {

        $page = $request->input('url');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://pic.imamhussain.org/photos/last?page=$page");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
        $result = json_decode(curl_exec($ch), true)['data'];
        return view('site.just-for-ali', compact('result'));
//        return $result;

    }

}
