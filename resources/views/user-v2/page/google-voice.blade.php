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
@if(!is_null($GoogleVoice))
        <div class="card border-0 shadow-lg mt-3">
            <div class="card-header">
                <h4>جزئیات سفارش:</h4>
            </div>
            <div class="card-body pb-2">
                @php
                    $Data = json_decode($Decrypt->decrypt($GoogleVoice->serial,$GoogleVoice->code),true);
                @endphp
                <div class="row">
                    <div class="col-lg-4 col-12">
                        <div class="custom-nav completed">
                            <div class="d-flex align-items-center" style="position: relative">
                                <div>
                                    <div class="media-tab-subtitle text-muted fs-sm mb-1">اپل آیدی</div>
                                    <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$Data['number']}}</h6>
                                </div>
                                <div class="button-area">
                                    <button class="btn btn-icon fav-btn" onclick="myFunction('{{$Data['number']}}')" type="button" data-bs-toggle="tooltip" title="" data-bs-original-title="کپی کردن" aria-label="کپی"><i class="fa-regular fa-copy text-primary"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="custom-nav completed">
                            <div class="d-flex align-items-center" style="position: relative">
                                <div>
                                    <div class="media-tab-subtitle text-muted fs-sm mb-1">شماره گوگل ویس</div>
                                    <label class="media-tab-title fs-md text-nowrap mb-0">
                                        <span id="email-{{$GoogleVoice->id}}">*****</span>
                                    </label>
                                </div>
                                <div class="button-area">
                                    <button class="btn btn-icon fav-btn show-code" data-type="0" type="button" data-id="{{$GoogleVoice->id}}" data-input="email" data-code="{{$Data['email']}}" data-bs-original-title="مشاهده" data-bs-toggle="tooltip" aria-label="مشاهده"><i id="email-eye-{{$GoogleVoice->id}}" class="fa-light fa-eye text-info"></i></button>
                                    <button class="btn btn-icon fav-btn" onclick="myFunction('{{$Data['email']}}')" type="button" data-bs-toggle="tooltip" title="" data-bs-original-title="کپی کردن" aria-label="کپی"><i class="fa-regular fa-copy text-primary"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="custom-nav completed">
                            <div class="d-flex align-items-center" style="position: relative">
                                <div>
                                    <div class="media-tab-subtitle text-muted fs-sm mb-1">کلمه عبور</div>
                                    <h6 class="media-tab-title fs-md text-nowrap mb-0" id="password-{{$GoogleVoice->id}}">*****</h6>
                                </div>
                                <div class="button-area">
                                    <button class="btn btn-icon fav-btn show-code" data-type="0" type="button" data-id="{{$GoogleVoice->id}}" data-input="password" data-code="{{$Data['password']}}" data-bs-toggle="tooltip" title="" data-bs-original-title="مشاهده" aria-label="مشاهده"><i id="password-eye-{{$GoogleVoice->id}}" class="fa-light fa-eye text-info"></i></button>
                                    <button class="btn btn-icon fav-btn" onclick="myFunction('{{$Data['password']}}')" type="button" data-bs-toggle="tooltip" title="" data-bs-original-title="کپی کردن" aria-label="کپی"><i class="fa-regular fa-copy text-primary"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-12">
                        <div class="custom-nav completed">
                            <div class="d-flex align-items-center" style="position: relative">
                                <div>
                                    <div class="media-tab-subtitle text-muted fs-sm mb-1">ایمیل ریکاوری:</div>
                                    <h6 class="media-tab-title fs-md text-nowrap mb-0" id="backup-email-{{$GoogleVoice->id}}">*****</h6>
                                </div>
                                <div class="button-area">
                                    <button class="btn btn-icon fav-btn show-code" data-type="0" type="button" data-id="{{$GoogleVoice->id}}" data-input="backup-email" data-code="{{$Data['backup-email']}}" data-bs-toggle="tooltip" title="" data-bs-original-title="مشاهده" aria-label="مشاهده"><i id="backup-email-eye-{{$GoogleVoice->id}}" class="fa-light fa-eye text-info"></i></button>
                                    <button class="btn btn-icon fav-btn" onclick="myFunction('{{$Data['backup-email']}}')" type="button" data-bs-toggle="tooltip" title="" data-bs-original-title="کپی کردن" aria-label="کپی"><i class="fa-regular fa-copy text-primary"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 col-12">
                        <div class="custom-nav completed">
                            <div class="d-flex align-items-center" style="position: relative">
                                <div>
                                    <div class="media-tab-subtitle text-muted fs-sm mb-1">کد دومرحله ای:</div>
                                    <h6 class="media-tab-title fs-md text-nowrap mb-0" id="2fa-{{$GoogleVoice->id}}">*****</h6>
                                </div>
                                <div class="button-area">
                                    <button class="btn btn-icon fav-btn show-code" data-type="0" type="button" data-id="{{$GoogleVoice->id}}" data-input="2fa" data-code="{{$Data['2fa']}}" data-bs-toggle="tooltip" title="" data-bs-original-title="مشاهده" aria-label="مشاهده"><i id="2fa-eye-{{$GoogleVoice->id}}" class="fa-light fa-eye text-info"></i></button>
                                    <button class="btn btn-icon fav-btn" onclick="myFunction('{{$Data['2fa']}}')" type="button" data-bs-toggle="tooltip" title="" data-bs-original-title="کپی کردن" aria-label="کپی"><i class="fa-regular fa-copy text-primary"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@else
    @if($Order->status == 'pending')
        <div class="alert alert-info d-flex fs-sm mb-4 mt-5" role="alert">
            <div class="alert-icon"><i class="ci-announcement"></i></div>
            <div> با تشکر از خرید شما، به زودی اطلاعات اپل آیدی برای شما ارسال خواهد شد.</div>
        </div>
    @else
        <div class="alert alert-danger d-flex fs-sm mb-4 mt-5" role="alert">
            <div class="alert-icon"><i class="ci-announcement"></i></div>
            <div>این سفارش لغو شده است.</div>
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
