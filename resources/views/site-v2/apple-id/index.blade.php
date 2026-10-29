    @extends('site-v2.layout.master',['MetaTitle' => '😍 خرید اپل آیدی (Apple Id) آمریکا | تحویل آنی | قیمت مناسب'])
@section('content')
    @php $Price = json_decode($Page->detail,true); @endphp
    <div class="page-title-overlap pt-4 bg-header">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb text-white">
                    <ol class="breadcrumb flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" target="_blank" href="{{secure_url('/')}}"><i class="ci-home"></i>شاپ مجازی</a></li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">{{$Page->h1}}</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 mb-0">{{$Page->h1}}</h1>
            </div>
        </div>
    </div>
    <section class="container mb-3 pb-3">
        <div class="bg-light shadow-lg rounded-3 overflow-hidden">
            <div class="row">
                <div class="col-12" id="hamkar-area">
                    <div class="bg-success h-100 p-4 text-center text-white">جهت سفارش اپل آیدی بیشتر از 10 عدد، پیام دهید. با تشکر</div>
                </div>
                @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                    <aside class="col-lg-6 ps-xl-5">
                        <hr class="d-lg-none">
                        <div class="bg-white h-100 p-4 ms-auto border-start">
                            <div class="px-lg-2 product-area">
                                <form action="{{route('submit-factor',1)}}" id="َAddToCartForm" method="post">
                                    <div class="rounded p-3 mt-2 mb-2">
                                        <div class="accordion accordion-flush" id="licenses">
                                            <div class="accordion-item border-bottom">
                                                <div
                                                        class="accordion-header d-flex justify-content-between align-items-center py-3">
                                                    <div class="form-check change-type" data-name="اپل آیدی آماده"
                                                         data-price="{{number_format($Price['normal_price'])}}"
                                                         data-product="1" data-bs-toggle="collapse"
                                                         data-bs-target="#standard-license">
                                                        <input class="form-check-input" type="radio" name="type"
                                                               id="license-std" value="1" checked="">
                                                        <label class="form-check-label fw-medium text-dark"
                                                               for="license-std">اپل آیدی آماده</label>
                                                    </div>
                                                    <span class="mb-0 text-accent fw-normal"><span
                                                                class="ProductPrice">{{number_format($Price['normal_price'])}}</span>
                                                        تومان </span>
                                                </div>
                                                <div class="accordion-collapse collapse show" id="standard-license"
                                                     data-bs-parent="#licenses">
                                                    <div class="accordion-body py-0 pb-2">
                                                        <div class="mb-3">
                                                            <span class="fs-ms text-muted">تعداد*</span>
                                                            <select name="quantity" id="quantity" class="form-select">
                                                                @for($i = 1; $i <= 10;$i++)
                                                                    <option value="{{$i}}">{{$i}} عدد</option>
                                                                @endfor
                                                                    <option value="30">30 عدد</option>
                                                                    <option value="50">50 عدد</option>
                                                                    <option value="100">100 عدد</option>
                                                                    <option value="200">200 عدد</option>
                                                            </select>
                                                        </div>
                                                        <ul class="list-unstyled fs-sm" id="CountryArea">
                                                            <li class="d-flex align-items-center mr-3"><i class="ci-check-circle text-success me-1"></i><span class="fs-ms">وریفای شده</span></li>
                                                            <li class="d-flex align-items-center mr-3"><i class="ci-check-circle text-success me-1"></i><span class="fs-ms">تحویل آنی اپل آیدی</span></li>
                                                            <li class="d-flex align-items-center mr-3"><i class="ci-check-circle text-success me-1"></i><span class="fs-ms">اپل آیدی کشور آمریکا</span></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion-item border-bottom">
                                                <div class="accordion-header d-flex justify-content-between align-items-center py-3">
                                                    <div class="form-check change-type" data-name="اپل آیدی همکاری"
                                                         data-price="{{number_format($Price['normal_price'])}}"
                                                         data-product="1" data-bs-toggle="collapse"
                                                         data-bs-target="#hamkar-license">
                                                        <input class="form-check-input" type="radio" name="type"
                                                               id="license-std" value="1">
                                                        <label class="form-check-label fw-medium text-dark"
                                                               for="license-std">اپل آیدی همکاری</label>
                                                    </div>
                                                    <span class="mb-0 text-accent fw-normal"><span
                                                                class="ProductPrice2">{{number_format($Price['normal_price'] * 30)}}</span>
                                                        تومان </span>
                                                </div>
                                                <div class="accordion-collapse collapse" id="hamkar-license"
                                                     data-bs-parent="#licenses">
                                                    <div class="accordion-body py-0 pb-2">
                                                        <div class="mb-3">
                                                            <span class="fs-ms text-muted">تعداد*</span>
                                                            <select name="quantity" id="quantity2" class="form-select">
                                                                <option value="30">30 عدد</option>
                                                                <option value="50">50 عدد</option>
                                                                <option value="100">100 عدد</option>
                                                                <option value="200">200 عدد</option>
                                                            </select>
                                                        </div>
                                                        <ul class="list-unstyled fs-sm" id="CountryArea">
                                                            <li class="d-flex align-items-center mr-3"><i class="ci-check-circle text-success me-1"></i><span class="fs-ms">وریفای شده</span></li>
                                                            <li class="d-flex align-items-center mr-3"><i class="ci-check-circle text-success me-1"></i><span class="fs-ms">تحویل آنی اپل آیدی</span></li>
                                                            <li class="d-flex align-items-center mr-3"><i class="ci-check-circle text-success me-1"></i><span class="fs-ms">اپل آیدی کشور آمریکا</span></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion-item">
                                                <div
                                                        class="accordion-header d-flex justify-content-between align-items-center py-3">
                                                    <div class="form-check change-type" data-name="اپل آیدی سفارش"
                                                         data-price="{{number_format($Price['custom_price'])}}"
                                                         data-product="2" data-bs-toggle="collapse"
                                                         data-bs-target="#extended-license">
                                                        <input class="form-check-input" type="radio" name="type" value="2"
                                                               id="license-ext">
                                                        <label class="form-check-label fw-medium text-dark"
                                                               for="license-ext">اپل آیدی سفارشی</label>
                                                    </div>
                                                    <span class="mb-0 text-accent fw-normal">{{number_format($Price['custom_price'])}}
                                                        تومان </span>
                                                </div>
                                                <div class="accordion-collapse collapse border-0" id="extended-license"
                                                     data-bs-parent="#licenses">
                                                    <div class="accordion-body py-0 pb-2">
                                                        <div class="row mb-2">
                                                            <div class="col-lg-6 col-12 mb-2">
                                                                <span class="fs-ms text-muted">نام*</span>
                                                                <input name="name" type="text"
                                                                       class="form-control">
                                                            </div>
                                                            <div class="col-lg-6 col-12 mb-2">
                                                                <span class="fs-ms text-muted">نام و نام خانوادگی*</span>
                                                                <input name="family" type="text"
                                                                       class="form-control">
                                                            </div>
                                                            <div class="col-lg-6 col-12 mb-2">
                                                                <span class="fs-ms text-muted">آدرس ایمیل*</span>
                                                                <input name="email" type="text"
                                                                       class="form-control">
                                                            </div>
                                                            <div class="col-lg-6 col-12 mb-2">
                                                                <span class="fs-ms text-muted">تاریخ تولد*</span>
                                                                <input name="b_day" type="date"
                                                                       class="form-control">
                                                            </div>

                                                        </div>
                                                        <ul class="list-unstyled fs-sm display-flex">
                                                            <li class="d-flex align-items-center mr-3"><i
                                                                        class="ci-check-circle text-success me-1"></i><span
                                                                        class="fs-ms">تحویل کمتر از 10 دقیقه</span></li>
                                                            <li class="d-flex align-items-center mr-3"><i
                                                                        class="ci-check-circle text-success me-1"></i><span
                                                                        class="fs-ms">اپل آیدی کشور آمریکا با ایمیل شخصی شما</span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 mt-3 text-center">
                                        <span class="text-heading fw-medium me-1">نوع اپل آیدی:</span>
                                        <span class="m-a-1" id="ProductName"> اپل آیدی آماده</span><br>
                                        <span id="SelectedFirstOption">مبلغ:</span>
                                        <span class="ProductPrice m-a-2" id="ProductPrice">{{number_format($Price['normal_price'])}}</span>
                                        <span> تومان</span>
                                    </div>
                                    @csrf
                                    <button data-form="َAddToCartForm" id="AddToCartBtn" data-text="خرید و ثبت سفارش"
                                            class="submit-form btn btn-primary btn-shadow d-block w-100 mt-4 mb-4"
                                            type="submit">
                                        <i class="ci-cart fs-lg me-2"></i>ثبت سفارش و خرید
                                    </button>
                                </form>
                                <table>
                                    <tbody>
                                    <tr>
                                        <th> 🛒نام سرویس:</th>
                                        <td>buy Apple Id</td>
                                    </tr>
                                    <tr>
                                        <th>💲قیمت:</th>
                                        <td>از 30,000 هزار تومان</td>
                                    </tr>
                                    <tr>
                                        <th> ⏳زمان تحویل:</th>
                                        <td> کمتر از 10 دقیقه</td>
                                    </tr>
                                    <tr>
                                        <th>📱فضای آیکلود:</th>
                                        <td>5 گیگابایت</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="d-flex flex-wrap justify-content-between align-items-center pt-3" >
                                    <div class="d-flex flex-wrap justify-content-between align-items-center pt-3" >
                                        <div class="py-2">
                                            <label class="form-label d-inline-block align-middle my-2 me-3">اشتراک:</label>
                                            <a class="btn-share btn-twitter me-2 my-2" href="http://www.facebook.com/share.php?u={{URL::current()}}"><i class="ci-twitter"></i>توییتر</a>
                                            <a class="btn-share btn-facebook my-2" href="http://www.facebook.com/share.php?u={{URL::current()}}"><i class="ci-facebook"></i>فیسبوک</a>
                                            <a class="btn-share btn-twitter my-2" href="https://t.me/share/url?url={{URL::current()}}"><i class="ci-telegram"></i>تلگرام</a>
                                            <label class="btn-share btn-instagram my-2" id="copy" onclick="copyToClipboard('#copy')" data-copy="{{URL::current()}}"><i class="icon-copy"></i>کپی لینک</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                    <section class="col-lg-6 pt-2 pt-lg-4 pb-4 mb-lg-3">
                        <div class="pt-3 px-4 pe-lg-0 ps-xl-3">
                            <div class="gallery">
                                <div class="rounded-3 mb-grid-gutter">
                                    <img class="header-image" src="{{secure_url($Page->image)}}" width="" alt="apple-id">
                                    <span class="gallery-item-caption">{{$Page->name}}</span>
                                </div>
                            </div>
                            <div class="m-a-3">
                                {!! $Page->short_detail !!}
                            </div>
                        </div>
                    </section>
                @else
                    <section class="col-lg-6 pt-2 pt-lg-4 pb-4 mb-lg-3">
                        <div class="pt-3 px-4 pe-lg-0 ps-xl-3">
                            <div class="gallery">
                                @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                                    <div class="d-table scale-up delay-4 mx-auto mx-lg-0 mb-3">
                                        <button class="btn btn-primary slow-scroll m-submit-order ml-5 m-a-4" data-target="CountryArea">خرید اپل آیدی</button>
                                        <button class="btn btn-info slow-scroll m-faq-button ml-5" data-target="FaqArea">سوالات متدوال</button>
                                    </div>
                                @endif
                                <div class="rounded-3 mb-grid-gutter">
                                    <img class="header-image" src="{{secure_url($Page->image)}}" alt="Gallery preview">
                                    <span class="gallery-item-caption">{{$Page->name}}</span>
                                </div>
                            </div>
                            <div class="m-a-3">
                                {!! $Page->short_detail !!}
                            </div>
                        </div>
                    </section>
                    <aside class="col-lg-6 ps-xl-5">
                        <hr class="d-lg-none">
                        <div class="bg-white h-100 p-4 ms-auto border-start">
                            <div class="px-lg-2 product-area">
                                <form action="{{route('submit-factor',1)}}" id="َAddToCartForm" method="post">
                                    <div class="rounded p-3 mt-2 mb-2">
                                        <div class="accordion accordion-flush" id="licenses">
                                            <div class="accordion-item border-bottom">
                                                <div
                                                        class="accordion-header d-flex justify-content-between align-items-center py-3">
                                                    <div class="form-check change-type" data-name="اپل آیدی آماده"
                                                         data-price="{{number_format($Price['normal_price'])}}"
                                                         data-product="1" data-bs-toggle="collapse"
                                                         data-bs-target="#standard-license">
                                                        <input class="form-check-input" type="radio" name="type"
                                                               id="license-std" value="1" checked="">
                                                        <label class="form-check-label fw-medium text-dark"
                                                               for="license-std">اپل آیدی آماده</label>
                                                    </div>
                                                    <span class="mb-0 text-accent fw-normal"><span
                                                                class="ProductPrice">{{number_format($Price['normal_price'])}}</span>
                                                        تومان </span>
                                                </div>
                                                <div class="accordion-collapse collapse show" id="standard-license"
                                                     data-bs-parent="#licenses">
                                                    <div class="accordion-body py-0 pb-2">
                                                        <div class="mb-3">
                                                            <span class="fs-ms text-muted">تعداد*</span>
                                                            <select name="quantity" id="quantity" class="form-select">
                                                                @for($i = 1; $i <= 10;$i++)
                                                                    <option value="{{$i}}">{{$i}} عدد</option>
                                                                @endfor
                                                                    <option value="30">30 عدد</option>
                                                                    <option value="50">50 عدد</option>
                                                                    <option value="100">100 عدد</option>
                                                                    <option value="200">200 عدد</option>
                                                            </select>
                                                        </div>
                                                        <ul class="list-unstyled fs-sm display-flex" id="CountryArea">
                                                            <li class="d-flex align-items-center mr-3"><i class="ci-check-circle text-success me-1"></i><span class="fs-ms">وریفای شده</span></li>
                                                            <li class="d-flex align-items-center mr-3"><i class="ci-check-circle text-success me-1"></i><span class="fs-ms">تحویل آنی اپل آیدی</span></li>
                                                            <li class="d-flex align-items-center mr-3"><i class="ci-check-circle text-success me-1"></i><span class="fs-ms">اپل آیدی کشور آمریکا</span></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion-item border-bottom">
                                                <div class="accordion-header d-flex justify-content-between align-items-center py-3">
                                                    <div class="form-check change-type" data-name="اپل آیدی همکاری"
                                                         data-price="{{number_format($Price['normal_price'])}}"
                                                         data-product="1" data-bs-toggle="collapse"
                                                         data-bs-target="#hamkar-license">
                                                        <input class="form-check-input" type="radio" name="type"
                                                               id="license-std" value="1">
                                                        <label class="form-check-label fw-medium text-dark"
                                                               for="license-std">اپل آیدی همکاری</label>
                                                    </div>
                                                    <span class="mb-0 text-accent fw-normal"><span
                                                                class="ProductPrice2">{{number_format($Price['normal_price'] * 30)}}</span>
                                                        تومان </span>
                                                </div>
                                                <div class="accordion-collapse collapse" id="hamkar-license"
                                                     data-bs-parent="#licenses">
                                                    <div class="accordion-body py-0 pb-2">
                                                        <div class="mb-3">
                                                            <span class="fs-ms text-muted">تعداد*</span>
                                                            <select name="quantity" id="quantity2" class="form-select">
                                                                <option value="30">30 عدد</option>
                                                                <option value="50">50 عدد</option>
                                                                <option value="100">100 عدد</option>
                                                                <option value="200">200 عدد</option>
                                                            </select>
                                                        </div>
                                                        <ul class="list-unstyled fs-sm" id="CountryArea">
                                                            <li class="d-flex align-items-center mr-3"><i class="ci-check-circle text-success me-1"></i><span class="fs-ms">وریفای شده</span></li>
                                                            <li class="d-flex align-items-center mr-3"><i class="ci-check-circle text-success me-1"></i><span class="fs-ms">تحویل آنی اپل آیدی</span></li>
                                                            <li class="d-flex align-items-center mr-3"><i class="ci-check-circle text-success me-1"></i><span class="fs-ms">اپل آیدی کشور آمریکا</span></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="accordion-item">
                                                <div
                                                        class="accordion-header d-flex justify-content-between align-items-center py-3">
                                                    <div class="form-check change-type" data-name="اپل آیدی سفارش"
                                                         data-price="{{number_format($Price['custom_price'])}}"
                                                         data-product="2" data-bs-toggle="collapse"
                                                         data-bs-target="#extended-license">
                                                        <input class="form-check-input" type="radio" name="type" value="2"
                                                               id="license-ext">
                                                        <label class="form-check-label fw-medium text-dark"
                                                               for="license-ext">اپل آیدی سفارشی</label>
                                                    </div>
                                                    <span class="mb-0 text-accent fw-normal">{{number_format($Price['custom_price'])}}
                                                        تومان </span>
                                                </div>
                                                <div class="accordion-collapse collapse border-0" id="extended-license"
                                                     data-bs-parent="#licenses">
                                                    <div class="accordion-body py-0 pb-2">
                                                        <div class="row mb-2">
                                                            <div class="col-lg-6 col-12 mb-2">
                                                                <span class="fs-ms text-muted">نام*</span>
                                                                <input name="name" type="text"
                                                                       class="form-control">
                                                            </div>
                                                            <div class="col-lg-6 col-12 mb-2">
                                                                <span class="fs-ms text-muted">نام و نام خانوادگی*</span>
                                                                <input name="family" type="text"
                                                                       class="form-control">
                                                            </div>
                                                            <div class="col-lg-6 col-12 mb-2">
                                                                <span class="fs-ms text-muted">آدرس ایمیل*</span>
                                                                <input name="email" type="text"
                                                                       class="form-control">
                                                            </div>
                                                            <div class="col-lg-6 col-12 mb-2">
                                                                <span class="fs-ms text-muted">تاریخ تولد*</span>
                                                                <input name="b_day" type="date"
                                                                       class="form-control">
                                                            </div>

                                                        </div>
                                                        <ul class="list-unstyled fs-sm display-flex">
                                                            <li class="d-flex align-items-center mr-3"><i
                                                                        class="ci-check-circle text-success me-1"></i><span
                                                                        class="fs-ms">تحویل کمتر از 10 دقیقه</span></li>
                                                            <li class="d-flex align-items-center mr-3"><i
                                                                        class="ci-check-circle text-success me-1"></i><span
                                                                        class="fs-ms">اپل آیدی کشور آمریکا با ایمیل شخصی شما</span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 mt-3 text-center">
                                        <span class="text-heading fw-medium me-1">نوع اپل آیدی:</span>
                                        <span class="m-a-1" id="ProductName"> اپل آیدی آماده</span><br>
                                        <span id="SelectedFirstOption">مبلغ:</span>
                                        <span class="ProductPrice m-a-1"
                                              id="ProductPrice">{{number_format($Price['normal_price'])}}</span>
                                        <span> تومان</span>
                                    </div>
                                    @csrf
                                    <button data-form="َAddToCartForm" id="AddToCartBtn" data-text="خرید و ثبت سفارش"
                                            class="submit-form btn btn-primary btn-shadow d-block w-100 mt-4 mb-4"
                                            type="submit">
                                        <i class="ci-cart fs-lg me-2"></i>ثبت سفارش و خرید
                                    </button>
                                </form>
                                <table>
                                    <tbody>
                                    <tr>
                                        <th> 🛒نام سرویس:</th>
                                        <td>buy Apple Id</td>
                                    </tr>
                                    <tr>
                                        <th>💲قیمت:</th>
                                        <td>از 30,000 هزار تومان</td>
                                    </tr>
                                    <tr>
                                        <th> ⏳زمان تحویل:</th>
                                        <td> کمتر از 10 دقیقه</td>
                                    </tr>
                                    <tr>
                                        <th>📱فضای آیکلود:</th>
                                        <td>5 گیگابایت</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="d-flex flex-wrap justify-content-between align-items-center pt-3" >
                                    <div class="py-2">
                                        <label class="form-label d-inline-block align-middle my-2 me-3">اشتراک:</label>
                                        <a class="btn-share btn-twitter me-2 my-2" href="http://www.facebook.com/share.php?u={{URL::current()}}"><i class="ci-twitter"></i>توییتر</a>
                                        <a class="btn-share btn-facebook my-2" href="http://www.facebook.com/share.php?u={{URL::current()}}"><i class="ci-facebook"></i>فیسبوک</a>
                                        <a class="btn-share btn-twitter my-2" href="https://t.me/share/url?url={{URL::current()}}"><i class="ci-telegram"></i>تلگرام</a>
                                        <label class="btn-share btn-instagram my-2" id="copy" onclick="copyToClipboard('#copy')" data-copy="{{URL::current()}}"><i class="icon-copy"></i>کپی لینک</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                @endif
            </div>
        </div>
    </section>
    @include('site-v2.components.why-shopmajazi')
        <!-- Start Content -->
        <section class="container ps-lg-4 pe-lg-3 pt-4 ">
            <section class="mb-3 pb-3">
                <div class="shadow-lg rounded-3 overflow-hidden">
                    <div class="row content-area">
                        <div class="col-lg-12 pt-2 pt-lg-4 pb-4">
                            <div class="pt-3 px-4 pe-lg-5 ps-lg-5">
                                <div class="post-container">
                                    <div class="post-content" id="content">
                                        {!! $Page->long_detail !!}
                                    </div>
                                    <div class="m-a-5">

                                    </div>
                                    <div class="show-more-btn">
                                        <button class="btn btn-primary btn-shadow more-btn">مشاهده بیشتر</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>
        <!-- End Content -->
    @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
        <div class="modal fade" id="virtual-number" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-secondary">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item"><label class="nav-link fw-medium active" id="Login-tab-header"><i
                                        class="fa-regular fa-cart-shopping"></i>خرید اپل آیدی</label></li>
                        </ul>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="{{route('user.service-bank-payment')}}"
                          class="needs-validation tab-pane fade show active" autocomplete="off" novalidate>
                        <div class="modal-body tab-content py-4">
                            <div class="table-responsive fs-md mb-4">
                                <table class="table table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th>نام سرویس</th>
                                        <th>مبلغ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class="py-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="ps-2">
                                                    <span id="ProductNameTable" class="fw-medium text-heading me-1">asdasdas</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 align-middle" id="ProductPriceTable">asdasd</td>
                                        <input type="hidden" name="product_id" value="" id="product_id">
                                        <input type="hidden" name="type" value="" id="type">
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @csrf
                        <div class="modal-footer justify-content-center">
                            <button id="btn-area" class="login-btn btn btn-primary btn-shadow d-block pe-3"
                                    style="width: 47%" type="submit">پرداخت با درگاه
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    @php $Faq = [
      [
            'q' => 'آیا با خرید اپل آیدی، میتوانم از خدمات اپل استفاده کنم؟',
            'a' => 'بله، با خرید اپل آیدی شما بدون مشکل از تمامی خدمات اپل استفاده کنید.',
            ],
             [
            'q' => 'چرا باید اپل آیدی خریداری کنم؟',
            'a' => 'اگر شما صاحب آیفون، آی پد، آی واچ و یا سایر محصولات اپل می باشید برای استفاده از آنها باید یک Apple Id داشته باشید.',
            ],[
            'q' => 'آیا میتوان با ایمیل شخصی خودم، اپل آیدی بسازم؟',
            'a' => 'بله، شما میتوانید با ایمیل شخصی خودتون، اپل آیدی بسازید و از آن استفاده کنید',
            ],[
            'q' => 'آیا فرقی بین خرید اپل آیدی آماده به صورت آنلاین و کارتی وجود دارد؟',
            'a' => 'بله، اپل آیدی های کارتی پس از مدتی قفل(لاک) می شوند.',
            ],

]
    @endphp
    @include('site-v2.components.faq')
    @include('site-v2.components.comment',[$Comment,'Type' => 'page' ,'Id' => '4'])

@endsection

@section('header')
    <link rel="stylesheet" href="{{secure_url('/site-v2/css/apple-id.css?v=14')}}">

@endsection

@section('footer')
    <script type="text/javascript">
        function copyToClipboard(element) {

            navigator.clipboard.writeText(element);
            toastr.success("عملیات کپی با موفقیت انجام شد.", "", {
                timeOut: 3000,
                closeButton: !0,
                debug: !1,
                newestOnTop: !0,
                progressBar: !0,
                positionClass: "toast-top-right",
                preventDuplicates: !0,
                onclick: null,
                showDuration: "300",
                hideDuration: "1000",
                extendedTimeOut: "1000",
                showEasing: "swing",
                hideEasing: "linear",
                showMethod: "fadeIn",
                hideMethod: "fadeOut",
                tapToDismiss: !1
            });
        }

        $('.change-type').click(function () {
            $('#ProductName').empty().text($(this).data('name'));
            $('#ProductPrice').empty().text($(this).data('price'));
        });
        $('#quantity').change(function () {
            var price = {{$Price['normal_price']}};
            var quantity = $(this).val();
            price = price * quantity;
            $('.ProductPrice').empty().text(price);
            $('.ProductPrice').text(function (index, value) {
                return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            });
            $('#hamkar-area').hide();

        });

        $('#quantity2').change(function () {
            var price = {{$Price['normal_price']}};
            var quantity = $(this).val();
            price = price * quantity;
            $('.ProductPrice2').empty().text(price);
            $('.ProductPrice2').text(function (index, value) {
                return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            });
            $('#hamkar-area').show();
        });
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

        $(document).ready(function () {
            $('button').prop('disabled', false);
            $('.submit-form').click(function () {
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
                        $('#second-btn').remove();
                        $('#ProductNameTable').empty().text(data.name);
                        $('#ProductPriceTable').empty().text(data.price);
                        $('#product_id').val(data.product_id);
                        $('#type').val(data.type);
                        $('#btn-area').before(data.btn);
                        $('#virtual-number').modal('toggle');
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);
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
                        } else {
                            var Error = err.responseJSON.error;
                            Error.forEach(function (value) {
                                error(value);
                            });
                        }
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);
                    }
                });
                return false;
            });
            $('.submit-btn').click(function () {
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
                        $('#second-btn').remove();
                        $('#ProductNameTable').empty().text(data.name);
                        $('#ProductPriceTable').empty().text(data.price);
                        $('#product_id').val(data.product_id);
                        $('#type').val(data.type);
                        $('#btn-area').before(data.btn);
                        $('#virtual-number').modal('toggle');
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);
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
                        } else {
                            var Error = err.responseJSON.error;
                            Error.forEach(function (value) {
                                error(value);
                            });
                        }
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);
                    }
                });
                return false;
            });
        });
        function copyToClipboard(element) {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(element).data('copy')).select();
            document.execCommand("copy");
            $temp.remove();
            alert('لینک با موفقیت کپی شد')
        }

        @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
        $(document).on('click', '.buy-with-wallet', function () {
            var id = $(this).data('id');
            $.ajax({
                url: '{{secure_url('user/payment/wallet')}}/3/' + id + '',
                dataType: 'JSON',
                method: 'GET',
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
                    var Error = err.responseJSON.error;
                    Error.forEach(function (value) {
                        error(value);
                    });
                    $('.buy-with-wallet').prop('disabled', false);
                    $('.buy-with-wallet').empty().text('خرید با کیف پول');
                }
            });
        });
        @endif
    </script>
@endsection

@section('seo')
    @php
        $seo['meta_title'] = ' 😍 خرید اپل آیدی (Apple Id) آمریکا | تحویل فوری | قیمت مناسب |ضمانت برگشت وجه ';
        $seo['meta_desc'] = 'خرید اپل آیدی وریفای شده آمریکا همراه با سوالات امنیتی🔐، خرید اپل آیدی با قیمت مناسب 💵 و تحویل آنی 🕕 | شاپ مجازی';
        $seo['image'] = url($Page->image);
        @endphp
    @include('site-v2.layout.seo')
@endsection
