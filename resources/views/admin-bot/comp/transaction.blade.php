<div class="table-responsive">
    <table class="table table-row-bordered  table-hover table-row-gray-100 align-middle gs-0 gy-3">
        <thead>
        <tr class="fw-bold text-muted">
            <th class="text-center">شناسه</th>
            <th class="text-center">کاربر</th>
            <th class="text-center">نوع تراکنش</th>
            <th class="text-center">نوع پرداخت</th>
            <th class="text-center">مبلغ</th>
            <th class="text-center">وضعیت</th>
            <th class="text-center">تاریخ ثبت</th>
            <th class="text-center">تاریخ پرداخت</th>
            <th class="text-center">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @if(count($list) != 0)
            @foreach($list as $item)
                <tr>
                    <td class="text-center">
                        {{$item->id}}
                    </td>
                    <td class="text-center">
                        @if(!is_null($item->User))
                            <a href="{{route('admin.bot-user-single',$item->user_id)}}"
                               class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{$item->User->username}}</a>
                            <span class="text-muted fw-semibold text-muted d-block fs-7">{{$item->User->user_id}}</span>
                        @else
                            <a href="#" class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">کاربری یافت
                                نشد</a>
                            <span class="text-muted fw-semibold text-muted d-block fs-7">کاربری یافت نشد</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{__('bot.paymentType.'.$item->type)}}
                    </td>
                    <td class="text-center">
                        {{__('bot.paymentMethod.'.$item->method)}}
                    </td>
                    <td class="text-center">
                        {{number_format($item->price)}} تومان
                    </td>
                    <td class="text-center">
                        {{BotPaymentStatus($item->status)}}
                    </td>
                    <td class="text-center">
                        <label class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('Y-m-d',strtotime($item->created_at))}}</label>
                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{$jdf->jdate('H:i',strtotime($item->created_at))}}</span>
                    </td>
                    <td class="text-center">
                        @if(!is_null($item->paid_at))
                            <label class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('Y-m-d',strtotime($item->paid_at))}}</label>
                            <span class="text-muted fw-semibold text-muted d-block fs-7">{{$jdf->jdate('H:i',strtotime($item->paid_at))}}</span>
                        @else
                            --
                        @endif
                    </td>
                    <td class="text-center">
                        <button data-id="{{$item->id}}" type="button"
                           class="btn btn-bg-light check-payment btn-active-color-primary btn-sm ">
                            مشاهده جزئیات
                        </button>
                    </td>
                </tr>
            @endforeach

        @else
            <tr>
                <td colspan="10" class="text-center">
                    سفارشای یافت نشد
                </td>

            </tr>
        @endif
        </tbody>
    </table>
</div>


<div class="modal fade" id="PaymentModal" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none">
									<rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                          transform="rotate(-45 6 17.3137)" fill="currentColor"></rect>
									<rect x="7.41422" y="6" width="16" height="2" rx="1"
                                          transform="rotate(45 7.41422 6)" fill="currentColor"></rect>
								</svg>
							</span>
                </div>
            </div>
            <div class="modal-body scroll-y pt-0 pb-15" id="PaymentDetail">
            </div>
        </div>
    </div>
</div>



