@php
    $AppleIds = \App\Models\AppleId::where('order_id',$Order->id)->where('status',1)->get();
    $Decrypt = new \App\lib\PhpEncrypt();
@endphp

<div class="card card-flush py-4 flex-row-fluid overflow-hidden">
    <div class="card-header">
        <div class="card-title">
            <h2>شماره سفارش {{$Order->id}}</h2>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="d-flex align-items-center bg-light-warning rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                        class="fa-regular fa-typewriter"></i></span>
                    <div class="flex-grow-1 me-2">
                        <label class="fw-bolder text-gray-800 text-hover-primary fs-6">نوع سفارش</label>
                        <span class="text-muted fw-bold d-block">سفارش اپل آیدی</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="d-flex align-items-center bg-light-success rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                        class="fa-regular fa-typewriter"></i></span>
                    <div class="flex-grow-1 me-2">
                        <label class="fw-bolder text-gray-800 text-hover-primary fs-6">مبلغ سفارش</label>
                        <span class="text-muted fw-bold d-block">{{number_format($Order->price)}} تومان </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-12">
                <div class="d-flex align-items-center bg-light-danger rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                        class="fa-regular fa-typewriter"></i></span>
                    <div class="flex-grow-1 me-2">
                        <label class="fw-bolder text-gray-800 text-hover-primary fs-6">وضعیت سفارش</label>
                        <span class="text-muted fw-bold d-block">{{AdminOrderStatus($Order->status)}}</span>
                    </div>
                </div>
            </div>
            <hr>
        </div>
    </div>
    <div class="card-header">
        <div class="card-title">
            <h2>اطلاعات ارسال شده توسط کاربر</h2>
        </div>
    </div>
    <div class="card-body pt-0">
        @php  $Data = json_decode($OrderItems->first()->detail,true); @endphp
        <div class="col-12">
            <div class="row">
                <div class="col-lg-3">
                    <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                    <span class="svg-icon svg-icon-warning me-5"><i
                                            class="fa-regular fa-typewriter"></i></span>
                        <div class="flex-grow-1 me-2">
                            <label class="fw-bolder text-gray-800 text-hover-primary fs-6">نام</label>
                            <span class="text-muted fw-bold d-block">{{$Data['name']}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                    <span class="svg-icon svg-icon-warning me-5"><i
                                            class="fa-regular fa-typewriter"></i></span>
                        <div class="flex-grow-1 me-2">
                            <label class="fw-bolder text-gray-800 text-hover-primary fs-6">نام خانوادگی</label>
                            <span class="text-muted fw-bold d-block">{{$Data['family']}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                        <span class="svg-icon svg-icon-warning me-5"><i class="fa-regular fa-typewriter"></i></span>
                        <div class="flex-grow-1 me-2">
                            <label class="fw-bolder text-gray-800 text-hover-primary fs-6">ایمیل</label>
                            <span class="text-muted fw-bold d-block">{{$Data['email']}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                        <span class="svg-icon svg-icon-warning me-5"><i class="fa-regular fa-typewriter"></i></span>
                        <div class="flex-grow-1 me-2">
                            <label class="fw-bolder text-gray-800 text-hover-primary fs-6">تاریخ تولد</label>
                            <span class="text-muted fw-bold d-block">{{$Data['b_day']}}</span>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
        </div>
    </div>
    <div class="card-header">
        <div class="card-title">
            <h2>اطلاعات اپل آیدی</h2>
        </div>
    </div>
    <div class="card-body pt-0">
        @if(count($AppleIds) != 0)
            @foreach($AppleIds as $items)
                <div class="card border-0 shadow-lg mt-3">
                    <div class="card-header">
                        <h4>جزئیات سفارش:</h4>
                    </div>
                    <div class="card-body pb-2">
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
        @endif
    </div>
</div>
