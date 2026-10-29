@extends('site-v2.layout.master',['MetaTitle' => $Blog->meta_title])
@section('content')
    <div class="bg-header py-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" href="{{url('/')}}"><i class="ci-home"></i>خانه</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap"><a href="{{url($Type == 'news' ? '/tech-news' : '/blog')}}">{{$Type == 'news' ? 'اخبار تکنولوژی' : 'وبلاگ شاپ مجازی'}}</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">{{$Blog->title}}</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="fs-10 mb-0">{{$Blog->title}}</h1>
            </div>
        </div>
    </div>
    <div class="container pb-5">
        <div class="row pt-5 mt-md-2">
            <section class="col-lg-8">
                <!-- Post meta-->
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-4 mt-n1">
                    <div class="d-flex align-items-center fs-sm mb-2"><label class="blog-entry-meta-link">
                            <div class="blog-entry-author-ava"><img
                                        src="https://shopmajazi.com/site-v2/img/blog/meta/05.jpg" width="25" height="25"
                                        alt="واحد تولید محتوا"></div>
                            واحد تولید محتوا</label><span class="blog-entry-meta-divider"></span><label
                                class="blog-entry-meta-link">{{$jdf->jdate('d F Y',strtotime($Blog->created_at))}}</label>
                    </div>
                </div>
                <!-- Gallery-->
                <div class="gallery row pb-2">
                    <div class="col-sm-12">
                        <label class="rounded-3 mb-grid-gutter">
                            <img class="rounded-3" src="{{secure_url($Blog->image_big)}}" width="800" height="400"
                                 alt="{{$Blog->title}}">
                        </label>
                    </div>
                </div>

                <div class="col-12 mt-2">
                    <div class="accordion accordion-flush title-area" id="methods">
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <a class="accordion-button collapsed fs-10" href="#reading-area" role="button"
                                   data-bs-toggle="collapse" aria-expanded="false" aria-controls="methodTwo">آنچه
                                    در این
                                    مقاله میخوانید:</a></div>
                            <div class="accordion-collapse collapse" id="reading-area"
                                 data-bs-parent="#methods">
                                <div class="card-body fs-md">
                                    <div class="widget widget-links ">
                                        <ul class="widget-list" id="titles-check">
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Post content-->
                <div class="post-content mt-3" id="Content">
                    {!! $Blog->detail !!}
                </div>

                <div class="d-flex flex-wrap justify-content-between pt-2 pb-4 mb-1">
                    <div class="mt-3 me-3"></div>
                    <div class="mt-3">
                        <div class="d-flex flex-wrap justify-content-between align-items-center pt-3">
                            <div class="py-2">
                                <label class="form-label d-inline-block align-middle my-2 me-3">اشتراک:</label>
                                <a class="btn-share btn-twitter me-2 my-2" target="_blank" rel="noopener"
                                   href="https://twitter.com/intent/tweet?url={{urlencode(URL::current())}}"><i
                                            class="ci-twitter"></i>توییتر</a>
                                <a class="btn-share btn-facebook my-2"
                                   target="_blank" rel="noopener" href="https://www.facebook.com/sharer.php?u={{urlencode(URL::current())}}"><i
                                            class="ci-facebook"></i>فیسبوک</a>
                                <a class="btn-share btn-twitter my-2"
                                   target="_blank" rel="noopener" href="https://t.me/share/url?url={{urlencode(URL::current())}}"><i class="ci-telegram"></i>تلگرام</a>
                                <label class="btn-share btn-instagram my-2 copy" data-copy="{{URL::current()}}"><i
                                            class="icon-copy"></i>کپی لینک</label>
                            </div>
                        </div>
                    </div>
                </div>
                @if(!is_null($Blog->faq) && $Blog->faq != 'null')
                    <h2 id="question" class="h4 pb-3 mt-3">سوالات متداول</h2>
                    <div class="accordion accordion-flush" id="faq">
                        @foreach(json_decode($Blog->faq,true) as $keys => $question)
                            <div class="accordion-item">
                                <h3 class="accordion-header">
                                    <a class="accordion-button" href="#methodOne-{{$keys}}" role="button"
                                       data-bs-toggle="collapse" aria-expanded="false"
                                       aria-controls="methodOne">{{$question['question']}}</a>
                                </h3>
                                <div class="accordion-collapse collapse" id="methodOne-{{$keys}}"
                                     data-bs-parent="#faq">
                                    <div class="card-body fs-md">
                                        {{$question['answer']}}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
            @if(!\Jenssegers\Agent\Facades\Agent::isPhone())
                <aside class="col-lg-4">
                    <!-- Sidebar-->
                    <div class="offcanvas offcanvas-collapse offcanvas-end border-start ms-lg-auto" id="blog-sidebar"
                         style="max-width: 22rem;position: sticky;top: 20px;z-index: 1">
                        <div class="offcanvas-body py-grid-gutter py-lg-1 px-lg-4" data-simplebar
                             data-simplebar-auto-hide="true">
                            <!-- Categories-->
                            <div class="widget widget-links mb-grid-gutter pb-grid-gutter border-bottom mx-lg-2"
                                 id="ourService">
                                <h3 class="widget-title">خدمات ما</h3>
                                <ul class="widget-list">
                                    <li class="widget-list-item"><a
                                                class="widget-list-link d-flex justify-content-between align-items-center"
                                                href="{{secure_url('/virtual-numbers')}}"><span>خرید شماره مجازی</span></a>
                                    </li>
                                    {{--                                    <li class="widget-list-item"><a--}}
                                    {{--                                                class="widget-list-link d-flex justify-content-between align-items-center"--}}
                                    {{--                                                href="{{secure_url('/static-ip')}}"><span>خرید آی پی ثابت</span></a></li>--}}
                                    <li class="widget-list-item"><a
                                                class="widget-list-link d-flex justify-content-between align-items-center"
                                                href="{{secure_url('/apple-id')}}"><span>خرید اپل آیدی</span></a></li>
                                    <li class="widget-list-item"><a
                                                class="widget-list-link d-flex justify-content-between align-items-center"
                                                href="{{secure_url('/google-voice')}}"><span>خرید گوگل ویس</span></a>
                                    </li>
                                </ul>
                            </div>
                            @if(count($TopBlogs) != 0)
                                <!-- پست های پرطرفدار-->
                                <div class="widget mb-grid-gutter pb-grid-gutter border-bottom mx-lg-2">
                                    <label class="h3 widget-title">پست های پرطرفدار</label>
                                    @foreach($TopBlogs as $item)
                                        <div class="d-flex align-items-center mb-3">
                                            <a class="flex-shrink-0"
                                               href="{{route('Single Blog',['id' => $item->slug])}}">
                                                <img class="rounded" src="{{secure_url($item->image)}}" width="64"
                                                     alt="{{$item->title}}">
                                            </a>
                                            <div class="ps-3">
                                                <h6 class="fs-sm mb-0">
                                                    <a class="widget-list-link d-flex justify-content-between align-items-center"
                                                       href="{{route('Single Blog',['id' => $item->slug])}}"><span>{{$item->title}}</span></a>

                                                </h6>
                                                <span class="fs-ms text-muted">توسط <label class='blog-entry-meta-link'>واحد تولید محتوا</label></span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if(count($News) != 0)
                                <!-- پست های پرطرفدار-->
                                <div class="widget mb-grid-gutter pb-grid-gutter border-bottom mx-lg-2">
                                    <label class="h3 widget-title">اخبار جدید</label>
                                    @foreach($News as $item)
                                        <div class="d-flex align-items-center mb-3">
                                            <a class="flex-shrink-0"
                                               href="{{route('Single news',['id' => $item->slug])}}">
                                                <img class="rounded" src="{{secure_url($item->image)}}" width="64"
                                                     alt="{{$item->title}}">
                                            </a>
                                            <div class="ps-3">
                                                <h6 class="fs-sm mb-0">
                                                    <a class="widget-list-link d-flex justify-content-between align-items-center"
                                                       href="{{route('Single news',['id' => $item->slug])}}"><span>{{$item->title}}</span></a>
                                                </h6>
                                                <span class="fs-ms text-muted">تاریخ خبر:<label
                                                            class='blog-entry-meta-link'>{{$jdf->jdate('Y/m/d',strtotime($item->created_at))}}</label></span>
                                                <br>
                                                <span class="fs-ms text-muted">توسط <label class='blog-entry-meta-link'>واحد تولید محتوا</label></span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </aside>
            @endif
        </div>
    </div>
    <!-- Related posts-->
    @include('site-v2.components.comment',[$Comment,'Type' => 'blog' ,'Id' => $Blog->id])

    @include('site-v2.components.related-blog',['Blogs' => $Related, 'RelatedRoute' => $Type == 'news' ? 'Single news' : 'Single Blog'])

@endsection
@section('header')

    <style>
        .fs-10 {
            font-size: 25px !important;
        }

        .post-content {
            text-align: justify;
        }

        .post-content img {
            border-radius: 5px;
            margin: auto;
            display: block;
            height: auto !important;
            width: auto !important;
        }

        .blog-entry-title a {
            font-size: 20px !important;
        }

        .blog-title a {
            color: #373f50;
            text-decoration: none !important;
            line-height: 1.3;
        }

        .read-more {
            display: none;
        }

        .blog-image {
            width: 93% !important;
            display: block !important;
            margin: auto !important;
            margin-top: 10px !important;
            border-radius: 5px !important;
        }

        @if(\Jenssegers\Agent\Facades\Agent::isPhone())
        .post-content p img {
            height: auto;
        !important;

        }
        @endif
    </style>
@endsection
@section('footer')
    <script src="{{secure_url('/site-v2')}}/vendor/tiny-slider/dist/min/tiny-slider.js"></script>


    <script>
        $('.submit-comment').click(function () {
            var BtnId = $(this).attr('id');
            var text = $(this).data('text');
            var form = $(this).data('form');
            $('#' + BtnId).html('لطفا صبر کنید...');
            $('#' + BtnId).attr('disabled', true);
            form = document.querySelector('#' + form);
            $(form).ajaxSubmit({
                dataType: 'json',
                beforeSend: function () {
                    $('#' + BtnId).html('لطفا صبر کنید');
                },
                success: function (data) {
                    success(data.success)
                    $('#' + BtnId).html(text);
                    $('#' + BtnId).attr('disabled', false);
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                },
                error: function (data) {
                    console.log('error')
                    if (data.responseJSON.message) {
                        error('');
                    } else {
                        data.responseJSON.error.forEach(function (value) {
                            error(value);
                        });
                    }
                    $('#' + BtnId).html(text);
                    $('#' + BtnId).attr('disabled', false);
                }
            });
            return false;
        });
        $(window).scroll(function () {
            if ($(window).scrollTop() > 300) {
                $('#ourService').css('margin-top', '80px')
            } else {
                $('#ourService').css('margin-top', '0px')
            }
        });
        $(document).on('click', '.copy', function () {
            $(this).data('copy');
            navigator.clipboard.writeText($(this).data('copy'));
            toastr.success("عملیات کپی با موفقیت انجام شد.", "اطلاعیه", {
                timeOut: 3000,
                closeButton: !0,
                debug: !1,
                newestOnTop: !0,
                progressBar: !0,
                positionClass: "toast-top-right",
                preventDuplicates: !0,
                onclick: null,
                showDuration: "300",
                hideDuration: "1000",
                extendedTimeOut: "1000",
                showEasing: "swing",
                hideEasing: "linear",
                showMethod: "fadeIn",
                hideMethod: "fadeOut",
                tapToDismiss: !1
            });
        });
        $(document).ready(function () {
            $('.simplebar-placeholder').remove();
        })

        $(document).ready(function () {
            var h1, h2, h3, h4, h5, h6 = null
            var count = $('#Content h2,#Content h3,#Content h4,#Content h5,#Content h6').length;
            if (count != 0) {
                $('#Content h2,#Content h3,#Content h4,#Content h5,#Content h6').each(function (value, index) {
                    var val = $(this).html();
                    if (val != '&nbsp;' && val != ' ' && val != null) {
                        $(this).attr('id', 'tag-' + value);
                        if (value != ' ' || value != null) {
                            if ($(this).is('h1')) {
                                h1 = value;
                                $('#titles-check').append('<li class="widget-list-item">' + '<a class="widget-list-link check-article fs-14" href="#tag-' + value + '"> ' + val + '</a>' + '<ul id="level-' + h1 + '"></ul>' + '<li>'
                                );
                            }
                            if ($(this).is('h2')) {
                                h2 = value;
                                if (h1 == null) {
                                    $('#titles-check').append('<li class="widget-list-item">' + '<a class="widget-list-link check-article fs-14" href="#tag-' + value + '"><i class="ci-book opacity-60 align-middle mt-n1 me-1"></i> ' + val + '</a>' + '<ul id="level-' + h2 + '"> </ul>' + '<li>');
                                } else {
                                    $('#level-' + h1).append('<li class="widget-list-item">' + '<a class="widget-list-link check-article fs-14" href="#tag-' + value + '"><i class="ci-book opacity-60 align-middle mt-n1 me-1"></i> ' + val + '</a>' + '<ul id="level-' + h2 + '"> </ul>' + '<li>');
                                }
                            }
                            if ($(this).is('h3')) {
                                if (h1 == null) {
                                    $('#titles-check').append('<li class="widget-list-item mr-3">' + '<a class="widget-list-link check-article fs-14" href="#tag-' + value + '"> <i class="ci-check text-success me-2"></i> ' + val + '</a>' + '<ul id="level-' + h3 + '"> </ul>' + '<li>');
                                } else {
                                    $('#level-' + h2).append('<li class="widget-list-item mr-3">' + '<a class="widget-list-link check-article fs-14" href="#tag-' + value + '"> <i class="ci-check text-success me-2"></i> ' + val + '</a>' + '<ul id="level-' + h3 + '"> </ul>' + '<li>');
                                }
                            }
                            if ($(this).is('h4')) {
                                h4 = value;
                                if (h3 == null) {
                                    $('#titles-check').append('<li class="widget-list-item mr-3">' + '<a class="widget-list-link check-article fs-14" href="#tag-' + value + '"> ' + val + '</a>' + '<ul id="level-' + h4 + '"> </ul>' + '<li>');
                                } else {
                                    $('#level-' + h3).append('<li class="widget-list-item mr-3">' + '<a class="widget-list-link check-article fs-14" href="#tag-' + value + '"> ' + val + '</a>' + '<ul id="level-' + h4 + '"> </ul>' + '<li>');
                                }
                            }
                        }
                    }
                });
                $('.check-article').click(function () {
                    var href = $(this).attr('href')
                    $([document.documentElement, document.body]).animate({
                        scrollTop: $(href).offset().top - 150
                    }, 2000);
                });
            } else {
                $('.ready-article').hide();
            }


            var read_more = $('.read-more').length;
            if(read_more != 0){
                $('.read-more').each(function (index,value) {
                    var jsonObject = JSON.parse($(this).text());
                    console.log()
                    var html = '<div class="d-flex align-items-center mb-3"><a class="flex-shrink-0" href="https://shopmajazi.com/blog/'+jsonObject['slug']+'"><img class="rounded" src="'+jsonObject['image']+'" width="64" alt="'+jsonObject['title']+'"></a><div class="ps-3"><h6 class="fs-sm mb-0"><a class="widget-list-link d-flex justify-content-between align-items-center" href="https://shopmajazi.com/blog/'+jsonObject['slug']+'"><span>'+jsonObject['title']+'</span></a></h6><span class="fs-ms text-muted">توسط<label class="blog-entry-meta-link">واحد تولید محتوا</label></span></div></div>'
                    $(this).css('display','block').empty().append(html)
                });
            }

        });
        @if(isset($_GET['tag']))
        $([document.documentElement, document.body]).animate({scrollTop: $("#{{$_GET['tag']}}").offset().top - 250}, 2000);
        @endif

    </script>

    <script>
        $(document).ready(function (){
            $("img").each(function () {
                var img = $(this);
                var width = Math.floor(img.width());
                var height = Math.floor(img.height());

                img.attr("width",width);
                img.attr("height",height);
            });
        });

        $(document).ready(function() {

            if (window.location.hash) {
                var target = $(window.location.hash);
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 200
                    }, 800, function() {
                        
                        target.css("color", "red");
                        setTimeout(function() {
                            target.css("color", "");
                        }, 2000);
                    });
                }
            }


        });
    </script>



@endsection

@section('seo')
    @php
        $seo['meta_title'] = $Blog->meta_title ?: $Blog->title;
        $seo['meta_desc'] = $Blog->meta_desc ?: \Illuminate\Support\Str::limit(trim(strip_tags($Blog->short_detail)), 160);
        $seo['image'] = secure_url($Blog->image_big);
        $article = [
            '@context' => 'https://schema.org',
            '@type' => $Type == 'news' ? 'NewsArticle' : 'BlogPosting',
            'headline' => $Blog->title,
            'description' => $seo['meta_desc'],
            'image' => [$seo['image']],
            'datePublished' => optional($Blog->created_at)->toIso8601String(),
            'dateModified' => optional($Blog->updated_at ?? $Blog->created_at)->toIso8601String(),
            'mainEntityOfPage' => URL::current(),
            'author' => ['@type' => 'Organization', 'name' => 'شاپ مجازی', 'url' => secure_url('/')],
            'publisher' => ['@type' => 'Organization', 'name' => 'شاپ مجازی', 'logo' => ['@type' => 'ImageObject', 'url' => secure_url('upload/logo/logo-v5.jpg')]],
        ];
    @endphp
    @include('site-v2.layout.seo',['faq' => json_decode($Blog->faq,true), 'ogType' => 'article'])
    <script type="application/ld+json">{!! json_encode($article, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endsection
