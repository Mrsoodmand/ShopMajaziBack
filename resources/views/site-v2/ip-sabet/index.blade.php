@extends('site-v2.layout.master',['MetaTitle' => ' خرید آی پی ثابت مخصوص ترید و بازار های مالی 📈 | تحویل آنی'])
@section('content')
    <section class="position-relative bg-size-cover bg-position-center-x position-relative bg-header">
        <div class="container position-relative zindex-5 py-4 my-3">
            <div class="row justify-content-center">
                @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                    <div class="col-lg-3 col-12">
                        <img class="header-image gallery-item rounded-3 header-image" width="276" height="276"
                             src="{{url('/image/static-ip.png')}}" alt="Static-Ip">
                    </div>
                @endif
                <div class="col-lg-9 col-12">
                    <h1 class="h1-color">خرید آی پی ثابت مخصوص ترید و رفع تحریم سایت های خارجی</h1>
                    <p class="pb-3 text-white m-text-justify">
                        شاید برای شما این سوال به وجود آید که چرا باید از ای پی ثابت استفاده کنیم و این موضوع چه فرقی با
                        سرویس‌های ای پی متغییر دارد. زمانی که ای پی شما به صورت دائم در حال تغییر است، این موضوع
                        می‌تواند اکانت شما را تحت تاثیر قرار دهد به عنوان مثال اکانت شبکه‌های مجازی، اکانت صرافی‌ها به
                        تغییر آی پی حساسیت دارند و ممکن است که خدمات شما را قطع کنند. ما در شاپ مجازی این امکان را برای
                        شما فراهم کردیم که به راحتی و با یک آی پی ثابت فعالیت های روزانه خود را انجام دهید و نگران تغییر
                        آی پی بعد از هر قطع و وصل کردن نباشد.
                    </p>
                    <div class="d-table scale-up delay-4 mx-auto mx-lg-0">
                        <button class="btn btn-primary slow-scroll m-submit-order"
                                data-target="CountryArea">ثبت سفارش
                        </button>
                        <button class="btn btn-info slow-scroll m-faq-button"
                                data-target="FaqArea">سوالات متدوال
                        </button>
                        @if(!\Illuminate\Support\Facades\Auth::guard('user')->check())
                            <a class="btn btn-primary m-faq-button" target="_blank"
                               href="{{secure_url('user/login')}}">ورود یا ثبت نام</a>
                        @endif
                    </div>
                </div>
                  @if(!\Jenssegers\Agent\Facades\Agent::isPhone())
                    <div class="col-lg-3 col-12">
                        <img class="header-image gallery-item rounded-3 header-image" width="276" height="276"
                             src="{{secure_url('/image/static-ip.png')}}" alt="Static-Ip">
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="container pt-4 mb-3 text-center">
        <label class="h3 text-center pt-2">چگونه خرید کنم؟</label>
        <div class="row g-0 bg-light rounded-3">
            <div class="col-xl-4 col-lg-12 col-md-4 border-end">
                <div class="py-3">
                    <div class="align-items-center mx-auto py-1 px-1" style="text-align: center;max-width: 362px;">
                        <div class="ps-2"><i class="fa-regular fa-circle-1"></i>
                            <p class="mb-3 pt-1">ابتدا کشور مورد نظر را مشخص می نمایید.</p>
                        </div>
                    </div>
                    <hr class="d-md-none d-lg-block d-xl-none">
                </div>
            </div>
            <div class="col-xl-4 col-lg-12 col-md-4 border-end">
                <div class="py-3">
                    <div class="align-items-center mx-auto py-1 px-1" style="text-align: center;max-width: 362px;">
                        <div class="ps-2"><i class="fa-regular fa-circle-2"></i>
                            <p class="mb-3 pt-1">سپس تعرفه و حجم را انتخاب کرده.</p>
                        </div>
                    </div>
                    <hr class="d-md-none d-lg-block d-xl-none">
                </div>
            </div>
            <div class="col-xl-4 col-lg-12 col-md-4">
                <div class="py-3">
                    <div class="align-items-center mx-auto py-1 px-1" style="text-align: center;max-width: 362px;">
                        <div class="ps-2"><i class="fa-regular fa-circle-3"></i>
                            <p class="mb-3 pt-1">ثبت سفارش و پرداخت!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container ps-lg-4 pe-lg-3" id="CountryArea">
        @if(\Illuminate\Support\Facades\Session::has('error'))
            @foreach(\Illuminate\Support\Facades\Session::get('error') as $value)
                <div class="alert alert-danger">
                    {{$value}}
                </div>
            @endforeach
        @endif
        <section class="py-2 rounded-3 px-4 mt-5 shadow-lg mb-3">
            <div class="d-flex align-items-center ps-2 mb-2 m-country-area">
                <h2 class="h3 mb-0 me-2 custom-width-10">کشور</h2>
                @if(!\Jenssegers\Agent\Facades\Agent::isPhone())
                    <div class="input-group">
                        <i class="ci-search position-absolute top-50 start-0 translate-middle-y fs-md ms-3"></i>
                        <input class="form-control border-0 shadow-none" id="CountrySearch" type="text"
                               placeholder="جستجو بر اساس نام کشور">
                    </div>
                @endif
            </div>
            @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                <div class="d-flex align-items-center ps-2 shadow-sm mb-2 m-country-area-2">
                    <div class="input-group">
                        <i class="ci-search position-absolute top-50 start-0 translate-middle-y fs-md ms-3"></i>
                        <input class="form-control border-0 shadow-none" id="CountrySearch" type="text"
                               placeholder="جستجو بر اساس نام سرویس">
                    </div>
                </div>
            @endif
            <hr>
            <div class="row px-1 p-3 mb-2 rounded scrollbar-juicy-peach m-country-area-3">
                @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                    @foreach($Countries as $country)
                        @if(in_array($country->id,$Access))
                            <div class="col-md-3 col-sm-4 col-12 country-search box-area">
                                <div class="bg-white d-block country-item shadow-sm @if($country->id == 50) selected-country @endif rounded-3 py-3 py-sm-4 mb-grid-gutter get-country"
                                     id="country-{{$country->id}}" data-country="{{$country->id}}">
                                    <img class="country-image d-block mx-auto"
                                         width="40" height="40"
                                         src="{{secure_url(str_replace('http://localhost:8000/','',$country->image))}}"
                                         alt="{{$country->name}}">
                                    <span class="service-name-2">{{$country->name}}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @foreach($Countries as $country)
                        @if(!in_array($country->id,$Access))
                            <div class="col-md-3 col-sm-4 col-12 country-search box-area m-country-area-4">
                                <div class="not-available"></div>
                                <div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter"
                                     id="country-{{$country->id}}" data-country="{{$country->id}}">
                                    <img class="country-image d-block mx-auto"
                                         width="40" height="40"
                                         src="{{secure_url(str_replace('http://localhost:8000/','',$country->image))}}"
                                         alt="{{$country->name}}">
                                    <span class="service-name-2">{{$country->name}}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    @foreach($Countries as $country)
                        @if(in_array($country->id,$Access))
                            <div class="col-md-3 col-sm-4 col-12 country-search box-area m-country-area-4">
                                <div class="bg-white d-block country-item shadow-sm @if($country->id == 50) selected-country @endif rounded-3 py-3 py-sm-4 mb-grid-gutter @if(in_array($country->id,$Access)) get-country @endif"
                                     id="country-{{$country->id}}" data-country="{{$country->id}}">
                                    <img class="d-block mx-auto country-image"
                                         height="100" width="100"
                                         src="{{secure_url(str_replace('http://localhost:8000/','',$country->image))}}"
                                         alt="{{$country->name}}">
                                    <span class="service-name-2">{{$country->name}} </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @foreach($Countries as $country)
                            @if(!in_array($country->id,$Access))
                        <div class="col-md-3 col-sm-4 col-12 country-search box-area m-country-area-4">
                                <div class="not-available"></div>
                                <div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter"
                                     id="country-{{$country->id}}" data-country="{{$country->id}}">
                                    <img class="d-block mx-auto country-image"
                                         height="100" width="100"
                                         src="{{secure_url(str_replace('http://localhost:8000/','',$country->image))}}"
                                         alt="{{$country->name}}">
                                    <span class="service-name-2">{{$country->name}} </span>
                                </div>
                        </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </section>
        <!-- End Countries -->
        <!-- Products -->
        <section class="py-2 rounded-3 px-4 mt-5 shadow-lg mb-3 m-i-1" id="plans-area">
            <!-- Heading-->
            <div class="d-flex align-items-center ps-2 mb-2 m-country-area">
                <!-- Search-->
                <h2 class="h3 mb-0 me-2 custom-width-10">پلن ها</h2>
            </div>
            <hr>
            <!-- Grid-->
            <div class="row px-1 p-3 mb-2 rounded scrollbar-juicy-peach" id="Service-Area">
                @include('site-v2.ip-sabet.products-json')
            </div>
        </section>

        <!-- End Products -->
        <!-- Products -->
        <section class="py-2 px-4 rounded-3 mt-5 mb-3 shadow-lg m-i-1" id="Volume-Area">
            <!-- Heading-->
            <div class="d-flex align-items-center ps-2 mb-2 m-country-area">
                <!-- Search-->
                <h2 class="h3 mb-0 me-2 custom-width-10">حجم اضافه</h2>
            </div>
            <hr>
            <!-- Grid-->
            <div class="row px-1 p-3 mb-2 rounded scrollbar-juicy-peach">
                @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                    @for($i = 0;$i <= 5;$i++)
                        <div class="col-md-4 col-sm-4 col-12 service-search box-area m-country-area-6">
                            <div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter buy-volume volume-area"
                                 data-id="{{$i}}">
                                <div class="number-text">
                                    <span class="volume-name">@lang('order.Volume-Name.'.$i)</span>
                                    <div>
                                        @if($i != 0)
                                            @php $Price = __('order.Volume-Gb.'.$i) * 1500 @endphp
                                            <span class="price-area-2">  {{number_format($Price)}} تومان</span>
                                        @endif
                                    </div>
                                </div>
                                <button class="btn btn-icon me-2 btn-info service-buy-btn"
                                        data-service="{{$i}}" type="button" data-bs-toggle="tooltip"
                                        title="خرید"><i class="fa-regular fa-cart-shopping me-1"></i>انتخاب
                                </button>
                            </div>
                        </div>
                    @endfor
                @else
                    @for($i = 0;$i <= 5;$i++)
                        <div class="col-md-4 col-sm-4 col-12 service-search box-area mb-4 m-country-area-6">
                            <div class="d-block d-sm-flex p-2 align-items-start text-sm-start buy-volume shadow-sm volume-area volume-id-{{$i}}"
                                 data-id="{{$i}}">
                                <div class="pt-2 ms-2">
                                    <label class="product-title fs-base mb-2">@lang('order.Volume-Name.'.$i)</label>
                                    @if($i == 0)

                                    @elseif($i != 6)
                                        @php $Price = __('order.Volume-Gb.'.$i) * 1500 @endphp
                                        <label class="btn btn-icon service-fav-btn fs-md">
                                            @php $Price = __('order.Volume-Gb.'.$i) * 1500 @endphp
                                            <span>{{number_format($Price)}} تومان </span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                            <button class="btn btn-icon btn-info me-2 btn-sm w-100 mt-1 volume-area"
                                    data-id="{{$i}}" type="button" data-bs-toggle="tooltip" title="انتخاب"><i
                                        class="fa-regular fa-cart-shopping me-1"></i>انتخاب
                            </button>
                        </div>
                    @endfor
                @endif
            </div>
        </section>
        <div class="pb-3"></div>
    </section>
    @include('site-v2.components.why-shopmajazi')
    @if(!\Illuminate\Support\Facades\Auth::guard('user')->check())
        <!-- Start Content -->
        <section class="container ps-lg-4 pe-lg-3 pt-4 ">
            <section class="mb-3 pb-3">
                <div class="shadow-lg rounded-3 overflow-hidden">
                    <div class="row content-area">
                        <div class="col-lg-12 pt-2 pt-lg-4 pb-4">
                            <div class="pt-3 px-4 pe-lg-5 ps-lg-5">
                                <div class="post-container">
                                    {{--                                    <div class="gallery row pb-2" lg-uid="lg0">--}}
                                    {{--                                        <div class="col-sm-8 offset-lg-2"><img--}}
                                    {{--                                                    class="gallery-item rounded-3 mb-grid-gutter"--}}
                                    {{--                                                    src="{{secure_url('/site-v2/')}}/img/blog/single/01.jpg"--}}
                                    {{--                                                    alt="Gallery image">--}}
                                    {{--                                        </div>--}}
                                    {{--                                    </div>--}}
                                    <div class="post-content" id="content">
                                        {!! $Page->long_detail !!}
                                    </div>
                                    <div class="content-shadow">

                                    </div>
                                    <div class="show-more-btn">
                                        <button class="btn btn-primary btn-shadow more-btn"><i
                                                    class="ci-book mt-n1 me-2"></i>مشاهده
                                            بیشتر
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table>
                            <tbody>
                            <tr>
                                <th>نام سرویس</th>
                                <td>خرید آی پی ثابت</td>
                            </tr>
                            <tr>
                                <th>تحویل</th>
                                <td>آنی پس از خرید</td>
                            </tr>
                            <tr>
                                <th>قیمت</th>
                                <td>100,000 تومان</td>
                            </tr>
                            <tr>
                                <th>پشتیبانی</th>
                                <td>24 ساعته</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </section>
        <!-- End Content -->
    @endif
    @php $Faq = [
             [
            'q' => 'چرا باید آی پی ثابت خریداری کنم؟',
            'a' => 'با خرید آی پی ثابت شما میتونید آی پی خود را عوض کنید و از سایت های که ایران را تحریم کرده اند استفاده کنید.',
            ], [
            'q' => 'آیا خرید آی پی ثابت میتوانم در صرافی های حساس به آی پی ترید کنم؟',
            'a' => 'بله، فقط کافی است که کشور مورد نظر را انتخاب کنید ',
            ], [
            'q' => 'آیا پس از تمدید، آی پی من عوض خواهد شد؟',
            'a' => 'خیر، پس از تمدید، آی پی ثابتی که در حال استفاده کردن از آن هستید تمدید خواهد شد و آی پی شما عوض نخواهد شد.',
            ],
]
    @endphp
    @include('site-v2.components.faq',[$Faq])
    @include('site-v2.components.comment',[$Comment,'Type' => 'page' ,'Id' => '5'])
    <div class="modal fade" id="virtual-number" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item"><label class="nav-link fw-medium active" id="Login-tab-header"><i
                                        class="fa-regular fa-cart-shopping"></i>خرید آی پی ثابت</label></li>
                    </ul>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{route('user.service-bank-payment')}}"
                      class="needs-validation tab-pane fade show active" autocomplete="off" novalidate>
                    <div class="modal-body tab-content py-4">
                        <div class="table-responsive fs-md mb-4">
                            <table class="table table-hover mb-0 text-center m-i-2">
                                <thead>
                                <tr>
                                    <th>کشور</th>
                                    <th>پلن</th>
                                    <th>حجم</th>
                                    <th>مبلغ</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(\Illuminate\Support\Facades\Auth::guard('user')->check() && $Session != null)
                                    @php

                                        $Plan = App\Models\Product::where('type', 'pu-ip-sabet')->where('id', $Session['plan_id'])->first();
                                        $Data = json_decode($Plan->detail,true);
                                        if($Data['dis'] == 0){
                                            $Plan_price = $Plan->price;
                                        }else{
                                             $Plan_price = $Data['dis'];
                                        }
                                        $Price = __('order.Volume-Gb.'.$Session['volume_id']) * 1500;
                                        $Price = $Plan_price + $Price;
                                    @endphp
                                    <tr>
                                        <td class="py-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <img id="ProductImage"
                                                     src="{{secure_url($Countries->where('id',$Session['country_id'])->first()->image)}}"
                                                     width="39" alt="product-image">
                                                <div class="ps-2">
                                                    <span id="ProductName"
                                                          class="fw-medium text-heading me-1">{{$Countries->where('id',$Session['country_id'])->first()->name}}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 align-middle" id="PlanName">{{$Plan->faName}}</td>
                                        <td class="py-3 align-middle"
                                            id="VolumeName">@lang('order.Volume-Name.'.$Session['volume_id'])</td>
                                        <td class="py-3 align-middle" id="ProductPrice">{{number_format($Price)}}
                                            تومان
                                        </td>
                                        <input type="hidden" value="{{$Session['country_id']}}" id="country_id"
                                               name="country_id">
                                        <input type="hidden" value="{{$Plan->id}}" id="plan_id" name="plan_id">
                                        <input type="hidden" value="{{$Session['volume_id']}}" id="volume_id"
                                               name="volume_id">
                                        <input type="hidden" value="5" name="type">
                                        <input type="hidden" value="0" name="product_id">
                                        @csrf
                                    </tr>
                                @else
                                    <tr>
                                        <td class="py-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <img id="ProductImage"
                                                     src=""
                                                     width="39" alt="product-image">
                                                <div class="ps-2">
                                                    <span id="ProductName"
                                                          class="fw-medium text-heading me-1"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 align-middle" id="PlanName"></td>
                                        <td class="py-3 align-middle" id="VolumeName"></td>
                                        <td class="py-3 align-middle" id="ProductPrice"></td>
                                        <input type="hidden" value="5" name="type">
                                        <input type="hidden" value="0" name="product_id">
                                        <input type="hidden" value="" id="country_id" name="country_id">
                                        <input type="hidden" value="" id="plan_id" name="plan_id">
                                        <input type="hidden" value="" id="volume_id" name="volume_id">
                                        @csrf
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        @if(\Illuminate\Support\Facades\Auth::guard('user')->check() && $Session != null)
                            @if(Auth::guard('user')->user()->wallet >= 2000)
                                <a id="second-btn" data-type="1" type="button"
                                   class="buy-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet m-i-3">پرداخت با کیف پول</a>
                            @else
                                <a id="second-btn" href="{{secure_url('user/wallet')}}"
                                   class="login-btn btn btn-info btn-shadow d-block pe-3 m-i-3"
                                   type="submit">شارژ کیف پول</a>
                            @endif
                        @endif
                        <button id="btn-area" class="login-btn btn btn-primary btn-shadow d-block pe-3 m-i-3"
                                type="submit">پرداخت با درگاه
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('header')
    <link rel="stylesheet" href="{{secure_url('/site-v2/css/virtual-number.css?v=14')}}">
    <link rel="stylesheet" href="{{secure_url('/site-v2/css/static-ip.css?v=15')}}">

@endsection
@section('footer')
    <script src="{{URL::asset('plugins/timer/jquery.simple.timer.js?v=2')}}"></script>

    <script>
        $('button').prop('disabled', false);
        @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
        $(document).on('click', '.buy-with-wallet', function () {
            var id = $(this).data('id');
            $.ajax({
                url: '{{secure_url('user/payment/wallet')}}/5/0',
                dataType: 'JSON',
                method: 'GET',
                data: {
                    country_id: $('#country_id').val(),
                    plan_id: $('#plan_id').val(),
                    volume_id: $('#volume_id').val(),
                },
                beforeSend: function () {
                    $('.buy-with-wallet').prop('disabled', true);
                    $('.buy-with-wallet').empty().text('لطفا صبر کنید...');
                },
                success: function (data) {
                    success('سفارش شما با موفقیت پرداخت شد. درحال انتقال به صفحه جزئیات سفارش.')
                    setTimeout(function () {
                        location.href = data.url
                    }, 2000)
                },
                error: function (err) {
                    console.log(err)
                    var Error = err.responseJSON.error;
                    Error.forEach(function (value) {
                        error(value);
                    });
                    $('.buy-with-wallet').prop('disabled', false);
                    $('.buy-with-wallet').empty().text('خرید با کیف پول');
                }
            });
        });
        @if($Session != null)
        $(document).ready(function () {
            $('#virtual-number').modal('toggle');
        });
        @endif
        @endif
        var btn = 0;
        $('.more-btn').click(function () {
            if (btn == 0) {
                btn = 1;
                $(this).empty().text('مشاهده کمتر')
            } else {
                btn = 0;
                $(this).empty().text('مشاهده بیشتر');
            }
            $('#content').toggleClass('height-auto');
        });
        $("#CountrySearch").keyup(function () {
            var filter = $(this).val();
            count = 0;
            $('.country-search').each(function () {
                if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                    $(this).hide();
                } else {
                    $(this).show();
                    count++;
                }
            });
        });
        $("#ServiceSearch").keyup(function () {
            var filter = $(this).val();
            console.log('asd')
            count = 0;
            $('.service-search').each(function () {
                if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                    $(this).hide();
                } else {
                    $(this).show();
                    count++;
                }
            });
        });

        $(document).on('click', '.get-country', function () {
            var country_id = $(this).data('country');
            $('.country-item').removeClass('selected-country');
            $(this).addClass('selected-country');
            $('.buy-service').removeClass('selected-country');
            $('.plans').removeClass('selected-country');
            $('.volume-area').removeClass('selected-country');
            $('#country_id').val(country_id);
            $('#plan_id').val('');
            $('#volume_id').val('');
            $.ajax({
                url: '{{secure_url('/static-ip/product-detail')}}',
                dataType: 'json',
                method: "GET",
                data: {id: country_id},
                success: function (data) {
                    $('#Volume-Area').slideUp();
                    $('#plans-area').slideDown();
                    $('#Service-Area').empty().append(data.data);
                    $([document.documentElement, document.body]).animate({scrollTop: $("#Service-Area").offset().top - 150}, 2000);
                },
                error: function () {
                }
            });
        });
        $(document).on('click', '.buy-service', function () {
            var plan_id = $(this).data('id');
            $('#plan_id').val(plan_id);
            $('#volume_id').val('');
            $('.buy-service').removeClass('selected-country');
            @if(\Jenssegers\Agent\Facades\Agent::isPhone())
            $(this).addClass('selected-country');
            $('.service-id-' + plan_id).addClass('selected-country');
            @else
            $('.service-id-' + plan_id).addClass('selected-country');
            @endif
            $('.volume-area').removeClass('selected-country');
            $('#Volume-Area').slideDown();
            $([document.documentElement, document.body]).animate({scrollTop: $("#Volume-Area").offset().top - 150}, 2000);
        });
        $(document).on('click', '.volume-area', function () {
            var volume_id = $(this).data('id');
            $('#volume_id').val(volume_id);
            $('.volume-area').removeClass('selected-country');
            @if(\Jenssegers\Agent\Facades\Agent::isPhone())
            $(this).addClass('selected-country');
            @else
            $('.volume-id-' + volume_id).addClass('selected-country');
            @endif
            $.ajax({
                url: "{{secure_url('/static-ip/submit-order')}}",
                dataType: "JSON",
                method: "POST",
                data: {
                    country_id: $('#country_id').val(),
                    plan_id: $('#plan_id').val(),
                    volume_id: $('#volume_id').val(),
                    _token: '{{csrf_token()}}'
                },
                success: function (data) {
                    var info = data.data
                    $('#ProductImage').attr('src', info.image);
                    $('#ProductName').empty().text(info.serviceName);
                    $('#PlanName').empty().text(info.PlanName);
                    $('#VolumeName').empty().text(info.VolumeName);
                    $('#ProductPrice').empty().text(info.price + ' تومان ');
                    $('#second-btn').remove();
                    $('#btn-area').before(info.btn);
                    $('#virtual-number').modal('toggle');
                },
                error: function (err) {
                    var errors = err.responseJSON;
                    if (errors.status == 1) {
                        err.responseJSON.error.forEach(function (value, key) {
                            error(value)
                        });
                        $('#signin-modal').modal('toggle');
                    } else if (errors.status == 2) {
                        for (const [key, value] of Object.entries(err.responseJSON.error)) {
                            error(value)
                        }
                    }
                }
            });
        });
        $('.submit-comment').click(function () {
            var BtnId = $(this).attr('id');
            var text = $(this).data('text');
            var form = $(this).data('form');
            $('#' + BtnId).html('لطفا صبر کنید...');
            $('#' + BtnId).attr('disabled', true);
            form = document.querySelector('#' + form);
            $(form).ajaxSubmit({
                dataType: 'json',
                beforeSend: function () {
                    $('#' + BtnId).html('لطفا صبر کنید');
                },
                success: function (data) {
                    success(data.success)
                    $('#' + BtnId).html(text);
                    $('#' + BtnId).attr('disabled', false);
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                },
                error: function (data) {
                    console.log('error')
                    if (data.responseJSON.message) {
                        error('');
                    } else {
                        data.responseJSON.error.forEach(function (value) {
                            error(value);
                        });
                    }
                    $('#' + BtnId).html(text);
                    $('#' + BtnId).attr('disabled', false);

                }
            });

            return false;
        });
    </script>
@endsection

@section('seo')
    @php
        $seo['meta_title'] = 'خرید آی پی ثابت مخصوص ترید و بازار های مالی 📈 | تحویل آنی';
        $seo['meta_desc'] = 'شاپ مجازی، ارائه دهنده خدمات آی پی ثابت جهت استفاده در صرافی های ارز دیجیتال (بایننس، کوکوین و...)، بازار های مالی جهانی(فارکس) و رفع تحریم سایتهای خارجی ';
        $seo['image'] = secure_url('/image/static-ip.jpg');
    @endphp
    @include('site-v2.layout.seo')
    <script type="application/ld+json">
        {  "@@context": "https://schema.org",  "@type": "FAQPage", "mainEntity": [{ "@type": "Question","name": "چرا باید آی پی ثابت خریداری کنم؟","acceptedAnswer": {"@type": "Answer","text": "با خرید آی پی ثابت شما میتونید آی پی خود را عوض کنید و از سایت های که ایران را تحریم کرده اند استفاده کنید."}},{"@type": "Question","name": "آیا پس از تمدید، آی پی من عوض خواهد شد؟","acceptedAnswer": {"@type": "Answer","text": "خیر، پس از تمدید، آی پی ثابتی که در حال استفاده کردن از آن هستید تمدید خواهد شد و آی پی شما عوض نخواهد شد."}},{"@type": "Question","name": "آیا خرید آی پی ثابت میتوانم در صرافی های حساس به آی پی ترید کنم؟","acceptedAnswer": {"@type": "Answer","text": "بله، فقط کافی است که کشور مورد نظر را انتخاب کرده و سرویس مورد نظر را خریداری کنید"}}]}
    </script>
    <script type="application/ld+json">
        {  "@@context": "https://schema.org/",   "@type": "BreadcrumbList",   "itemListElement": [{"@type": "ListItem","position": 1,"name": "شاپ مجازی","item": "https://shopmajazi.com/"},{"@type": "ListItem","position": 2,"name": "خرید ای پی ثابت","item": "https://shopmajazi.com/static-ip"}]}
    </script>
@endsection

