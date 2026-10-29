@extends('admin.layout.master',['title'=>'لیست کشور های شماره مجازی'])

@section('content')
    <div class="content-body" style="min-height: 1100px;">
        <div class="container-fluid">
            <div class="page-titles">
                <h4>کشور های شماره مجازی</h4>
            </div>
            <!-- row -->

            <div class="row">

                <div class="col-lg-4">

                </div>
                <div class="col-lg-4">
                    {!! $list->render('admin.layout.paginate') !!}
                </div>
                <div class="col-lg-4 text-left">
                    {!! $list->render('admin.layout.item') !!}
                </div>


                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header d-sm-flex d-block border-0 pb-0">
                            <div>
                                <h4 class="fs-20 text-black"> ثبت کشور جدید </h4>

                            </div>
                            <div class="card-action card-tabs mt-3 mt-sm-0">
                                <button onclick="location.href = '{{route('admin.create-Virtual-Services')}} '  " type="button" class="btn light btn-success">
                                    ایجاد سرویس
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-responsive-md">
                                    <thead>
                                    <tr>
                                        <th class="width80"><strong>#</strong></th>
                                        <th><strong>نام کشور <sup>فارسی</sup></strong></th>
                                        <th><strong>نام کشور <sup>لاتین</sup></strong></th>
                                        <th><strong> کد کشور </strong></th>
                                        <th><strong> کد کشور (لند) </strong></th>
                                        <th><strong> ویژه </strong></th>
                                        <th><strong> کلمه کلیدی </strong></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($list as $key => $item)
                                        <tr>
                                            <td><strong> {{$list->firstitem() + $key}}</strong></td>
                                            <td> {{$item->faName}} </td>
                                            <td>{{$item->enName}}</td>
                                            <td> {{$item->code}} </td>
                                            <td> {{$item->land_code}} </td>
                                            <td>{{$item->meta_key}} </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-success light sharp"
                                                            data-toggle="dropdown" aria-expanded="false">
                                                        <svg width="20px" height="20px" viewBox="0 0 24 24"
                                                             version="1.1">
                                                            <g stroke="none" stroke-width="1" fill="none"
                                                               fill-rule="evenodd">
                                                                <rect x="0" y="0" width="24" height="24"></rect>
                                                                <circle fill="#000000" cx="5" cy="12" r="2"></circle>
                                                                <circle fill="#000000" cx="12" cy="12" r="2"></circle>
                                                                <circle fill="#000000" cx="19" cy="12" r="2"></circle>
                                                            </g>
                                                        </svg>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right"
                                                         x-placement="bottom-end"
                                                         style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(137px, 40px, 0px);">
                                                        <a class="dropdown-item" href="{{route('admin.Single-Virtual-Countries',[$item->id])}}">ویرایش</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-lg-4">

                </div>
                <div class="col-lg-4">
                    {!! $list->render('admin.layout.paginate') !!}
                </div>
                <div class="col-lg-4 text-left">
                    {!! $list->render('admin.layout.item') !!}
                </div>
            </div>
        </div>
    </div>



@stop
@section('')

@stop
@section('')

@stop
