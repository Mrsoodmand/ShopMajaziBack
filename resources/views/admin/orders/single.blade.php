@php
    $total = 0;
    $Title = 'جزئیات سفارش' @endphp
@extends('admin.layout.master',['title'=>$Title])

@section('content')


    <div class="content-body" style="min-height: 1100px;">
        <div class="container-fluid">
            <div class="page-titles">
                <h4>فاکتور</h4>
            </div>
            @if($Order->status == 'failed' && $Order->detail != null)
                <div class="alert alert-danger alert-dismissible fade show">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                         stroke-linecap="round" stroke-linejoin="round" class="ml-2">
                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2">
                        </polygon>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    {{$Order->detail}}
                </div>
            @endif
            @if(\Illuminate\Support\Facades\Session::has('error'))
                @foreach(\Illuminate\Support\Facades\Session::get('error') as $error)
                    <div class="alert alert-danger alert-dismissible fade show">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                             fill="none"
                             stroke-linecap="round" stroke-linejoin="round" class="ml-2">
                            <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2">
                            </polygon>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        {{$error}}
                    </div>
                @endforeach
            @endif
            @if(\Illuminate\Support\Facades\Session::has('success'))

                <div class="alert alert-success alert-dismissible fade show">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                         stroke-linecap="round" stroke-linejoin="round" class="ml-2">
                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2">
                        </polygon>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    {{\Illuminate\Support\Facades\Session::get('success')}}
                </div>

            @endif


            <div class="default-tab">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#home"><i class="la la-home ml-2"></i> فاکتور
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#profile"><i class="la la-user ml-2"></i> تاریخچه
                            تغییر وضعیت </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " data-toggle="tab" href="#contact"><i class="la la-phone ml-2"></i> چت
                            <span class="badge light badge-success">{{$CountOrderChat}}</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active show" id="home" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-12">

                                <div class="card mt-3">
                                    <div class="card-header"> فاکتور شماره : {{$Order->id}}
                                        <strong> {{$Jdf->jdate(' تاریخ : Y/m/d  ساعت : H:i:s' , strtotime($Order->created_at))}} </strong>
                                        <span class="float-right">
							<button type="button" class="btn light btn-warning btn-sm" data-toggle="modal"
                                    data-target="#exampleModalCenter"> تغییر وضعیت </button>
                                @if($Order->status == 'failed')
                                                <a href="{{route('admin.return back order money to user',[Encode($Order->id)])}}"
                                                   class="btn light btn-danger btn-sm"> برگشت وجه </a>
                                            @endif
                            </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-5">
                                            <div class="mt-3 col-xl-4 col-lg-3 col-md-6 col-sm-12">
                                                <h6>اطلاعات کاربر:</h6>
                                                @if($Order->user_type == 'guest')
                                                    <div><strong> کاربر مهمان </strong></div>
                                                    <div> {{$Order->user_id}} </div>
                                                @else
                                                    <div><strong> {{$Order->User->name}} </strong></div>
                                                    <div> {{$Order->User->mobile}} </div>
                                                    <div> موجودی کیف پول : {{number_format($Order->User->wallet) }}تومان    </div>
                                                        <div> تاریخ عضویت
                                                            : {{$Jdf->jdate('d / F / Y',strtotime($Order->User->created_at))}}</div>
                                                        @endif
                                            </div>
                                            <div class="mt-3 col-xl-4 col-lg-3 col-md-6 col-sm-12">
                                                <h6>اطلاعات سفارش:</h6>
                                                <div><strong>شماره سفارش : {{$Order->id}}</strong></div>
                                                <div> تاریخ ایجاد
                                                    : {{$Jdf->jdate(' تاریخ : Y/m/d  ساعت : H:i:s' , strtotime($Order->created_at))}} </div>
                                                <div> وضعیت سفارش : {{AdminOrderStatus($Order->status) }} </div>
                                            </div>
                                            <div class="mt-3 col-xl-4 col-lg-3 col-md-6 col-sm-12">
                                                <h6>اطلاعات پرداخت:</h6>
                                                <div><strong>شماره پرداخت : {{$Payment->id}}</strong></div>
                                                <div>وضعیت پرداخت : {{AdminOrderPayStatus($Order->pay_status) }} </div>
                                                <div> تاریخ پرداخت
                                                    : {{$Jdf->jdate(' تاریخ : Y/m/d  ساعت : H:i:s' , strtotime($Payment->updated_at))}} </div>
                                                <div> شماره پیگیری : {{$Payment->refid}} </div>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th class="center">#</th>
                                                    <th>ایتم</th>
                                                    <th>توضیحات</th>
                                                    <th class="right">هزینه واحد</th>
                                                    <th class="right">کل</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($OrderItems as $key => $item)
                                                    @if($item->type == 'Virtual-Number')
                                                        @php
                                                            $Product = $item->Product;
                                                            $ProductDetail = json_decode($Product->detail,true);
                                                            $OrderDetail = json_decode($item->detail,true);
                $total += $item->price
                                                        @endphp
                                                        <tr>
                                                            <td class="center">1</td>
                                                            <td class="left strong"> {{$ProductDetail['Info']['faName']}}</td>
                                                            <td class="left">شماره : {{$OrderDetail['number']}} <br>
                                                                -- کد کشور : {{$OrderDetail['country_code']}} <br>
                                                                -- کد تاییدیه : {{$OrderDetail['Sms']}} <br>

                                                            </td>
                                                            <td class="right">{{number_format($item->price)}} تومان</td>
                                                            <td class="right">{{number_format($item->price)}} تومان</td>
                                                        </tr>
                                                    @endif
                                                    @if($item->type == 'social-services')
                                                        @php
                                                            $PlansDetail = json_decode($item->detail,true);
                                                            $Options = $PlansDetail['Options'];
                                                            $PlansDetail = $PlansDetail['PlansDetail'];
                                                            unset($PlansDetail['price']);
                                                            unset($PlansDetail['discount']);
                                                            $Options = json_decode($Options,true);
                                                            $total += $item->price
                                                        @endphp
                                                        <td class="center">{{$key+1}}</td>
                                                        <td class="left strong"> {{$PlansDetail['name']}}</td>
                                                        <td class="left">

                                                            @foreach($Options as $option)
                                                                {{$option['filed_type']}} :   {{$option['filed_value']}}
                                                            @endforeach

                                                        </td>
                                                        <td class="right">{{number_format($item->price)}} تومان</td>
                                                        <td class="right">{{number_format($item->price)}} تومان</td>
                                                        </tr>


                                                    @endif
                                                    @if($item->type == 'sim card charge')
                                                        @php
                                                            $PlansDetail = json_decode($item->detail,true);
                                                            $Options = $PlansDetail;

                                                            $total += $item->price
                                                        @endphp
                                                        <td class="center">{{$key+1}}</td>
                                                        <td class="left strong"> خرید شارژ</td>
                                                        <td class="left">

                                                            @foreach($Options as $key => $option)
                                                                @if($key == 'operator')
                                                                    اپراتور : @lang('order.operator.'.$option)
                                                                @elseif($key == 'charge_type')
                                                                    نوع شارژ : @lang('order.charge type.'.$option)
                                                                @else
                                                                    شماره تلفن :        {{$option}}
                                                                @endif
                                                                <br>
                                                            @endforeach

                                                        </td>
                                                        <td class="right">{{number_format($item->price)}} تومان</td>
                                                        <td class="right">{{number_format($item->price)}} تومان</td>
                                                        </tr>


                                                    @endif
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-4 col-sm-5"></div>
                                            <div class="col-lg-4 col-sm-5 mr-auto">
                                                <table class="table table-clear">
                                                    <tbody>
                                                    <tr>
                                                        <td class="left"><strong>کل</strong></td>
                                                        <td class="right">
                                                            <strong>{{number_format($total)}} تومان</strong>
                                                            <br>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="profile">

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card  mt-3">
                                    <div class="card-header d-sm-flex d-block border-0 pb-0">
                                        <div>
                                            <h4 class="fs-20 text-black"> تاریخچه تغییر وضعیت ها </h4>
                                            <span class="fs-12"></span>
                                        </div>
                                    </div>
                                    <div class="card-body pb-0">
                                        <div id="accordion-seven"
                                             class="accordion accordion-header-bg accordion-bordered">

                                            @if($Order->admin_detail != null)
                                                @php
                                                    $Details = json_decode($Order->admin_detail ,true)
                                                @endphp
                                                @foreach($Details as $key => $detail)
                                                    <div class="accordion__item">
                                                        <div
                                                                class="accordion__header accordion__header--primary collapsed"
                                                                data-toggle="collapse" data-target="#status-{{$key}}"
                                                                aria-expanded="false">
                                                            <span class="accordion__header--icon"></span>
                                                            <span class="accordion__header--text"> تغییر وضعیت از {{AdminOrderStatus($detail['old_status'])}} به {{AdminOrderStatus($detail['new_status'])}} </span>
                                                            <span class="accordion__header--indicator"></span>
                                                        </div>
                                                        <div id="status-{{$key}}" class="accordion__body collapse"
                                                             data-parent="#accordion-seven" style="">
                                                            <div class="accordion__body--text">
                                                                {{$detail['detail']}}
                                                                <hr>
                                                                توسط {{\App\Admin::find($detail['changed_by'])->full_name}}
                                                                در
                                                                تاریخ {{$Jdf->jdate('Y/m/d در ساعت: H:i:s' ,strtotime($detail['changed_date']))}}
                                                            </div>
                                                        </div>
                                                    </div>

                                                @endforeach
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="tab-pane fade " id="contact">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="chatbox active card"
                                     style="z-index: 0!important;display: block!important;position: relative!important;width: 100%!important;height: auto!important;">
                                    <div class="custom-tab-1">
                                        <div class="tab-content">
                                            <div class="tab-pane fade active show" id="chat" role="tabpanel">
                                                <div class="card chat dz-chat-history-box">
                                                    <div class="card-header chat-list-header text-center">
                                                        <div>
                                                            <h6 class="mb-1">گفت و گو با کاربر</h6>
                                                        </div>
                                                    </div>
                                                    <div class="card-body msg_card_body dz-scroll height380"
                                                         id="DZ_W_Contacts_Body3"
                                                         style="overflow:scroll; height: 500px!important;">
                                                        @foreach($OrderChats as $chat)
                                                            @if($chat->type == 'client')

                                                                <div class="d-flex justify-content-start mb-4">
                                                                    <div class="img_cont_msg">
                                                                        <img
                                                                                src="{{URL::asset('/upload/user.png')}}"
                                                                                class="rounded-circle user_img_msg"
                                                                                width="35" height="35" alt=""/>
                                                                    </div>
                                                                    <div class="msg_cotainer">
                                                                        {{$chat->text}}
                                                                        <span
                                                                                class="msg_time"> {{$Jdf->jdate('Y/m-d , H:i:s',strtotime($chat->created_at))}} </span>
                                                                    </div>
                                                                </div>
                                                            @else

                                                                <div class="d-flex justify-content-end mb-4">
                                                                    <div class="msg_cotainer_send">
                                                                        {{$chat->text}}
                                                                        <span
                                                                                class="msg_time_send">{{$Jdf->jdate('Y/m-d , H:i:s',strtotime($chat->created_at))}} </span>
                                                                    </div>
                                                                    <div class="img_cont_msg">
                                                                        <img
                                                                                src="{{URL::asset('/upload/shomare-majazi.png')}}"
                                                                                width="35" height="35"
                                                                                class="rounded-circle user_img_msg"
                                                                                alt="">
                                                                    </div>
                                                                </div>

                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    <div class="card-footer type_msg">
                                                        <form action="{{route('admin.send order chat')}}" method="post"
                                                              style="display: contents;">
                                                            <div class="input-group">
                                                                @csrf
                                                                <input type="hidden" name="order_id"
                                                                       value="{{$Order->id}}">

                                                                <textarea class="form-control"
                                                                          style="padding-right: 5px!important;"
                                                                          name="message"
                                                                          placeholder="پیام خود را تایپ کنید..."></textarea>
                                                                <div class="input-group-append"
                                                                     style="margin-right: 10px!important; ">
                                                                    <button type="submit" class="btn btn-primary"><i
                                                                                class="fa fa-location-arrow"
                                                                                style="margin-top: 5px;"></i></button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="exampleModalCenter">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> تغییر وضعیت سفارش </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span>
                    </button>
                </div>
                <form action="{{route('admin.change order status')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label> وضعیت :</label>
                                    <select class="form-control default-select" name="status" id="sel1">
                                        <option {{$Order->status == 'Waiting to pay' ? 'selected' :''}} value="Waiting to pay">در انتظار پرداخت</option>
                                        <option {{$Order->status == 'Pending' ? 'selected' :''}} value="Pending"> درحال بررسی</option>
                                        <option {{$Order->status == 'complete' ? 'selected' :''}} value="complete">تکمیل شده</option>
                                        <option {{$Order->status == 'failed' ? 'selected' :''}} value="failed"> لغو شده</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label> علت تغییر وضعیت :</label>
                                    <div class="form-group">
                                        <input type="hidden" name="order_id" value="{{$Order->id}}">
                                        <textarea class="form-control" name="detail" rows="4" id="comment"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger light" data-dismiss="modal">بستن</button>
                        <button type="submit" class="btn light btn-success"> ثبت تغییرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop
@section('header')

@stop
@section('footer')

@stop
