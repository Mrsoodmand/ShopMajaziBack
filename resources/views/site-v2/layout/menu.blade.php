{{--<aside class="offcanvas offcanvas-expand w-100 border-end zindex-lg-5 pt-lg-5" id="sideNav"--}}
{{--       style="max-width: 21.875rem;">--}}
{{--    <div class="pt-2 d-none d-lg-block"></div>--}}
{{--    <ul class="nav nav-tabs nav-justified mt-4 mt-lg-5 mb-0" role="tablist" style="min-height: 3rem;">--}}
{{--        <li class="nav-item"><a class="nav-link fw-medium active" href="#menu" data-bs-toggle="tab" role="tab">منو</a>--}}
{{--        </li>--}}
{{--        <li class="nav-item"><a class="nav-link fw-medium" href="#categories" data-bs-toggle="tab" role="tab">برترین--}}
{{--                محصولات</a></li>--}}
{{--        <li class="nav-item d-lg-none"><a class="nav-link fs-sm" href="#" data-bs-dismiss="offcanvas" role="tab"><i--}}
{{--                        class="ci-close fs-xs me-2"></i>بستن</a></li>--}}
{{--    </ul>--}}
{{--    <div class="offcanvas-body px-0 pt-3 pb-0" data-simplebar>--}}
{{--        <div class="tab-content">--}}
{{--            <!-- Menu-->--}}
{{--            <div class="sidebar-nav tab-pane fade show active" id="menu" role="tabpanel">--}}
{{--                <div class="widget widget-categories">--}}
{{--                    <div class="accordion" id="shop-menu">--}}
{{--                        <!-- Homepages-->--}}
{{--                        <div class="accordion-item border-bottom">--}}
{{--                            <h3 class="accordion-header px-grid-gutter">--}}
{{--                                <a class="nav-link-style d-block fs-md fw-normal py-3" href="{{secure_url('/')}}">--}}
{{--                                    <span class="d-flex align-items-center">--}}
{{--                                        <i class="ci-discount fs-lg text-danger mt-n1 me-2"></i>--}}
{{--                                        صفحه اصلی--}}
{{--                                    </span>--}}
{{--                                </a>--}}
{{--                            </h3>--}}
{{--                        </div>--}}
{{--                        <div class="accordion-item border-bottom">--}}
{{--                            <h3 class="accordion-header px-grid-gutter">--}}
{{--                                <a class="nav-link-style d-block fs-md fw-normal py-3" href="{{route('virtual number index')}}">--}}
{{--                                    <span class="d-flex align-items-center">--}}
{{--                                        <i class="ci-discount fs-lg text-danger mt-n1 me-2"></i>--}}
{{--                                        خدمات مجازی--}}
{{--                                    </span>--}}
{{--                                </a>--}}
{{--                            </h3>--}}
{{--                        </div>--}}
{{--                        <div class="accordion-item border-bottom">--}}
{{--                            <h3 class="accordion-header px-grid-gutter">--}}
{{--                                <a class="nav-link-style d-block fs-md fw-normal py-3" href="{{secure_url('/')}}">--}}
{{--                                    <span class="d-flex align-items-center">--}}
{{--                                        <i class="ci-discount fs-lg text-danger mt-n1 me-2"></i>--}}
{{--                                        خرید شارژ--}}
{{--                                    </span>--}}
{{--                                </a>--}}
{{--                            </h3>--}}
{{--                        </div>--}}
{{--                        <div class="accordion-item border-bottom">--}}
{{--                            <h3 class="accordion-header px-grid-gutter">--}}
{{--                                <a class="nav-link-style d-block fs-md fw-normal py-3" href="{{secure_url('/')}}">--}}
{{--                                    <span class="d-flex align-items-center">--}}
{{--                                        <i class="ci-discount fs-lg text-danger mt-n1 me-2"></i>--}}
{{--                                        وبلاگ--}}
{{--                                    </span>--}}
{{--                                </a>--}}
{{--                            </h3>--}}
{{--                        </div>--}}
{{--                        <div class="accordion-item border-bottom">--}}
{{--                            <h3 class="accordion-header px-grid-gutter">--}}
{{--                                <a class="nav-link-style d-block fs-md fw-normal py-3" href="{{secure_url('/')}}">--}}
{{--                                    <span class="d-flex align-items-center">--}}
{{--                                        <i class="ci-discount fs-lg text-danger mt-n1 me-2"></i>--}}
{{--                                        تماس با ما--}}
{{--                                    </span>--}}
{{--                                </a>--}}
{{--                            </h3>--}}
{{--                        </div>--}}
{{--                        <div class="accordion-item border-bottom">--}}
{{--                            <h3 class="accordion-header px-grid-gutter">--}}
{{--                                <a class="nav-link-style d-block fs-md fw-normal py-3" href="{{secure_url('/')}}">--}}
{{--                                    <span class="d-flex align-items-center">--}}
{{--                                        <i class="ci-discount fs-lg text-danger mt-n1 me-2"></i>--}}
{{--                                       درباره ما--}}
{{--                                    </span>--}}
{{--                                </a>--}}
{{--                            </h3>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <!-- Categories-->--}}
{{--            <div class="sidebar-nav tab-pane fade" id="categories" role="tabpanel">--}}
{{--                <div class="widget widget-categories">--}}
{{--                    <div class="accordion" id="shop-categories">--}}
{{--                        <div class="accordion-item border-bottom">--}}
{{--                            <h3 class="accordion-header px-grid-gutter">--}}
{{--                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#bakery" aria-expanded="false" aria-controls="bakery">--}}
{{--                                    <a href="javascript:;">--}}
{{--                                         <span class="d-flex align-items-center">--}}
{{--                                        <i class="ci-bread fs-lg opacity-60 mt-n1 me-2"></i>--}}
{{--                                       خدمات تلگرام--}}
{{--                                    </span>--}}
{{--                                    </a>--}}
{{--                                </button>--}}
{{--                            </h3>--}}
{{--                            <div class="collapse show" id="bakery" data-bs-parent="#shop-categories">--}}
{{--                                <div class="px-grid-gutter pt-1 pb-4">--}}
{{--                                    <div class="widget widget-links">--}}
{{--                                        <ul class="widget-list">--}}
{{--                                            <li class="widget-list-item"><a class="widget-list-link" href="#">خرید ممبر فیک</a></li>--}}
{{--                                            <li class="widget-list-item"><a class="widget-list-link" href="#">خرید ممبر واقعی</a></li>--}}
{{--                                            <li class="widget-list-item"><a class="widget-list-link" href="#">خرید بازدید پست</a></li>--}}
{{--                                            <li class="widget-list-item"><a class="widget-list-link" href="#">خرید رای</a></li>--}}
{{--                                        </ul>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="accordion-item border-bottom">--}}
{{--                            <h3 class="accordion-header px-grid-gutter">--}}
{{--                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#bakery" aria-expanded="false" aria-controls="bakery">--}}
{{--                                    <a href="javascript:;">--}}
{{--                                         <span class="d-flex align-items-center">--}}
{{--                                        <i class="ci-bread fs-lg opacity-60 mt-n1 me-2"></i>--}}
{{--                                       خدمات اینستاگرام--}}
{{--                                    </span>--}}
{{--                                    </a>--}}
{{--                                </button>--}}
{{--                            </h3>--}}
{{--                            <div class="collapse show" id="bakery" data-bs-parent="#shop-categories">--}}
{{--                                <div class="px-grid-gutter pt-1 pb-4">--}}
{{--                                    <div class="widget widget-links">--}}
{{--                                        <ul class="widget-list">--}}
{{--                                            <li class="widget-list-item"><a class="widget-list-link" href="#">خرید فالور فیک</a></li>--}}
{{--                                            <li class="widget-list-item"><a class="widget-list-link" href="#">خرید فالور واقعی</a></li>--}}
{{--                                            <li class="widget-list-item"><a class="widget-list-link" href="#">خرید لایک</a></li>--}}
{{--                                            <li class="widget-list-item"><a class="widget-list-link" href="#">خرید بازدید استوری</a></li>--}}
{{--                                            <li class="widget-list-item"><a class="widget-list-link" href="#">خرید بازدید پست - REEL</a></li>--}}
{{--                                        </ul>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <div class="offcanvas-cap d-block px-grid-gutter pt-5 pb-4 mb-2">--}}
{{--        <div class="d-flex mb-3"><i class="ci-support h4 mb-0 fw-normal text-primary mt-1 me-1"></i>--}}
{{--            <div class="ps-2">--}}
{{--                <div class="text-muted fs-sm">پشتیبانی</div>--}}
{{--                <a class="nav-link-style fs-md" href="tel:+09302736430">09302736430</a>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="d-flex mb-3"><i class="ci-mail h5 mb-0 fw-normal text-primary mt-1 me-1"></i>--}}
{{--            <div class="ps-2">--}}
{{--                <div class="text-muted fs-sm">ایمیل</div>--}}
{{--                <a class="nav-link-style fs-md" href="">customer@example.com</a>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <h6 class="pt-2 pb-1">ما را دنبال کنید</h6><a class="btn-social bs-outline bs-twitter me-2 mb-2" href="#"><i--}}
{{--                    class="ci-twitter"></i></a><a class="btn-social bs-outline bs-facebook me-2 mb-2" href="#"><i--}}
{{--                    class="ci-facebook"></i></a><a class="btn-social bs-outline bs-instagram me-2 mb-2" href="#"><i--}}
{{--                    class="ci-instagram"></i></a><a class="btn-social bs-outline bs-youtube me-2 mb-2" href="#"><i--}}
{{--                    class="ci-youtube"></i></a>--}}
{{--    </div>--}}
{{--</aside>--}}
{{--<main class="offcanvas-enabled" style="padding-top: 5rem;">--}}
<main class="">
