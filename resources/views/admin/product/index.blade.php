@extends('admin.layout.master',['title'=>' محصولات'])

@section('content')
    <div class="content-body" style="min-height: 1100px;">
        <div class="container-fluid">
            <div class="page-titles">
                <h4>دسته بندی محصولات</h4>
            </div>
            <!-- row -->

            <div class="row">
                <div class="col-lg-4">
                    <form action="">
                        <div class="dashboard_bar">
                            <div class="input-group search-area d-lg-inline-flex d-none">
                                <div class="input-group-append">
                                    <button type="submit" class="input-group-text"><i class="flaticon-381-search-2"></i>
                                    </button>
                                </div>
                                <input type="text"  class="form-control SearchMenu" name="search" value="{{isset($_GET['search']) ? $_GET['search'] :''}}" data-class-target="search-table" placeholder="جستجو...">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4">
                    {!! $list->render('admin.layout.paginate') !!}
                </div>
                <div class="col-lg-4 text-left">
                    {!! $list->render('admin.layout.item') !!}
                </div>
            </div>

            <div class="row mt-2">

                <div class="col-lg-12">
                    <div class="card">

                        <div class="card-header d-sm-flex d-block border-0 pb-0">
                            <div>
                                <h4 class="fs-20 text-black"> محصولات</h4>

                            </div>
                            <div class="card-action card-tabs mt-3 mt-sm-0">
                                <button onclick="location.href ='{{route('admin.create product')}}'"
                                        type="button" class="btn light btn-success">
                                    ایجاد محصول
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-responsive-md">
                                    <thead>
                                    <tr>
                                        <th ><strong>#</strong></th>
                                        <th><strong>نام محصول <sup>فارسی</sup></strong></th>
                                        <th><strong>نام محصول <sup>لاتین</sup></strong></th>
                                        <th><strong>دسته بندی </strong></th>
                                        <th><strong> مشاهده  </strong></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody class="search-table">
                                    @foreach($list as $key => $item)
                                        @php $detail = json_decode($item->detail,true) @endphp
                                        <tr>
                                            <td><strong> {{$list->firstitem() + $key}}</strong></td>
                                            <td> {{$detail['name']}} </td>
                                            <td>{{$detail['enName']}}</td>
                                            <td>{{$item->ServiceCategory->name}}</td>
                                            <td><a href="{{route('SingleSocialServiceProduct',[$item->ServiceCategory->slug,$item->slug])}}"> مشاهده محصول </a> </td>
                                            <td>
                                                <a href="{{route('admin.edit product',[$item->id])}}" class="btn btn-primary shadow btn-xs sharp ml-1"><i class="fa fa-pencil"></i></a>
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
            $('tbody tr').each(function () {
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
