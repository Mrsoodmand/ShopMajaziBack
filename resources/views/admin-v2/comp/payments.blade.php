@php $jdf = new \App\lib\Jdf(); @endphp
<div class="table-responsive">
    <table class="table align-middle gs-0 gy-4">
        <thead>
        <tr class="fw-bolder text-muted bg-light">
            <th class="ps-4 text-center">شماره تراکنش</th>
            <th class="min-w-150px">کاربر</th>
            <th class="min-w-150px">نوع تراکنش</th>
            <th class="min-w-150px">نوع پرداخت</th>
            <th class="min-w-150px">مبلغ</th>
            <th class="min-w-150px">وضعیت</th>
            <th class="min-w-125px">تاریخ ثبت</th>
            <th class="min-w-125px">تاریخ پرداخت</th>
            <th class="text-end rounded-end" style="padding-left: 20px!important;">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @if(count($Payments) != 0)
            @foreach($Payments as $payment)
                <tr>
                    <td class="ps-4">
                        {{$payment->id}}
                    </td>
                    <td>
                        @if(!is_null($payment->User))
                        <div class="d-flex align-items-center">
                            <div class="d-flex justify-content-start flex-column">
                                <a href="{{url('/admin/users/'.$payment->user_id)}}" target="_blank"
                                   class="text-dark fw-bolder text-hover-primary mb-1 fs-6">{{$payment->User->name}}</a>
                                <span
                                    class="text-muted fw-bold text-muted d-block fs-7">{{$payment->User->mobile}}</span>
                            </div>
                        </div>
                        @else
                            <div class="d-flex align-items-center">
                                <div class="d-flex justify-content-start flex-column">
                                    <a href="#"
                                       class="text-dark fw-bolder text-hover-primary mb-1 fs-6">نام کاربر ثبت نشده است</a>
                                </div>
                            </div>
                        @endif
                    </td>
                    <td>
                        <a href="javascript:;"
                           class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">@lang('payments.type.'.$payment->type)</a>
                    </td>
                    <td>
                        <a href="javascript:;"
                           class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">@if($payment->gate == 'Wallet')
                                کیف پول
                            @elseif($payment->gate == 'Bank')
                                بانک
                            @else
                                ---
                            @endif
                        </a>
                    </td>
                    <td>
                        <a href="javascript:;"
                           class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{number_format($payment->price)}}</a>
                        <span class="text-muted fw-bold d-block fs-7">تومان</span>
                    </td>
                    <td>
                        {{AdminOrderPayStatus($payment->status)}}
                    </td>
                    <td>
                        <label
                            class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('d F Y',strtotime($payment->created_at))}}</label>
                        <span
                            class="text-muted fw-bold d-block fs-7">{{$jdf->jdate('H:i:s',strtotime($payment->created_at))}}</span>
                    </td>
                    <td>
                        @if(!is_null($payment->paid_at))
                            <label
                                class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('d F Y',strtotime($payment->paid_at))}}</label>
                            <span
                                class="text-muted fw-bold d-block fs-7">{{$jdf->jdate('H:i:s',strtotime($payment->paid_at))}}</span>
                        @else
                            ---
                        @endif
                    </td>
                    <td class="text-end" style="padding-left: 20px!important;">
                        <a href="javascript:;" data-payment="{{$payment->id}}"
                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm check-payment">
                            <i class="fa-regular fa-circle-info"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="9">
                    <a href="javascript:;"
                       class="text-dark text-center fw-bolder text-hover-primary d-block mb-1 fs-6">در حال حاضر همه چی مرتبه!</a>
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
<script>

</script>
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

