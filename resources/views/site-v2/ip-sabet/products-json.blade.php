@php $type = 1; @endphp
@if($type == 1)
    @if(\Jenssegers\Agent\Facades\Agent::isPhone())
        @foreach($Products as $product)
            @php
                $Data = json_decode($product->detail,true);
            @endphp
            <div class="col-md-4 col-sm-4 col-6 service-search box-area" data-id="{{$product->id}}" style="position: relative;">
                <div class="bg-white d-block country-item shadow-sm rounded-3 py-sm-4 mb-grid-gutter buy-service" data-id="{{$product->id}}" style="padding-bottom: 1rem !important;">
                    <div class="card product-card-alt buy-service service-id-{{$product->id}}">
                        <div class="product-thumb" style="position: relative!important;width: auto!important;height: auto!important;">
                            <img style="width: auto!important;height: auto!important;" src="{{secure_url($Data['image'])}}" alt="{{$product->faName}}">
                        </div>
                        <div class="card-body plans plan-{{$product->id}}" style="text-align: center;padding-bottom: 0px!important;">
                            <h3 class="product-title fs-sm mb-2 text-center">{{$product->faName}} با <br>{{$Data['volume']}} حجم </h3>
                            <div class="d-flex flex-wrap justify-content-center">
                                @if($Data['dis'] == 0)
                                    <div class="bg-faded-accent text-accent rounded-1 py-1 px-2 mb-1" style="font-size: 12px;opacity: 0">........</div>
                                    <div class="bg-faded-accent text-accent rounded-1 py-1 px-2" style="font-size: 12px">{{number_format($product->price)}} تومان</div>
                                @else
                                    <del class="bg-faded-accent text-accent rounded-1 py-1 px-2 mb-1" style="font-size: 12px">{{number_format($product->price)}} تومان</del>
                                    <div class="bg-faded-accent text-accent rounded-1 py-1 px-2" style="font-size: 12px">{{number_format($Data['dis'])}} تومان </div>
                                @endif
                            </div>
                            <button class="btn btn-sm btn-info text-white" style="margin-top: 10px"
                                    data-service="{{$product->id}}" type="button" data-bs-toggle="tooltip"
                                    title="انتخاب">انتخاب
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        @foreach($Products as $product)
            @php
                $Data = json_decode($product->detail,true);
            @endphp
            <div class="col-md-3 col-sm-4 col-12 service-search box-area " style="position: relative;margin-bottom: -10px;cursor: pointer">
                <div class="d-block d-sm-flex align-items-start text-sm-start buy-service service-id-{{$product->id}} shadow-sm" data-id="{{$product->id}}">
                    <div class="d-block flex-shrink-0" style="width: 100px;height: 100px">
                        <img class="rounded" src="{{secure_url($Data['image'])}}" alt="{{$product->faName}}">
                    </div>
                    <div class="pt-2 ms-2">
                        <h3 class="product-title fs-base mb-2">{{$product->faName}}</h3>
                        @if($Data['dis'] != 0)
                            <span class="discount-badge badge bg-danger badge-shadow align-middle mt-n2">{{$Data['percent']}}% تخفیف </span>
                        @endif

                        <div class="fs-sm"><span class="text-muted me-2">حجم:</span>{{$Data['volume']}} گیگ </div>
                        @if($Data['dis'] == 0)
                            <div class="fs-lg text-accent pt-2">{{number_format($product->price)}} تومان</div>
                        @else
                            <del class="fs-sm text-accent">{{number_format($product->price)}} تومان</del>
                            <div class="fs-lg text-accent">{{number_format($Data['dis'])}} تومان</div>
                        @endif
                    </div>
                </div>
                <button class="btn btn-icon btn-info me-2 btn-sm w-100 mt-1 buy-service" data-id="{{$product->id}}" type="button" data-bs-toggle="tooltip" title="انتخاب"><i class="fa-regular fa-cart-shopping me-1"></i>انتخاب</button>
            </div>
        @endforeach
    @endif

@else
@if(\Jenssegers\Agent\Facades\Agent::isPhone())
    @foreach($Products as $product)
        @php
            $Data = json_decode($product->detail,true);
        @endphp
        <div class="col-md-4 col-sm-4 col-6 service-search box-area" data-id="{{$product->id}}" style="position: relative;">
            <div class="bg-white d-block country-item shadow-lg rounded-3 py-3 py-sm-4 mb-grid-gutter buy-service" data-id="{{$product->id}}">
                <div class="card product-card-alt">
                    <div class="product-thumb" style="position: relative!important;width: auto!important;height: auto!important;">
                        <img style="width: auto!important;height: auto!important;" src="{{secure_url($Data['image'])}}" alt="{{$product->faName}}">
                    </div>
                    <div class="card-body" style="text-align: center;padding-bottom: 0px!important;">
                        <label class="product-title fs-sm mb-2 text-center">{{$product->faName}} با <br>{{$Data['volume']}} حجم </label>
                        <div class="d-flex flex-wrap justify-content-center">
                            @if($Data['dis'] == 0)
                                <div class="bg-faded-accent text-accent rounded-1 py-1 px-2 mb-1" style="font-size: 12px;opacity: 0">.</div>
                                <div class="bg-faded-accent text-accent rounded-1 py-1 px-2" style="font-size: 12px">{{number_format($product->price)}} تومان</div>
                            @else
                                <del class="bg-faded-accent text-accent rounded-1 py-1 px-2 mb-1" style="font-size: 12px">{{number_format($product->price)}} تومان</del>
                                <div class="bg-faded-accent text-accent rounded-1 py-1 px-2" style="font-size: 12px">{{number_format($Data['dis'])}} تومان </div>
                            @endif
                        </div>
                        <button class="btn btn-sm btn-info text-white" style="margin-top: 10px"
                                data-service="{{$product->id}}" type="button" data-bs-toggle="tooltip"
                                title="انتخاب">انتخاب
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    @foreach($Products as $product)
        @php
            $Data = json_decode($product->detail,true);
        @endphp
        <div class="col-lg-3 col-md-4 col-sm-6 px-2 mb-grid-gutter buy-service"
             data-id="{{$product->id}}">
            <div class="card product-card-alt">
                <div class="product-thumb">
                    <img src="{{secure_url($Data['image'])}}"  alt="{{$product->faName}}">
                </div>
                <div class="card-body plans plan-{{$product->id}}">
                    <h3 class="product-title fs-sm mb-2">{{$product->faName}}</h3>
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <div class="fs-sm me-2">حجم: {{$Data['volume']}} گیگ</div>
                        @if($Data['dis'] == 0)
                            <div class="bg-faded-accent text-accent rounded-1 py-1 px-2">{{number_format($product->price)}} تومان</div>
                        @else
                            <div class="bg-faded-accent text-accent rounded-1 py-1 px-2">{{number_format($Data['dis'])}} تومان </div>
                        @endif
                    </div>
                    <button class="btn btn-sm btn-info text-white w-100" style="margin-top: 10px"
                            data-service="{{$product->id}}" type="button" data-bs-toggle="tooltip"
                            title="انتخاب">انتخاب
                    </button>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endif
