@php
    $isNews = ($Type ?? 'blog') == 'news';
    $listTitle = $isNews ? 'اخبار تکنولوژی و فضای مجازی | شاپ مجازی' : 'وبلاگ شاپ مجازی، آموزش و ترفندهای فضای مجازی';
    $listH1 = $isNews ? 'جدیدترین اخبار تکنولوژی و فضای مجازی' : 'وبلاگ شاپ مجازی؛ آموزش و ترفندهای فضای مجازی';
    $listCrumb = $isNews ? 'اخبار تکنولوژی' : 'وبلاگ شاپ مجازی';
    if ($Blogs->currentPage() > 1) {
        $listTitle .= ' - صفحه ' . $Blogs->currentPage();
    }
@endphp
@extends('site-v2.layout.master',['MetaTitle' => $listTitle])
@section('seo')
    @php
        $seo['meta_title'] = $listTitle;
        $seo['meta_desc'] = $isNews
            ? 'آخرین اخبار تکنولوژی، شبکه‌های اجتماعی، هوش مصنوعی و فضای مجازی را در شاپ مجازی بخوانید.'
            : 'آموزش‌های کاربردی شبکه‌های اجتماعی، شماره مجازی، گوگل ویس، اپل آیدی و اکانت‌های خارجی در وبلاگ شاپ مجازی.';
    @endphp
    @include('site-v2.layout.seo')
@endsection
@section('content')

    <div class="bg-header py-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" href="{{secure_url('/')}}"><i
                                        class="ci-home"></i>شاپ
                                مجازی</a></li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">{{$listCrumb}}</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 mb-0">{{$listH1}}</h1>
            </div>
        </div>
    </div>
    <div class="container pb-5 mb-2 mb-md-4">
        <div class="row pt-5 mt-md-2">
            <section class="col-lg-12">
                <div class="row">
                    @foreach($Blogs as $blog)
                        <div class="col-12 col-lg-4 mb-4">
                            <article class="masonry-grid-item">
                                <div class="card">
                                    <a target="_blank" aria-label="{{$blog->title}}" class="blog-entry-thumb"
                                       href="{{route($Type == 'news' ? 'Single news' : 'Single Blog',['id' => $blog->slug])}}">
                                        <img class="card-img-top blog-image"
                                             src="{{secure_url($blog->image)}}" alt="{{$blog->title}}">
                                    </a>
                                    <div class="card-body">
                                        <h2 class="h6 blog-entry-title">
                                            <a target="_blank" aria-label="{{$blog->title}}"
                                               href="{{route($Type == 'news' ? 'Single news' : 'Single Blog',['id' => $blog->slug])}}">{{$blog->title}}</a>
                                        </h2>
                                        <p class="fs-sm text-justify">{!! $blog->short_detail !!}</p>
                                    </div>
                                    <div class="card-footer d-flex align-items-center fs-xs">
                                        <label class="blog-entry-meta-link">
                                            <div class="blog-entry-author-ava"><img
                                                        src="{{secure_url('/site-v2')}}/img/blog/meta/05.jpg"
                                                        alt="واحد تولید محتوا"></div>
                                            واحد تولید محتوا</label>
                                        <div class="ms-auto text-nowrap">
                                            <label class="blog-entry-meta-link text-nowrap">{{$Jdf->jdate('d F Y',strtotime($blog->created_at))}}</label>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
                <hr class="mb-4">
                <!-- Pagination-->
                {!! $Blogs->render('vendor.pagination.bootstrap-4') !!}
            </section>
        </div>
    </div>
    @include('site-v2.components.related-blog',['Blogs' => $News, 'RelatedRoute' => 'Single news'])

@endsection
@section('header')
    <style>
        .blog-entry-title a {
            font-size: 20px !important;
        }

        .text-justify {
            text-align: justify;
        }

        .blog-image {
            width: 93% !important;
            display: block !important;
            margin: auto !important;
            margin-top: 10px !important;
            border-radius: 5px !important;
        }
    </style>
@endsection
@section('footer')
    <script>
        $(document).ready(function (){
            $("img").each(function () {
                var img = $(this);
                var width = img.width();
                var height = img.height();

                img.attr("width",width);
                img.attr("height",height);
            });
        })
       
    </script>
@endsection
