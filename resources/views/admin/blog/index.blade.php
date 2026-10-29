@php $Title = 'لیست بلاگ ها' @endphp
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
                            <div class="card-action card-tabs mt-3 mt-sm-0">
                                <button onclick="location.href ='{{route('admin.create blog')}}'" type="button" class="btn light btn-success">
                                    ایجاد بلاگ
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-responsive-sm">
                                    <thead>
                                    <tr>
                                        <th class="width80"><strong>#</strong></th>
                                        <th><strong> عنوان </strong></th>
                                        <th><strong> توضیحات کوتاه </strong></th>
                                        <th><strong> تاریخ </strong></th>
                                        <th><strong> جزئیات </strong></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($list as $key => $item)
                                        <tr class="filter">
                                            <td><strong> {{$list->firstitem() + $key}}</strong></td>
                                            <td><strong> {{$item->title}}</strong></td>
                                            <td><strong> {{$item->short_detail}}</strong></td>
                                            <td><strong> {{$jdf->jdate('Y/m/d ساعت H:i:s',strtotime($item->created_at))}}</strong></td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="{{route('admin.edit blog',[$item->id])}}" title="جزئیات"
                                                       class="btn btn-primary shadow btn-xs sharp ml-1">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <a  href="{{route('admin.delete blog',[$item->id])}}" class="btn btn-danger shadow btn-xs sharp">
                                                        <i class="fa fa-trash"></i></a>
                                                </div>
                                            </td>

                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
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
