@php $Title = 'لیست تیکت ها' @endphp
@extends('admin.layout.master',['title'=>$Title])

@section('content')
    <div class="content-body" style="min-height: 1100px;">
        <div class="container-fluid">
            <div class="page-titles">
                <h4> {{$Title}} </h4>
            </div>
            <!-- row -->
            <div class="row">
                <div class="col-lg-4 text-end">

                </div>
                <div class="col-lg-4">
                    {!! $list->render('admin.layout.paginate') !!}
                </div>
                <div class="col-lg-4 text-left">
                    {!! $list->render('admin.layout.item') !!}
                </div>
            </div>
            <div class="row">


                <div class="col-lg-12 mt-3">
                    <div class="card">
                        <div class="card-header">
                            <form action="">
                                <div class="dashboard_bar">
                                    <div class="input-group search-area d-lg-inline-flex d-none">
                                        <div class="input-group-append">
                                            <button type="submit" class="input-group-text"><i class="flaticon-381-search-2"></i>
                                            </button>
                                        </div>
                                        <input type="text" class="form-control SearchMenu" name="search"
                                               value="{{isset($_GET['search']) ? $_GET['search'] :''}}"
                                               data-class-target="search-table" placeholder="جستجو...">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-responsive-sm">
                                    <thead>
                                    <tr>
                                        <th class="width80"><strong>#</strong></th>
                                        <th ><strong>شناسه تیکت </strong></th>
                                        <th><strong> نام کاربر </strong></th>
                                        <th><strong> شماره کاربر </strong></th>
                                        <th><strong> وضعیت </strong></th>
                                        <th><strong> عنوان </strong></th>
                                        <th><strong> تاریخ ایجاد </strong></th>
                                        <th><strong> تاریخ اخرین پاسخ </strong></th>
                                        <th><strong> جزئیات </strong></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($list as $key => $item)
                                        <tr class="filter">
                                            <td><strong> {{$list->firstitem() + $key}}</strong></td>
                                            <td><strong> {{$item->id}}</strong></td>
                                            <td><strong> {{$item->User->name}}</strong></td>
                                            <td><strong> {{$item->User->mobile}}</strong></td>
                                            <td><strong> {{TicketStatus($item->status)}}  </strong></td>
                                            <td><strong> {{$item->subject}}</strong></td>
                                            <td><strong> {{$jdf->jdate('Y/m/d ساعت: H:i:s',strtotime($item->created_at))}}</strong></td>
                                            <td><strong> {{$jdf->jdate('Y/m/d ساعت: H:i:s',strtotime($item->updated_at))}}  </strong></td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="{{route('admin.ticket single',[$item->id])}}" title="جزئیات"
                                                       class="btn btn-primary shadow btn-xs sharp ml-1">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
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
@section('footer')
    <script>
        $(".SearchMenu").keyup(function () {
            var ClassTarget = $(this).data('class-target');

            // Retrieve the input field text and reset the count to zero
            var filter = $(this).val();
            count = 0;
            // Loop through the comment list
            $('.filter').each(function () {
                // If the list item does not contain the text phrase fade it out
                if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                    $(this).fadeOut();  // MY CHANGE

                    // Show the list item if the phrase matches and increase the count by 1
                } else {
                    $(this).fadeIn(); // MY CHANGE
                    count++;
                }

            });


        });
    </script>
@stop
