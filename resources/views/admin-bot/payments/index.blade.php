@php $title = 'لیست تراکنشات'; @endphp

@extends('admin-bot.layout.master',['metaTitle' => $title])
@section('header')

@endsection

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="toolbar d-flex flex-stack mb-3 mb-lg-5" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-xxl d-flex flex-stack flex-wrap">
                <div class="page-title d-flex flex-column me-5 py-2">
                    <h1 class="d-flex flex-column text-gray-900 fw-bold fs-3 mb-0">{{$title}}</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="index.html" class="text-muted text-hover-primary">خانه</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-200 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-gray-900">{{$title}}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card ms-5 mb-xl-8">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">{{$title}}</span>
                        </h3>
                    </div>
                    <div class="card-body py-3">
                        @include('admin-bot.comp.transaction',['list' => $list])

                    </div>
                    <div class="card-footer">
                        {{$list->render('admin-bot.layout.paginate')}}
                    </div>
                </div>



            </div>
        </div>
    </div>
@endsection


@section('footer')
    <script>
        $(document).on('click', '.check-payment', function () {
            var id = $(this).data('id');
            $(this).empty().text('لطفا صبر کنید...');

            $.ajax({
                url: '{{route('admin.bot-payment-single')}}',
                dataType: 'JSON',
                method: "GET",
                data: {
                    id: id
                }, success: function (data) {
                    $('#PaymentDetail').empty().append(data.data);
                    $('#PaymentModal').modal('toggle');
                    $('.check-payment').empty().text('مشاهده جزئیات');
                }
            });
        });
    </script>
@endsection
