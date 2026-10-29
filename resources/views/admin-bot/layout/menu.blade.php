<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <div id="kt_aside" class="aside py-9" data-kt-drawer="true" data-kt-drawer-name="aside"
             data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
             data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
             data-kt-drawer-toggle="#kt_aside_toggle">
            <div class="aside-menu flex-column-fluid ps-5 pe-3 mb-7" id="kt_aside_menu">
                <div class="w-100 hover-scroll-y d-flex pe-2" id="kt_aside_menu_wrapper" data-kt-scroll="true"
                     data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
                     data-kt-scroll-dependencies="#kt_aside_footer, #kt_header"
                     data-kt-scroll-wrappers="#kt_aside, #kt_aside_menu, #kt_aside_menu_wrapper"
                     data-kt-scroll-offset="102">
                    <div class="menu menu-column menu-rounded menu-sub-indention menu-active-bg fw-semibold my-auto"
                         id="#kt_aside_menu" data-kt-menu="true">


                        {{--                        <div data-kt-menu-trigger="click" class="menu-item menu-accordion">--}}

                        {{--									<span class="menu-link">--}}
                        {{--										<span class="menu-icon">--}}
                        {{--											<i class="ki-duotone ki-element-7 fs-2">--}}
                        {{--												<span class="path1"></span>--}}
                        {{--												<span class="path2"></span>--}}
                        {{--											</i>--}}
                        {{--										</span>--}}
                        {{--										<span class="menu-title">ابزارک</span>--}}
                        {{--										<span class="menu-arrow"></span>--}}
                        {{--									</span>--}}


                        {{--                            <div class="menu-sub menu-sub-accordion">--}}

                        {{--                                <div class="menu-item">--}}

                        {{--                                    <a class="menu-link" href="widgets/lists.html">--}}
                        {{--												<span class="menu-bullet">--}}
                        {{--													<span class="bullet bullet-dot"></span>--}}
                        {{--												</span>--}}
                        {{--                                        <span class="menu-title">لیست ها</span>--}}
                        {{--                                    </a>--}}

                        {{--                                </div>--}}


                        {{--                                <div class="menu-item">--}}

                        {{--                                    <a class="menu-link" href="widgets/statistics.html">--}}
                        {{--												<span class="menu-bullet">--}}
                        {{--													<span class="bullet bullet-dot"></span>--}}
                        {{--												</span>--}}
                        {{--                                        <span class="menu-title">امار</span>--}}
                        {{--                                    </a>--}}

                        {{--                                </div>--}}


                        {{--                                <div class="menu-item">--}}

                        {{--                                    <a class="menu-link" href="widgets/charts.html">--}}
                        {{--												<span class="menu-bullet">--}}
                        {{--													<span class="bullet bullet-dot"></span>--}}
                        {{--												</span>--}}
                        {{--                                        <span class="menu-title">نمودار ها</span>--}}
                        {{--                                    </a>--}}

                        {{--                                </div>--}}


                        {{--                                <div class="menu-item">--}}

                        {{--                                    <a class="menu-link" href="widgets/mixed.html">--}}
                        {{--												<span class="menu-bullet">--}}
                        {{--													<span class="bullet bullet-dot"></span>--}}
                        {{--												</span>--}}
                        {{--                                        <span class="menu-title">مخلوط</span>--}}
                        {{--                                    </a>--}}

                        {{--                                </div>--}}


                        {{--                                <div class="menu-item">--}}

                        {{--                                    <a class="menu-link" href="widgets/tables.html">--}}
                        {{--												<span class="menu-bullet">--}}
                        {{--													<span class="bullet bullet-dot"></span>--}}
                        {{--												</span>--}}
                        {{--                                        <span class="menu-title">جداول</span>--}}
                        {{--                                    </a>--}}

                        {{--                                </div>--}}


                        {{--                                <div class="menu-item">--}}

                        {{--                                    <a class="menu-link" href="widgets/feeds.html">--}}
                        {{--												<span class="menu-bullet">--}}
                        {{--													<span class="bullet bullet-dot"></span>--}}
                        {{--												</span>--}}
                        {{--                                        <span class="menu-title">تغذیه</span>--}}
                        {{--                                    </a>--}}

                        {{--                                </div>--}}

                        {{--                            </div>--}}

                        {{--                        </div>--}}
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">کاربران</span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{route('admin.bot-user-list')}}"
                            >
										<span class="menu-icon">
											<i class="ki-duotone ki-rocket fs-2">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
                                <span class="menu-title">لیست کاربران</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{route('admin.bot-order-list')}}">
										<span class="menu-icon">
											<i class="ki-duotone ki-rocket fs-2">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
                                <span class="menu-title">لیست سفارشات</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{route('admin.bot-payment-index')}}"
                            >
										<span class="menu-icon">
											<i class="ki-duotone ki-rocket fs-2">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
                                <span class="menu-title">لیست تراکنشات</span>
                            </a>
                        </div>
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">سرور ها</span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{route('admin.bot-server-index')}}"
                            >
										<span class="menu-icon">
											<i class="ki-duotone ki-rocket fs-2">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
                                <span class="menu-title">لیست سرور ها</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{route('admin.bot-server-create')}}"
                            >
										<span class="menu-icon">
											<i class="ki-duotone ki-rocket fs-2">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
                                <span class="menu-title">افزودن سرور</span>
                            </a>
                        </div>
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">تنظمیات</span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{route('admin.bot-message')}}"
                            >
										<span class="menu-icon">
											<i class="ki-duotone ki-rocket fs-2">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
                                <span class="menu-title">ارسال پیام در ربات</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{route('admin.bot-settings')}}"
                            >
										<span class="menu-icon">
											<i class="ki-duotone ki-rocket fs-2">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
                                <span class="menu-title">تنظمیات</span>
                            </a>
                        </div>
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">مدیریت</span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="https://preview.keenthemes.com/html/metronic/docs/base/utilities">
										<span class="menu-icon">
											<i class="ki-duotone ki-rocket fs-2">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
                                <span class="menu-title">مدیر ها</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="https://preview.keenthemes.com/html/metronic/docs/base/utilities"
                               target="_blank">
										<span class="menu-icon">
											<i class="ki-duotone ki-rocket fs-2">
												<span class="path1"></span>
												<span class="path2"></span>
											</i>
										</span>
                                <span class="menu-title">دسترسی ها</span>
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            <div class="aside-footer flex-column-auto px-9" id="kt_aside_menu">
                <div class="d-flex flex-stack">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-circle symbol-40px">
                            <img src="assets/media/avatars/300-1.jpg" alt="photo"/>
                        </div>
                        <div class="ms-2">
                            <a href="#" class="text-gray-800 text-hover-primary fs-6 fw-bold lh-1">پیمان ابدی</a>
                            <span class="text-muted fw-semibold d-block fs-7 lh-1">برنامه نویس پایتون</span>
                        </div>
                    </div>
                    <div class="ms-1">
                        <div class="btn btn-sm btn-icon btn-active-color-primary position-relative me-n2"
                             data-kt-menu-trigger="click" data-kt-menu-overflow="true" data-kt-menu-placement="top-end">
                            <i class="ki-duotone ki-setting-2 fs-1">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                             data-kt-menu="true">
                            <div class="menu-item px-3">
                                <div class="menu-content d-flex align-items-center px-3">
                                    <div class="symbol symbol-50px me-5">
                                        <img alt="Logo" src="assets/media/avatars/300-1.jpg"/>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <div class="fw-bold d-flex align-items-center fs-5">جلالی
                                            <span class="badge badge-light-success fw-bold fs-8 px-2 py-1 ms-2">حرفه ای</span>
                                        </div>
                                        <a href="#"
                                           class="fw-semibold text-muted text-hover-primary fs-7">max@kt.com</a>
                                    </div>
                                </div>
                            </div>
                            <div class="separator my-2"></div>
                            <div class="menu-item px-5">
                                <a href="account/overview.html" class="menu-link px-5">پروفایل من</a>
                            </div>
                            <div class="menu-item px-5">
                                <a href="apps/projects/list.html" class="menu-link px-5">
                                    <span class="menu-text">پروژه ها من</span>
                                    <span class="menu-badge">
												<span class="badge badge-light-danger badge-circle fw-bold fs-7">3</span>
											</span>
                                </a>
                            </div>
                            <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                 data-kt-menu-placement="right-end" data-kt-menu-offset="-15px, 0">
                                <a href="#" class="menu-link px-5">
                                    <span class="menu-title">اشتراک من</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                    <div class="menu-item px-3">
                                        <a href="account/referrals.html" class="menu-link px-5">مراجعات</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="account/billing.html" class="menu-link px-5">صورتحساب</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="account/statements.html" class="menu-link px-5">درگاه ها</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="account/statements.html" class="menu-link d-flex flex-stack px-5">بیانه
                                            ها
                                            <span class="ms-2 lh-0" data-bs-toggle="tooltip"
                                                  title="اظهارات خود را نمایش دهید">
													<i class="ki-duotone ki-information-5 fs-5">
														<span class="path1"></span>
														<span class="path2"></span>
														<span class="path3"></span>
													</i>
												</span></a>
                                    </div>
                                    <div class="separator my-2"></div>
                                    <div class="menu-item px-3">
                                        <div class="menu-content px-3">
                                            <label class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input w-30px h-20px" type="checkbox" value="1"
                                                       checked="checked" name="notifications"/>
                                                <span class="form-check-label text-muted fs-7">اعلان ها</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="menu-item px-5">
                                <a href="account/statements.html" class="menu-link px-5">من بیانه ها</a>
                            </div>
                            <div class="separator my-2"></div>
                            <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                 data-kt-menu-placement="right-end" data-kt-menu-offset="-15px, 0">
                                <a href="#" class="menu-link px-5">
											<span class="menu-title position-relative">زبان
											<span class="fs-8 rounded bg-light px-3 py-2 position-absolute translate-middle-y top-50 end-0">انگلیسی
											<img class="w-15px h-15px rounded-1 ms-2"
                                                 src="assets/media/flags/united-states.svg" alt=""/></span></span>
                                </a>
                                <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                    <div class="menu-item px-3">
                                        <a href="account/settings.html" class="menu-link d-flex px-5 active">
												<span class="symbol symbol-20px me-4">
													<img class="rounded-1" src="assets/media/flags/united-states.svg"
                                                         alt=""/>
												</span>انگلیسی</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="account/settings.html" class="menu-link d-flex px-5">
												<span class="symbol symbol-20px me-4">
													<img class="rounded-1" src="assets/media/flags/spain.svg" alt=""/>
												</span>اسپانیایی</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="account/settings.html" class="menu-link d-flex px-5">
												<span class="symbol symbol-20px me-4">
													<img class="rounded-1" src="assets/media/flags/germany.svg" alt=""/>
												</span>آلمانی</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="account/settings.html" class="menu-link d-flex px-5">
												<span class="symbol symbol-20px me-4">
													<img class="rounded-1" src="assets/media/flags/japan.svg" alt=""/>
												</span>ژاپنی</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="account/settings.html" class="menu-link d-flex px-5">
												<span class="symbol symbol-20px me-4">
													<img class="rounded-1" src="assets/media/flags/france.svg" alt=""/>
												</span>فرانسه</a>
                                    </div>
                                </div>
                            </div>
                            <div class="menu-item px-5 my-1">
                                <a href="account/settings.html" class="menu-link px-5">اکانت تنظیمات</a>
                            </div>
                            <div class="menu-item px-5">
                                <a href="authentication/layouts/corporate/sign-in.html" class="menu-link px-5">خروج</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <div id="kt_header" class="header header-bg">
                <div class="container-fluid">
                    <div class="header-brand me-5">
                        <div class="d-flex align-items-center d-lg-none ms-n2 me-2" title="مشاهده aside menu">
                            <div class="btn btn-icon btn-color-white btn-active-color-primary w-30px h-30px"
                                 id="kt_aside_toggle">
                                <i class="ki-duotone ki-abstract-14 fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                        <a href="index.html">
                            <img alt="Logo" src="{{url('bot-folder')}}/assets/media/logos/default-dark.svg"
                                 class="h-25px h-lg-30px d-none d-md-block"/>
                            <img alt="Logo" src="{{url('bot-folder')}}/assets/media/logos/default-small.svg"
                                 class="h-25px d-block d-md-none"/>
                        </a>
                    </div>
                    <div class="topbar d-flex align-items-stretch">
                        <div class="d-flex align-items-center me-2 me-lg-4">
                            <a href="#"
                               class="btn btn-icon btn-borderless btn-color-white btn-active-primary bg-white bg-opacity-10"
                               data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                               data-kt-menu-placement="bottom-end">
                                <i class="ki-duotone ki-night-day theme-light-show fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                    <span class="path6"></span>
                                    <span class="path7"></span>
                                    <span class="path8"></span>
                                    <span class="path9"></span>
                                    <span class="path10"></span>
                                </i>
                                <i class="ki-duotone ki-moon theme-dark-show fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                                 data-kt-menu="true" data-kt-element="theme-mode-menu">
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                       data-kt-value="light">
												<span class="menu-icon" data-kt-element="icon">
													<i class="ki-duotone ki-night-day fs-2">
														<span class="path1"></span>
														<span class="path2"></span>
														<span class="path3"></span>
														<span class="path4"></span>
														<span class="path5"></span>
														<span class="path6"></span>
														<span class="path7"></span>
														<span class="path8"></span>
														<span class="path9"></span>
														<span class="path10"></span>
													</i>
												</span>
                                        <span class="menu-title">روشن</span>
                                    </a>
                                </div>
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
												<span class="menu-icon" data-kt-element="icon">
													<i class="ki-duotone ki-moon fs-2">
														<span class="path1"></span>
														<span class="path2"></span>
													</i>
												</span>
                                        <span class="menu-title">تیره</span>
                                    </a>
                                </div>
                                <div class="menu-item px-3 my-0">
                                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                       data-kt-value="system">
												<span class="menu-icon" data-kt-element="icon">
													<i class="ki-duotone ki-screen fs-2">
														<span class="path1"></span>
														<span class="path2"></span>
														<span class="path3"></span>
														<span class="path4"></span>
													</i>
												</span>
                                        <span class="menu-title">سیستم</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <a href="index.html"
                               class="btn btn-icon btn-color-white btn-active-color-primary border-0 me-n3"
                               data-bs-toggle="tooltip" data-bs-placement="left" title="Return to launcher">
                                <i class="ki-duotone ki-cross-square fs-2x text-white">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
