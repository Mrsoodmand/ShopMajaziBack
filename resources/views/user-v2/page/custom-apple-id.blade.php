@php
    $Decrypt = new \App\lib\PhpEncrypt();
@endphp
<style>
    .btn-icon {
        padding: 0.1rem 0.1rem!important;
    }
    .custom-nav{
        display: block;
        padding: 0.75rem 0.25rem
    }
    .button-area{
        margin-top: 20px;left: -15px; position: absolute;
    }
    @media screen and (max-height: 425px) {
        .button-area{
            margin-top: 30px;
        }
    }
</style>
<div class="card border-0 shadow-lg mt-3">
    <div class="card-header">
        <h4>اطلاعات ارسال شده توسط شما:</h4>
    </div>
    <div class="card-body pb-2">
        @php
        $Data = json_decode($OrderItems->first()->detail,true);
        @endphp
        <div class="row">
            <div class="col-lg-6 col-12">
                <div class="custom-nav completed">
                    <div class="d-flex align-items-center" style="position: relative">
                        <div>
                            <div class="media-tab-subtitle text-muted fs-sm mb-1">نام</div>
                            <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$Data['name']}}</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12">
                <div class="custom-nav completed">
                    <div class="d-flex align-items-center" style="position: relative">
                        <div>
                            <div class="media-tab-subtitle text-muted fs-sm mb-1"> نام خانوادگی</div>
                            <label class="media-tab-title fs-md text-nowrap mb-0">
                                <span>{{$Data['family']}}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12">
                <div class="custom-nav completed">
                    <div class="d-flex align-items-center" style="position: relative">
                        <div>
                            <div class="media-tab-subtitle text-muted fs-sm mb-1">ایمیل</div>
                            <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$Data['email']}}</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-12">
                <div class="custom-nav completed">
                    <div class="d-flex align-items-center" style="position: relative">
                        <div>
                            <div class="media-tab-subtitle text-muted fs-sm mb-1">تاریخ تولد</div>
                            <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$Data['b_day']}}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@if(count($AppleIds) != 0)
    @foreach($AppleIds as $items)
        <div class="card border-0 shadow-lg mt-3">
            <div class="card-header">
                <h4>اطلاعات اپل آیدی:</h4>
            </div>
            <div class="card-body">
                @php
                    $Data = json_decode($Decrypt->decrypt($items->serial,$items->code),true);
                @endphp
                <div class="row">
                    <div class="col-lg-4 col-12">
                        <div class="custom-nav completed">
                            <div class="d-flex align-items-center" style="position: relative">
                                <div>
                                    <div class="media-tab-subtitle text-muted fs-sm mb-1">اپل آیدی</div>
                                    <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$Data['appleid']}}</h6>
                                </div>
                                <div class="button-area">
                                    <button class="btn btn-icon fav-btn" onclick="myFunction('{{$Data['appleid']}}')" type="button" data-bs-toggle="tooltip" title="" data-bs-original-title="کپی کردن" aria-label="کپی"><i class="fa-regular fa-copy text-primary"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="custom-nav completed">
                            <div class="d-flex align-items-center" style="position: relative">
                                <div>
                                    <div class="media-tab-subtitle text-muted fs-sm mb-1">تاریخ تولد</div>
                                    <label class="media-tab-title fs-md text-nowrap mb-0">
                                        <span id="brithday-{{$items->id}}">*****</span>
                                    </label>
                                </div>
                                <div class="button-area">
                                    <button class="btn btn-icon fav-btn show-code" data-type="0" type="button" data-id="{{$items->id}}" data-input="brithday" data-code="{{$Data['brithday']}}" data-bs-original-title="مشاهده" data-bs-toggle="tooltip" aria-label="مشاهده"><i id="brithday-eye-{{$items->id}}" class="fa-light fa-eye text-info"></i></button>
                                    <button class="btn btn-icon fav-btn" onclick="myFunction('{{$Data['brithday']}}')" type="button" data-bs-toggle="tooltip" title="" data-bs-original-title="کپی کردن" aria-label="کپی"><i class="fa-regular fa-copy text-primary"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="custom-nav completed">
                            <div class="d-flex align-items-center" style="position: relative">
                                <div>
                                    <div class="media-tab-subtitle text-muted fs-sm mb-1">کلمه عبور</div>
                                    <h6 class="media-tab-title fs-md text-nowrap mb-0" id="password-{{$items->id}}">*****</h6>
                                </div>
                                <div class="button-area">
                                    <button class="btn btn-icon fav-btn show-code" data-type="0" type="button" data-id="{{$items->id}}" data-input="password" data-code="{{$Data['password']}}" data-bs-toggle="tooltip" title="" data-bs-original-title="مشاهده" aria-label="مشاهده"><i id="password-eye-{{$items->id}}" class="fa-light fa-eye text-info"></i></button>
                                    <button class="btn btn-icon fav-btn" onclick="myFunction('{{$Data['password']}}')" type="button" data-bs-toggle="tooltip" title="" data-bs-original-title="کپی کردن" aria-label="کپی"><i class="fa-regular fa-copy text-primary"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 col-12">
                        <div class="custom-nav completed">
                            <div class="d-flex align-items-center" style="position: relative">
                                <div>
                                    <div class="media-tab-subtitle text-muted fs-sm mb-1">سوال امنیتی 1:</div>
                                    <h6 class="media-tab-title fs-md text-nowrap mb-0" id="q1-{{$items->id}}">*****</h6>
                                </div>
                                <div class="button-area">
                                    <button class="btn btn-icon fav-btn show-code" data-type="0" type="button" data-id="{{$items->id}}" data-input="q1" data-code="{{$Data['q1']}}" data-bs-toggle="tooltip" title="" data-bs-original-title="مشاهده" aria-label="مشاهده"><i id="q1-eye-{{$items->id}}" class="fa-light fa-eye text-info"></i></button>
                                    <button class="btn btn-icon fav-btn" onclick="myFunction('{{$Data['q1']}}')" type="button" data-bs-toggle="tooltip" title="" data-bs-original-title="کپی کردن" aria-label="کپی"><i class="fa-regular fa-copy text-primary"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-12">
                        <div class="custom-nav completed">
                            <div class="d-flex align-items-center" style="position: relative">
                                <div>
                                    <div class="media-tab-subtitle text-muted fs-sm mb-1">سوال امنیتی 2:</div>
                                    <h6 class="media-tab-title fs-md text-nowrap mb-0" id="q2-{{$items->id}}">*****</h6>
                                </div>
                                <div class="button-area">
                                    <button class="btn btn-icon fav-btn show-code" data-type="0" type="button" data-id="{{$items->id}}" data-input="q2" data-code="{{$Data['q2']}}" data-bs-toggle="tooltip" title="" data-bs-original-title="مشاهده" aria-label="مشاهده"><i id="q2-eye-{{$items->id}}" class="fa-light fa-eye text-info"></i></button>
                                    <button class="btn btn-icon fav-btn" onclick="myFunction('{{$Data['q2']}}')" type="button" data-bs-toggle="tooltip" title="" data-bs-original-title="کپی کردن" aria-label="کپی"><i class="fa-regular fa-copy text-primary"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-12">
                        <div class="custom-nav completed">
                            <div class="d-flex align-items-center" style="position: relative">
                                <div>
                                    <div class="media-tab-subtitle text-muted fs-sm mb-1">سوال امنیتی 3:</div>
                                    <label class="media-tab-title fs-md text-nowrap mb-0" id="q3-{{$items->id}}">*****</label>
                                </div>
                                <div class="button-area">
                                    <button class="btn btn-icon fav-btn show-code" data-type="0" type="button" data-id="{{$items->id}}" data-input="q3" data-code="{{$Data['q3']}}" data-bs-toggle="tooltip" title="" data-bs-original-title="مشاهده" aria-label="مشاهده"><i id="q3-eye-{{$items->id}}" class="fa-light fa-eye text-info"></i></button>
                                    <button class="btn btn-icon fav-btn" onclick="myFunction('{{$Data['q3']}}')" type="button" data-bs-toggle="tooltip" title="" data-bs-original-title="کپی کردن" aria-label="کپی"><i class="fa-regular fa-copy text-primary"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    @if($Order->status == 'pending')
        <div class="alert alert-info d-flex fs-sm mb-4 mt-5" role="alert">
            <div class="alert-icon"><i class="ci-announcement"></i></div>
            <div> با تشکر از خرید شما، سفارش شما تا کمتر از 15 دقیقه دیگر انجام خواهد شد.</div>
        </div>
    @else
        <div class="alert alert-danger d-flex fs-sm mb-4 mt-5" role="alert">
            <div class="alert-icon"><i class="ci-announcement"></i></div>
            <div>این سفارش لغو شده است</div>
        </div>
    @endif
@endif
<script>
    function myFunction(id) {
        /* Copy the text inside the text field */
        navigator.clipboard.writeText(id);
        /* Alert the copied text */
        toastr.success("عملیات کپی با موفقیت انجام شد.", "اطلاعیه", {
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
        })
    }
</script>
