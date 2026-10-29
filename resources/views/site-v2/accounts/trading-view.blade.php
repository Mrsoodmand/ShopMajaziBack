<form action="{{route('submit-factor',3)}}" id="َAddToCartForm" method="post">
    <div class="rounded p-3 mt-2 mb-2">
        <div class="d-flex align-items-center">
            <div class="ps-2 w-100">
                <span class="fs-ms text-muted">نوع پلن*</span>
                <div class="d-flex">
                    <div class="row">
                        @foreach($Plans as $key => $Plan)
                            @php
                                $price = $perUser * $Plan['count'];
                                $price = CovertVirtualNumberPrice(number_format($price * $Currency));
                                $Discount = $Plan['discount'];
                                $DisValue = number_format($Plan['discount'] * $Currency);
                                 if($key == 0){
                                     $showPrice = $price;
                                     $Name = $Plan['name'];
                                 }
                            @endphp
                            <div class="col-lg-6 col-12">
                                <div class="form-check form-option form-check-justified mb-2">
                                    <input class="form-check-input"
                                           value="{{Encode($Plan['id'])}}" type="radio"
                                           name="plane_id"
                                           id="plan-{{$Plan['id']}}" {{$key == 0 ? 'checked="checked"': '' }}>
                                    <label class="form-option-label"
                                           onclick="ChangeName('{{$price}}','{{$Plan['name']}}','{{!is_null($Plan['discount']) ? '1' :'0'}}','{{!is_null($Plan['discount']) ? $DisValue :'0'}}')"
                                           for="plan-{{$Plan['id']}}">{{$Plan['name']}}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3 mt-3 text-center">
        <span class="text-heading fw-medium me-1">نوع اپل آیدی:</span>
        <span class="" style="color: #ef476f" id="ProductName"> اپل آیدی آماده</span><br>
        <span id="SelectedFirstOption">مبلغ:</span>
        <span class="ProductPrice" style="color: #ef476f"
              id="ProductPrice"></span>
        <span> تومان</span>
    </div>
    @csrf
    <button data-form="َAddToCartForm" id="AddToCartBtn" data-text="خرید و ثبت سفارش"
            class="submit-form btn btn-primary btn-shadow d-block w-100 mt-4 mb-4"
            type="submit">
        <i class="ci-cart fs-lg me-2"></i>ثبت سفارش و خرید
    </button>
</form>
<table>
    <tbody>
    <tr>
        <th> 🛒نام سرویس:</th>
        <td>buy Apple Id</td>
    </tr>
    <tr>
        <th>💲قیمت:</th>
        <td>از 30,000 هزار تومان</td>
    </tr>
    <tr>
        <th>زمان تحویل</th>
        <td> کمتر از 10 دقیقه</td>
    </tr>
    <tr>
        <th>فضای آیکلود</th>
        <td>5 گیگابایت</td>
    </tr>
    </tbody>
</table>
