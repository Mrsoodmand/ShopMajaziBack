@extends('site-v2.layout.master',['MetaTitle' => 'تراکنشات | پنل کاربری'])
@section('content')
    <div class="page-title-overlap bg-dark pt-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-light flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" href="{{secure_url('/')}}"><i class="ci-home"></i>خانه</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap"><a href="{{secure_url('/user/home')}}">پروفایل</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">تراکنشات</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 text-light mb-0">تراکنشات</h1>
            </div>
        </div>
    </div>
    <div class="container pb-5 mb-2 mb-md-4">
        <div class="row">
            <!-- Sidebar-->
            @include('user-v2.layout.menu')
            <!-- Content  -->
            <section class="col-lg-9 pt-lg-4 pb-4 mb-3">
                <div class="pt-2 px-4 ps-lg-0 pe-xl-5">
                    <h2 class="h3 py-2 text-white text-center text-sm-start">پرداخت ها</h2>
                    <div class="row mx-n2 py-2">
                        <ul class="nav nav-tabs nav-justified" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link px-0 active" href="#online" data-bs-toggle="tab" role="tab"
                                   aria-selected="false">
                                    <div class="d-none d-lg-block"><i
                                                class="fa-regular fa-money-simple-from-bracket opacity-60 me-2"></i>شارژ
                                        آنلاین
                                    </div>
                                    <div class="d-lg-none text-center"><i
                                                class="fa-regular fa-money-simple-from-bracket opacity-60 d-block fs-xl mb-2"></i>
                                        <span class="fs-ms">شارژ آنلاین</span>
                                    </div>
                                </a>
                            </li>
{{--                            <li class="nav-item">--}}
{{--                                <a class="nav-link px-0" href="#card" data-bs-toggle="tab" role="tab"--}}
{{--                                   aria-selected="true">--}}
{{--                                    <div class="d-none d-lg-block"><i--}}
{{--                                                class="fa-regular fa-money-check-dollar opacity-60 me-2"></i>کارت به--}}
{{--                                        کارت--}}
{{--                                    </div>--}}
{{--                                    <div class="d-lg-none text-center"><i--}}
{{--                                                class="fa-regular fa-money-check-dollar opacity-60 d-block fs-xl mb-2"></i>--}}
{{--                                        <span class="fs-ms">کارت به کارت</span>--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                            <li class="nav-item">--}}
{{--                                <a class="nav-link px-0" href="#ref" data-bs-toggle="tab" role="tab"--}}
{{--                                   aria-selected="false">--}}
{{--                                    <div class="d-none d-lg-block"><i--}}
{{--                                                class="fa-regular fa-square-share-nodes opacity-60 me-2"></i>کسب درآمد--}}
{{--                                    </div>--}}
{{--                                    <div class="d-lg-none text-center">--}}
{{--                                        <i class="fa-regular fa-square-share-nodes opacity-60 d-block fs-xl mb-2"></i>--}}
{{--                                        <span class="fs-ms">کسب درآمد</span>--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                            </li>--}}
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="online" role="tabpanel">
                                @if(\Illuminate\Support\Facades\Session::has('error'))
                                    @foreach(\Illuminate\Support\Facades\Session::get('error') as $value)
                                        <div class="alert alert-danger">
                                            {{$value}}
                                        </div>
                                    @endforeach
                                @endif
                                <div class="col-sm-12 px-2 mb-4">
                                    <form action="{{route('user.ChargeWallet')}}" method="post">
                                        <div class="bg-secondary h-100 rounded-3 p-4" style="text-align: left">
                                            <h3 class="h5" style="text-align: right"> شارژ آنلاین</h3>
                                            <div class="d-flex flex-wrap align-items-center py-1 mb-2">
                                                <label class="form-label" for="unp-standard-price"></label>
                                                <div class="input-group">
                                                    <input name="amount" class="value form-control" type="tel"
                                                           id="unp-standard-price">
                                                    <span class="input-group-text">تومان</span>
                                                </div>
                                                <div class="form-text">حداقل میزان شارژ، 10,000 تومان می باشد
                                                </div>
                                            </div>
                                            <div class="d-flex mb-3 row">
                                                <label class="form-label" for="unp-standard-price" style="text-align: right;margin-bottom: 10px;">انتخاب کارت</label>
                                                <div class="col-lg-4 col-md-6 col-12">
                                                    <div class="form-check form-option form-check-justified mb-2">
                                                        <input class="form-check-input" type="radio" value="0"
                                                               name="card_id" id="standard-0" checked=""
                                                               onclick="$('.new-card').fadeIn()">
                                                        <label class="form-option-label" for="standard-0">افزودن کارت
                                                            جدید</label>
                                                    </div>
                                                </div>
                                                @foreach($Cards as $card)
                                                    <div class="col-lg-4 col-md-6 col-12">
                                                        <div class="form-check form-option form-check-justified mb-2">
                                                            <input class="form-check-input" type="radio"
                                                                   value="{{$card->id}}" name="card_id"
                                                                   id="standard-{{$card->id}}"
                                                                   onclick="$('.new-card').fadeOut()">
                                                            <label class="form-option-label"
                                                                   for="standard-{{$card->id}}">{{substr(chunk_split($card->card,4,'-'),0,strlen($card->card) + 3)}}</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                <div class="col-12 new-card">
                                                    <div class="form-text" style="text-align: right">لطفا کارتی که قصد پرداخت با آن را دارید، انتخاب یا وارد نمایید.</div>
                                                </div>
                                            </div>
                                            <div class="row new-card">
                                                <div class="d-flex flex-wrap align-items-center py-1 mb-2 ">
                                                    <label class="form-label" for="unp-standard-price">ثبت کارت
                                                        جدید</label>
                                                    <div class="input-group">
                                                        <input name="card_number" class="form-control" type="tel"
                                                               id="unp-standard-price">
                                                    </div>
                                                </div>
                                            </div>
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm" href="#">پرداخت
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="card" role="tabpanel">
                                <div class="col-sm-12 px-2 mb-4">
                                    <div class="bg-secondary h-100 rounded-3 p-4">
                                        <h3 class="h5">کارت به کارت</h3>
                                        <div class="d-flex flex-wrap align-items-center py-1 mb-2">
                                            <label class="form-label" for="unp-standard-price"></label>
                                            <div class="input-group"><span class="input-group-text"><i
                                                            class="ci-dollar"></i></span>
                                                <input class="form-control" type="text" id="unp-standard-price">
                                            </div>
                                            <div class="form-text">قیمت متوسط ​​در بازار برای این گروه 15 تومان است.
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-sm" href="#">پرداخت</button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="ref" role="tabpanel">
                                <div class="col-sm-12 px-2 mb-4">
                                    <div class="bg-secondary h-100 rounded-3 p-4">
                                        <h3 class="h5">کسب درآمد</h3>
                                        <div class="d-flex flex-wrap align-items-center py-1 mb-2">
                                            <label class="form-label" for="unp-standard-price"></label>
                                            <div class="input-group"><span class="input-group-text"><i
                                                            class="ci-dollar"></i></span>
                                                <input class="form-control" type="text" id="unp-standard-price">
                                            </div>
                                            <div class="form-text">قیمت متوسط ​​در بازار برای این گروه 15 تومان است.
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-sm" href="#">پرداخت</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h3 class="h5 pb-2">لیست تراکنشات</h3>
                    <div class="table-responsive">
                       @include('user-v2.layout.payments',['list' =>$list])
                    </div>
                    <hr class="mb-grid-gutter">
                    <!-- Pagination-->
                    {!! $list->render('vendor.pagination.bootstrap-4') !!}
                </div>
            </section>
        </div>
    </div>
@endsection
@section('header')
    <style>
        input[type="number"] {
            -webkit-appearance: textfield;
            -moz-appearance: textfield;
            appearance: textfield;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
        }
    </style>
@endsection

@section('footer')
    <script>
        $('.value').keyup(function () {
            var values = $(this).val();
            $(this).val(function (index, value) {
                return value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            });
        })

    </script>

@endsection
