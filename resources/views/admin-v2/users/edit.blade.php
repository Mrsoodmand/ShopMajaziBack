@extends('admin-v2.layout.master',['MetaTitle' => 'ویرایش کاربر'])
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">

        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card mb-6 mb-xl-9">
                    <div class="card-body pt-9 pb-0">
                        <div class="d-flex flex-wrap flex-sm-nowrap mb-6">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1">
                                            <a href="javascript:void(0)" class="text-gray-800 text-hover-primary fs-2 fw-bolder me-3">{{$User->name}}</a>
                                            @if($User->account_blocked)
                                                <span class="badge badge-light-danger me-auto">غیرفعال</span>
                                            @else
                                                <span class="badge badge-light-success me-auto">فعال</span>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-wrap fw-bold mb-4 fs-5 text-gray-400">{{$User->mobile}}</div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap justify-content-start">
                                    <div class="d-flex flex-wrap">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-bold fs-6 text-gray-400 mb-2"> <i class="fa-light fa-calendar-days"></i> تاریخ ثبت نام</div>
                                            <div class="d-flex align-items-center">
                                                <div class="fs-4 fw-bolder">{{$jdf->jdate('d F Y',strtotime($User->created_at))}}</div>
                                            </div>
                                        </div>
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-bold fs-6 text-gray-400 mb-2"><i class="fa-duotone fa-wallet" style="margin-left: 5px"></i>موجودی کیف پول</div>
                                            <div class="d-flex">
                                                <div class="fs-4 fw-bolder">{{number_format($User->wallet)}} تومان </div>
                                            </div>
                                        </div>
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-bold fs-6 text-gray-400 mb-2"><i class="fa-light fa-lock" style="margin-left: 5px"></i>موجودی مسدود شده</div>
                                            <div class="d-flex">
                                                <div class="fs-4 fw-bolder">{{number_format($User->blocked_wallet)}} تومان </div>
                                            </div>
                                        </div>
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-bold fs-6 text-gray-400 mb-2"><i class="fa-light fa-basket-shopping" style="margin-left: 5px"></i>مجموع سفارشات</div>
                                            <div class="d-flex">
                                                <div class="fs-4 fw-bolder">{{$OrdersCount}}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div><i class=""></i>
                            </div>
                        </div>
                    </div>
                </div>
                <form id="CurrentForm" class="form d-flex flex-column flex-lg-row"
                      action="{{route('admin.user submit')}}"
                      method="post">
                    @csrf
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-n2">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#all">کیف پول</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                   href="#seo">پروفایل کاربری</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                   href="#settings">محدودیت</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane show active" id="all" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>عمومی</h2>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="d-flex flex-wrap gap-5">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">موجودی کیف پول</label>
                                                    <input type="tel" name="wallet" class="form-control mb-2" placeholder="" value="0"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">نوع عملیات</label>
                                                    <select name="wallet_action" class="form-control" id="">
                                                        <option value="1">شارژ حساب</option>
                                                        <option value="0">کسر از حساب</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-5">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">موجودی مسدود شده</label>
                                                    <input type="tel" name="blocked_wallet" class="form-control mb-2" placeholder="" value="0"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">نوع عملیات</label>
                                                    <select name="blocked_wallet_action" class="form-control" id="">
                                                        <option value="1">شارژ حساب</option>
                                                        <option value="0">کسر از حساب</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="seo" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>پروفایل کاربری</h2>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="mb-10">
                                                <label class="required form-label">نام و نام خانوادگی</label>
                                                <input type="text" name="user_name" class="form-control mb-2" placeholder="نام به فارسی" value="{{$User->name}}"/>
                                                <input type="hidden" name="id" value="{{$User->id}}"/>
                                            </div>
                                            <div>
                                                <label class="form-label">کلمه عبور</label>
                                                <input type="text" name="password" class="form-control mb-2" placeholder="نام به فارسی" value=""/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="settings" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>تنظمیات</h2>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="d-flex flex-wrap gap-5 mt-4">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">ورود</label>
                                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox" value="1" id="status" name="status" @if(isset($User) && $User->status == 1) checked="checked" @endif>
                                                        <label class="form-check-label fw-bold text-gray-400 ms-3" for="status">مسدود</label>
                                                    </div>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">خرید با کیف پول</label>
                                                    <div
                                                            class="form-check form-switch form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox" value="1" id="status" name="wallet_blocked" @if(isset($User) && $User->wallet_blocked == 1) checked="checked" @endif>
                                                        <label class="form-check-label fw-bold text-gray-400 ms-3"
                                                               for="status">مسدود</label>
                                                    </div>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">خرید با درگاه</label>
                                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox" value="1" id="status" name="bank_blocked" @if(isset($User) && $User->bank_blocked == 1) checked="checked" @endif>
                                                        <label class="form-check-label fw-bold text-gray-400 ms-3" for="status">مسدود</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Tab content-->
                        <div class="d-flex justify-content-end">
                            <button type="submit" id="submit_btn" class="btn btn-primary">
                                <span class="indicator-label">ذخیره تغییرات</span>
                                <span class="indicator-progress">لطفا صبر کنید...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                            <!--end::Button-->
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('header')
    <link href="{{URL::asset('admin/js/all.css')}}" rel="stylesheet" type="text/css">
@endsection
@section('footer')
    <script src="{{secure_url('/admin-v2')}}/assets/plugins/custom/datatables/datatables.bundle.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/plugins/custom/formrepeater/formrepeater.bundle.js"></script>
    <!--end::Page Vendors Javascript-->
    <!--begin::Page سفارشی Javascript(used by this page)-->
    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/apps/ecommerce/catalog/save-product.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/js/widgets.bundle.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/widgets.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/apps/chat/chat.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/utilities/modals/upgrade-plan.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/utilities/modals/create-app.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/utilities/modals/users-search.js"></script>

    @include('admin.layout.submit-form')
    <script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
    <script src="{{secure_url('admin/js/all.js')}}" type="text/javascript"></script>
    <script>
        var editor = CKEDITOR.replace('detail', {
            height: 800,
            contentsLangDirection: 'rtl',
            filebrowserBrowseUrl: '/admin/ckfinder/browser',
            filebrowserUploadUrl: '/admin/ckfinder/connector?command=QuickUpload&type=Images',
            filebrowserImageBrowseUrl: '/admin/ckfinder/browser?type=Images'
        });

        editor.on('change', function (evt)/* evt yek moteghaier ast ke har esmi ke dos dari mishe gozasht */ {
            $('#changed').val(evt.editor.getData())

        })

        function ChangeName() {
            var text = $("#name").val()
            var replace = $("#name").val().replace('شماره مجازی کشور', "")
            var url = "خرید "
            var all = url + text;
            var replace_all = "وب سایت شماره مجازی ارائه دهنده شماره مجازی +   replace +  اپل آیدی ، اینستاگرام ، واتس آپ ، تلگرام ، تیک تاک و انواع اپلیکشین های دیگر با تنوع بیش از 30 کشور سرویس دهنده.  | پشتیبانی 24 ساعته | فقط با 3 کلیک شمارتو بخر   .";
            $(".replace").val(all)
            $("#desc").val(" وب سایت شماره مجازی ارائه دهنده  شماره مجازی از کشورهای " + replace + " ، روسیه ، امریکا ، آرژانتین ، آلمان ، انگلستان و بیش از 50 کشور و با تنوع بیش از 60 برنامه و اپلیکشین | پشتیبانی 24 ساعته | فقط با 3 کلیک شمارتو بخر. ")
        }
    </script>
@endsection
