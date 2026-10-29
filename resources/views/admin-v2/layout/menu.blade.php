<?php $Admin = \Illuminate\Support\Facades\Auth::guard('admin')->user(); ?>

<body id="kt_body"
      class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed"
      style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <div id="kt_aside" class="aside aside-dark aside-hoverable" data-kt-drawer="true" data-kt-drawer-name="aside"
             data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
             data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
             data-kt-drawer-toggle="#kt_aside_mobile_toggle">
            <div class="aside-logo flex-column-auto" id="kt_aside_logo">
                <a href="{{secure_url('/admin/home')}}">
                    <img alt="Logo" src="{{secure_url('/admin-v2')}}/assets/media/logos/logo-1-dark.svg" class="h-25px logo"/>
                </a>
                <div id="kt_aside_toggle" class="btn btn-icon w-auto px-0 btn-active-color-primary aside-toggle"
                     data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
                     data-kt-toggle-name="aside-minimize">
                    <span class="svg-icon svg-icon-1 rotate-180">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none">
									<path opacity="0.5"
                                          d="M14.2657 11.4343L18.45 7.25C18.8642 6.83579 18.8642 6.16421 18.45 5.75C18.0358 5.33579 17.3642 5.33579 16.95 5.75L11.4071 11.2929C11.0166 11.6834 11.0166 12.3166 11.4071 12.7071L16.95 18.25C17.3642 18.6642 18.0358 18.6642 18.45 18.25C18.8642 17.8358 18.8642 17.1642 18.45 16.75L14.2657 12.5657C13.9533 12.2533 13.9533 11.7467 14.2657 11.4343Z"
                                          fill="currentColor"/>
									<path
                                            d="M8.2657 11.4343L12.45 7.25C12.8642 6.83579 12.8642 6.16421 12.45 5.75C12.0358 5.33579 11.3642 5.33579 10.95 5.75L5.40712 11.2929C5.01659 11.6834 5.01659 12.3166 5.40712 12.7071L10.95 18.25C11.3642 18.6642 12.0358 18.6642 12.45 18.25C12.8642 17.8358 12.8642 17.1642 12.45 16.75L8.2657 12.5657C7.95328 12.2533 7.95328 11.7467 8.2657 11.4343Z"
                                            fill="currentColor"/>
								</svg>
							</span>
                </div>
            </div>
            <div class="aside-menu flex-column-fluid">
                <div class="hover-scroll-overlay-y my-5 my-lg-5" id="kt_aside_menu_wrapper" data-kt-scroll="true"
                     data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
                     data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer"
                     data-kt-scroll-wrappers="#kt_aside_menu" data-kt-scroll-offset="0">
                    <div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500"
                         id="#kt_aside_menu" data-kt-menu="true" data-kt-menu-exp="false">
                        <div class="menu-item here show menu-accordion">
                            <a href="{{secure_url('admin/home')}}" class="menu-link">
                                <span class="menu-icon"><i class="fa-regular fa-heart text-primary"></i></span>
                                <span class="menu-title">داشبورد</span>
                            </a>
                        </div>
                        @if($Admin->hasAnyPermission(['ip-sabet-list',
'google-voice-list',
'virtual-number-countries',
'virtual-number-services',
'virtual-service-setting',
'create-edit-virtual-service',
'virtual-service',
'add-apple-id',
'custom-apple-id-order',
'ready-apple-id-orders']))
                            <div class="menu-item">
                                <div class="menu-content pt-8 pb-2">
                                    <span class="menu-section text-muted text-uppercase fs-8 ls-1">خدمات</span>
                                </div>
                            </div>
                        @endif
                        @if($Admin->hasAnyPermission(['	accounts-list','accounts-create-edit']))
                            <!-- اشتراک سایت ها -->
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="fa-light fa-globe"></i></span>
                                <span class="menu-title">اشتراک سایت های خارجی</span>
                                <span class="menu-arrow"></span>
                            </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    <div class="menu-item">
                                        <a class="menu-link" href="{{secure_url('admin/accounts')}}">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">لیست سایت ها</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link" href="{{secure_url('/admin/accounts/create/')}}">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">افزودن سایت</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link" href="">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">سفارشات</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($Admin->hasAnyPermission(['virtual-service','virtual-service-orders','create-edit-virtual-service']))
                            <!-- خدمات مجازی -->
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="fa-light fa-earth-americas"></i></span>
                                <span class="menu-title">خدمات مجازی</span>
                                <span class="menu-arrow"></span>
                            </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    @can('create-edit-virtual-service')
                                        <div class="menu-item">
                                            <a class="menu-link" href="{{route('admin.service-list')}}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">لیست خدمات </span>
                                            </a>
                                        </div>
                                    @endcan
                                    @can('virtual-service-setting')
                                        <div class="menu-item">
                                            <a class="menu-link" href="{{route('admin.virtualService-setting-page')}}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">تنظیمات</span>
                                            </a>
                                        </div>
                                    @endcan
                                    @can('virtual-service-orders')
                                        <div class="menu-item">
                                            <a class="menu-link" href="">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">سفارشات</span>
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @endif
                        @if($Admin->hasAnyPermission(['virtual-number-countries','virtual-number-services','virtual-number-setting','virtual-number-orders']))
                            <!-- شماره مجازی -->
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="fa-light fa-sim-card"></i></span>
                                <span class="menu-title">شماره مجازی</span>
                                <span class="menu-arrow"></span>
                            </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    @can('virtual-number-services')
                                        <div class="menu-item">
                                            <a class="menu-link" href="{{route('admin.Virtual-Services')}}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">لیست سرویس ها</span>
                                            </a>
                                        </div>
                                    @endcan
                                    @can('virtual-number-countries')
                                        <div class="menu-item">
                                            <a class="menu-link" href="{{route('admin.Virtual-Countries')}}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">لیست کشور ها</span>
                                            </a>
                                        </div>
                                    @endcan
                                    @can('virtual-number-setting')
                                        <div class="menu-item">
                                            <a class="menu-link" href="{{route('admin.virtualNumber-setting-page')}}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">تنظیمات</span>
                                            </a>
                                        </div>
                                    @endcan
                                    @can('virtual-number-orders')
                                        <div class="menu-item">
                                            <a class="menu-link" href="">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">سفارشات</span>
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @endif
                        @if($Admin->hasAnyPermission(['ip-sabet-orders','ip-sabet-list']))
                            <!-- آی پی ثابت -->
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="fa-regular fa-location-dot"></i></span>
                                <span class="menu-title">آی پی ثابت</span>
                                <span class="menu-arrow"></span>
                            </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    @can('ip-sabet-list')
                                        <div class="menu-item">
                                            <a class="menu-link" href="{{route('admin.ip-sabet-list','pu-ip-sabet')}}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">آی پی اشتراکی</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link" href="">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">آی پی اختصاصی</span>
                                            </a>
                                        </div>
                                    @endcan
                                    @can('ip-sabet-orders')
                                        <div class="menu-item">
                                            <a class="menu-link" href="">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">سفارشات</span>
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @endif
                        @if($Admin->hasAnyPermission(['google-voice-list','google-voice-orders']))
                            <!-- گوگل ویس -->
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="fa-brands fa-google"></i></span>
                                <span class="menu-title">گوگل ویس</span>
                                <span class="menu-arrow"></span>
                            </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    @can('google-voice-list')
                                        <div class="menu-item">
                                            <a class="menu-link" href="{{route('admin.google-voice-list')}}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">لیست گوگل ویس ها</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link" href="">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">افزودن گوگل ویس</span>
                                            </a>
                                        </div>
                                    @endcan
                                    @can('google-voice-orders')
                                        <div class="menu-item">
                                            <a class="menu-link" href="">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">سفارشات</span>
                                            </a>
                                        </div>
                                    @endcan()
                                </div>
                            </div>
                        @endif
                        @if($Admin->hasAnyPermission(['create-edit-giftcards','giftcards-orders']))
                            <!-- گیفت کارت -->
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="fa-regular fa-gift"></i></span>
                                <span class="menu-title">گیف کارت</span>
                                <span class="menu-arrow"></span>
                            </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    @can('create-edit-giftcards')
                                        <div class="menu-item">
                                            <a class="menu-link" href="">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">لیست گیف کارت</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link" href="">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">افزودن گیف کارت</span>
                                            </a>
                                        </div>
                                    @endcan
                                    @can('giftcards-orders')
                                        <div class="menu-item">
                                            <a class="menu-link" href="">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">سفارشات</span>
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @endif
                        @if($Admin->hasAnyPermission(['add-apple-id','ready-apple-id-orders']))
                            <!-- اپل ایدی -->
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="fa-brands fa-apple"></i></span>
                                <span class="menu-title">اپل آیدی</span>
                                <span class="menu-arrow"></span>
                            </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    @can('add-apple-id')
                                        <div class="menu-item">
                                            <a class="menu-link" href="{{route('admin.apple-id-list')}}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">لیست اپل آیدی ها</span>
                                            </a>
                                        </div>
                                        <div class="menu-item">
                                            <a class="menu-link" href="{{route('admin.apple-id-create')}}">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">افزودن آپل آیدی</span>
                                            </a>
                                        </div>
                                    @endcan
                                    @can('ready-apple-id-orders')
                                        <div class="menu-item">
                                            <a class="menu-link" href="">
                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                <span class="menu-title">سفارشات</span>
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @endif
                        @if($Admin->hasAnyPermission(['tickets-list','users-list','comments-list']))
                            <div class="menu-item">
                                <div class="menu-content pt-8 pb-2">
                                    <span class="menu-section text-muted text-uppercase fs-8 ls-1">کاربران</span>
                                </div>
                            </div>
                        @endif
                        @can('users-list')
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.user list')}}">
                                    <span class="menu-icon"><i class="fa-light fa-users"></i></span>
                                    <span class="menu-title">لیست کاربران</span>
                                </a>
                            </div>
                        @endcan
                        @if($Admin->hasAnyRole(['super-admin']))
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="fa-light fa-globe"></i></span>
                                <span class="menu-title">ربات</span>
                                <span class="menu-arrow"></span>
                            </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    <div class="menu-item">
                                        <a class="menu-link" href="{{route('admin.bot-users')}}">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">لیست کاربران</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link" href="{{route('admin.bot-orders')}}">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">لیست سفارشات</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link" href="{{route('admin.hegza-ip-list',['type' =>'pu-ip-sabet'])}}">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">ای پی ها</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link" href="{{route('admin.bot-messages')}}">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">ارسال پیام</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                        @endif

                        @can('tickets-list')
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.tickets list')}}">
                                    <span class="menu-icon"><i class="fa-light fa-users"></i></span>
                                    <span class="menu-title">لیست تیکت ها</span>
                                </a>
                            </div>
                        @endcan
                        @can('comments-list')
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.comment-index')}}">
                                    <span class="menu-icon"><i class="fa-light fa-users"></i></span>
                                    <span class="menu-title">لیست کامنت ها</span>
                                </a>
                            </div>
                        @endcan

                        @if($Admin->hasAnyPermission(['order','transaction-list','bank-transaction','moghayerat','scam-transaction']))
                            <div class="menu-item">
                                <div class="menu-content pt-8 pb-2">
                                    <span class="menu-section text-muted text-uppercase fs-8 ls-1">سفارشات و حسابداری</span>
                                </div>
                            </div>
                        @endif
                        @can('order')
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.order list')}}">
                                    <span class="menu-icon"><i class="fa-regular fa-basket-shopping-simple"></i></span>
                                    <span class="menu-title">لیست سفارشات</span>
                                </a>
                            </div>
                        @endcan
                        @can('transaction-list')
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.transaction list')}}">
                                    <span class="menu-icon"><i class="fa-regular fa-money-from-bracket"></i></span>
                                    <span class="menu-title">لیست تراکنش ها</span>
                                </a>
                            </div>
                        @endcan
                        @can('bank-transaction')
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.bank payments list')}}">
                                    <span class="menu-icon"><i class="fa-sharp fa-solid fa-credit-card"></i></span>
                                    <span class="menu-title">پرداختی های بانکی</span>
                                </a>
                            </div>
                        @endcan
                        @can('moghayerat')
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.contradiction payments list')}}">
                                    <span class="menu-icon"><i class="fa-light fa-binoculars"></i></span>
                                    <span class="menu-title">مغایرت</span>
                                </a>
                            </div>
                        @endcan
                        @can('scam-transaction')
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="">
                                    <span class="menu-icon"><i class="fa-regular fa-user-secret"></i></span>
                                    <span class="menu-title">تراکنش های مشکوک</span>
                                </a>
                            </div>
                        @endcan
                        @if(
    $Admin->hasAnyRole(['super-admin']) ||
    $Admin->hasAnyPermission(['blog-list','news-list','page-list'])
)
                            <div class="menu-item">
                                <div class="menu-content pt-8 pb-2">
                                    <span class="menu-section text-muted text-uppercase fs-8 ls-1">تنظمیات عمومی</span>
                                </div>
                            </div>
                        @endif
                        @can('blog-list')
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.blog list')}}">
                                    <span class="menu-icon"><i class="fa-regular fa-user-secret"></i></span>
                                    <span class="menu-title">لیست بلاگ ها</span>
                                </a>
                            </div>
                        @endcan
                        @can('news-list')
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.news list')}}">
                                    <span class="menu-icon"><i class="fa-regular fa-user-secret"></i></span>
                                    <span class="menu-title">لیست اخبار</span>
                                </a>
                            </div>
                        @endcan
                        @can('page-list')
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.page list')}}">
                                    <span class="menu-icon"><i class="fa-regular fa-user-secret"></i></span>
                                    <span class="menu-title">لیست صفحات</span>
                                </a>
                            </div>
                        @endcan
                        @if($Admin->hasAnyRole(['super-admin']))
                            <div class="menu-item">
                                <div class="menu-content pt-8 pb-2">
                                    <span class="menu-section text-muted text-uppercase fs-8 ls-1">مدیریت</span>
                                </div>
                            </div>
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.role list')}}">
                                    <span class="menu-icon"><i class="fa-light fa-binoculars"></i></span>
                                    <span class="menu-title">دسترسی ها</span>
                                </a>
                            </div>
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.admin list')}}">
                                    <span class="menu-icon"><i class="fa-light fa-binoculars"></i></span>
                                    <span class="menu-title">مدیران </span>
                                </a>
                            </div>
                            <div class="menu-item menu-accordion">
                                <a class="menu-link" href="{{route('admin.partner list')}}">
                                    <span class="menu-icon"><i class="fa-light fa-binoculars"></i></span>
                                    <span class="menu-title">همکاران </span>
                                </a>
                            </div>

                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="fa-light fa-globe"></i></span>
                                <span class="menu-title">ربات</span>
                                <span class="menu-arrow"></span>
                            </span>
                                <div class="menu-sub menu-sub-accordion menu-active-bg">
                                    <div class="menu-item">
                                        <a class="menu-link" href="{{route('admin.bot-users')}}">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">لیست کاربران</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link" href="{{route('admin.bot-orders')}}">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">لیست سفارشات</span>
                                        </a>
                                    </div>
                                    <div class="menu-item">
                                        <a class="menu-link" href="{{route('admin.hegza-ip-list',['type' =>'pu-ip-sabet'])}}">
                                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                            <span class="menu-title">ای پی ها</span>
                                        </a>
                                    </div>
                                </div>
                            </div>


                        @endif

                    </div>
                </div>
            </div>
        </div>
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <div id="kt_header" style="" class="header align-items-stretch">
                <div class="container-fluid d-flex align-items-stretch justify-content-between">
                    <div class="d-flex align-items-center d-lg-none ms-n2 me-2" title="مشاهده aside menu">
                        <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px"
                             id="kt_aside_mobile_toggle">
                            <span class="svg-icon svg-icon-1">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none">
											<path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z"
                                                  fill="currentColor"></path>
											<path opacity="0.3"
                                                  d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z"
                                                  fill="currentColor"></path>
										</svg>
									</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                        <a href="{{secure_url('/admin/home')}}" class="d-lg-none">
                            <img alt="Logo" src="{{secure_url('/admin-v2')}}/assets/media/logos/logo-2.svg" class="h-30px">
                        </a>
                    </div>
                    <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
                        <div class="d-flex align-items-center" id="kt_header_nav">
                            <!--begin::Page title-->
                            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                                 data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_header_nav'}"
                                 class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                                <!--begin::Title-->
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">بدون نوار ابزار</h1>
                                <!--end::Title-->
                                <!--begin::Separator-->
                                <span class="h-20px border-gray-300 border-start mx-4"></span>
                                <!--end::Separator-->
                                <!--begin::Breadcrumb-->
                                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                                    <!--begin::Item-->
                                    <li class="breadcrumb-item text-muted">
                                        <a href="../../demo1/dist/index.html"
                                           class="text-muted text-hover-primary">خانه</a>
                                    </li>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <li class="breadcrumb-item">
                                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                                    </li>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <li class="breadcrumb-item text-muted">نوار ابزار</li>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <li class="breadcrumb-item">
                                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                                    </li>
                                    <!--end::Item-->
                                    <!--begin::Item-->
                                    <li class="breadcrumb-item text-dark">بدون نوار ابزار</li>
                                    <!--end::Item-->
                                </ul>
                                <!--end::Breadcrumb-->
                            </div>
                            <!--end::Page title-->
                        </div>
                        <div class="d-flex align-items-stretch flex-shrink-0">
                            <div class="d-flex align-items-center ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
                                <div class="cursor-pointer symbol symbol-30px symbol-md-40px"
                                     data-kt-menu-trigger="click" data-kt-menu-attach="parent"
                                     data-kt-menu-placement="bottom-end">
                                    <img src="{{secure_url('/admin-v2')}}/assets/media/avatars/300-1.jpg" alt="user">
                                </div>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-bold py-4 fs-6 w-275px"
                                     data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <div class="menu-content d-flex align-items-center px-3">
                                            <div class="symbol symbol-50px me-5">
                                                <img alt="Logo"
                                                     src="{{secure_url('admin-v2')}}/assets/media/avatars/300-1.jpg">
                                            </div>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bolder d-flex align-items-center fs-5">{{\Illuminate\Support\Facades\Auth::guard('admin')->user()->full_name}}
                                                    <br>
                                                    <span class="badge badge-light-success fw-bolder fs-8 px-2 py-1 ms-2">مدیر</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="separator my-2"></div>
                                    <div class="menu-item px-5">
                                        <a href="{{secure_url('/admin/profile')}}" class="menu-link px-5">پروفایل من</a>
                                    </div>
                                    <div class="separator my-2"></div>
                                    <div class="menu-item px-5">
                                        <a href="{{secure_url('/admin-url-login-with-secure-path/logout')}}"
                                           class="menu-link px-5">خروج</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

