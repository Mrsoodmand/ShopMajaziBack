@if(\Jenssegers\Agent\Facades\Agent::isPhone())
    @for($i = 0;$i <= 5;$i++)
        <div class="col-md-4 col-sm-4 col-12 service-search box-area"
             style="position: relative;margin-bottom: -10px">
            <div
                class="bg-white d-block country-item shadow-lg rounded-3 py-3 py-sm-4 mb-grid-gutter buy-volume volume-area"
                data-id="{{$i}}">
                <div class="number-text">
                    <span class="volume-name">@lang('order.Volume-Name.'.$i)</span>
                    <div>
                        @if($i == 0)
                            <span class="price-area-2">{{number_format($product->price)}} تومان </span>
                        @else
                            @php $Price = __('order.Volume-Gb.'.$i) * 1500 @endphp
                            <span class="price-area-2">  {{number_format($Price)}} تومان</span>
                        @endif
                    </div>
                </div>
                <button class="btn btn-icon me-2 bg-info service-buy-btn"
                        data-service="{{$product->id}}" type="button" data-bs-toggle="tooltip"
                        title="خرید"><i class="fa-regular fa-cart-shopping me-1"></i>انتخاب
                </button>
            </div>
        </div>
    @endfor
@else
    @for($i = 0;$i <= 5;$i++)
        <div class="col-md-3 col-sm-4 col-12 box-area" style="position: relative;margin-bottom: -10px">
            <div
                class="bg-white d-block country-item shadow-lg rounded-3 py-3 py-sm-4 mb-grid-gutter buy-volume volume-area"
                data-id="{{$i}}">
                <span class="volume-name">@lang('order.Volume-Name.'.$i)</span>
                <button class="btn btn-icon bg-info me-2 service-buy-btn-v2"
                        data-service="{{$i}}" type="button" data-bs-toggle="tooltip"
                        title="انتخاب"><i class="fa-regular fa-cart-shopping me-1"></i>انتخاب
                </button>
                @if($i == 0)

                @elseif($i == 6)
                    <label class="btn btn-icon service-fav-btn">
                        <span>1 گیگ = 1,500 تومان</span>
                    </label>
                @else
                    <label class="btn btn-icon service-fav-btn">
                        @php $Price = __('order.Volume-Gb.'.$i) * 1500 @endphp
                        <span>{{number_format($Price)}} تومان </span>
                    </label>
                @endif
            </div>
        </div>
    @endfor
@endif
