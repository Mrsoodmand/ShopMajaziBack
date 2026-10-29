<aside class="col-lg-3 pt-4 pt-lg-0 pe-xl-5">
    <div class="bg-white rounded-3 shadow-lg pt-1 mb-5 mb-lg-0" style="position: sticky;top: 6rem">
        <div class="d-md-flex justify-content-between align-items-center text-center text-md-start p-4">
            <div class="d-md-flex align-items-center">
                <div class="ps-md-3">
                    <h3 class="fs-base mb-0">{{\Illuminate\Support\Facades\Auth::guard('user')->user()->name}}</h3><span class="text-accent fs-sm">{{\Illuminate\Support\Facades\Auth::guard('user')->user()->mobile}}</span>
                </div>
            </div>
            <a class="btn btn-primary d-lg-none mb-2 mt-3 mt-md-0" href="#account-menu" data-bs-toggle="collapse"
               aria-expanded="false"><i class="ci-menu me-2"></i>منو</a>
        </div>
        <style>
            #account-menu a{
                font-size: 14px;
            }
        </style>
        <div class="d-lg-block collapse" id="account-menu">
            <div class="bg-secondary px-4 py-3">
                <h3 class="fs-sm mb-0 text-muted">داشبورد</h3>
            </div>
            <ul class="list-unstyled mb-0">
                <li class="border-bottom mb-0">
                    <a class="nav-link-style d-flex align-items-center px-4 py-3 @if(Request::is('user/home')) active @endif" href="{{secure_url('/user/home')}}">
                        <i class="fa-regular fa-square-user me-2"></i>
                        داشبورد
                    </a>
                </li>
                <li class="border-bottom mb-0">
                    <a class="nav-link-style d-flex align-items-center px-4 py-3 @if(Request::is('user/referral')) active @endif" href="{{secure_url('/user/referral')}}">
                        <i class="fa-regular fa-share-nodes me-2"></i>
                        کسب درآمد
                    </a>
                </li>
                <li class="border-bottom mb-0">
                    <a class="nav-link-style d-flex align-items-center px-4 py-3 @if(Request::is('user/orders')) active @endif" href="{{secure_url('/user/orders')}}">
                        <i class="fa-light fa-bags-shopping me-2"></i>
                        سفارشات
                    </a>
                </li>
                <li class="border-bottom mb-0">
                    <a class="nav-link-style d-flex align-items-center px-4 py-3 @if(Request::is('user/wallet')) active @endif" href="{{secure_url('/user/wallet')}}">
                        <i class="fa-regular fa-file-invoice-dollar opacity-60 me-2"></i>تراکنشات
                    </a>
                </li>
                <li class="mb-0">
                    <a class="nav-link-style d-flex align-items-center px-4 py-3 @if(Request::is('user/tickets')) active @endif" href="{{secure_url('/user/tickets')}}">
                        <i class="fa-duotone fa-messages opacity-60 me-2"></i>تیکت ها
                    </a>
                </li>
            </ul>
            <div class="bg-secondary px-4 py-3">
                <h3 class="fs-sm mb-0 text-muted">تنظیمات حساب</h3>
            </div>
            <ul class="list-unstyled mb-0">
                <li class="border-bottom mb-0">
                    <a class="nav-link-style d-flex align-items-center px-4 py-3" href="{{secure_url('user/profile')}}"><i class="ci-user opacity-60 me-2"></i>اطلاعات پروفایل</a>
                </li>
                <li class="border-bottom mb-0">
                    <a class="nav-link-style d-flex align-items-center px-4 py-3" href="{{secure_url('user/logout')}}"><i class="ci-sign-out opacity-60 me-2"></i>خروج</a>
                </li>
            </ul>
        </div>
    </div>
</aside>
