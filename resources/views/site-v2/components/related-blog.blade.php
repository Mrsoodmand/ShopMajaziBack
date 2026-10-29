<div class="bg-secondary py-5">
    <div class="container py-3">
        <h2 class="h4 text-center pb-4">ممکن است دوست داشته باشید</h2>
        <div class="tns-carousel ltr">
            <div class="tns-carousel-inner"
                 data-carousel-options="{&quot;items&quot;: 2, &quot;controls&quot;: false, &quot;autoHeight&quot;: true, &quot;responsive&quot;: {&quot;0&quot;:{&quot;items&quot;:1},&quot;500&quot;:{&quot;items&quot;:2, &quot;gutter&quot;: 20},&quot;900&quot;:{&quot;items&quot;:3, &quot;gutter&quot;: 20}, &quot;1100&quot;:{&quot;items&quot;:3, &quot;gutter&quot;: 30}}}">
                @foreach($Blogs as $blog)
                    <article class="rtl">
                        <div class="card">
                            <a class="blog-entry-thumb" href="{{route($RelatedRoute ?? 'Single Blog',['id' => $blog->slug])}}"
                               aria-label="{{$blog->name}}">
                                <img class="card-img-top blog-image" width="400" height="800" src="{{secure_url($blog->image_big)}}"
                                     alt="{{$blog->name}}"></a>
                            <div class="card-body">
                                <h2 class="h6 blog-entry-title"><a
                                            href="{{route($RelatedRoute ?? 'Single Blog',['id' => $blog->slug])}}">{{$blog->title}}</a>
                                </h2>
                                <p class="fs-sm text-justify">{{$blog->short_detail}}</p>
                                <div class="fs-xs text-nowrap">
                                    <div class="blog-entry-meta-link text-nowrap">{{$Jdf->jdate('d F',strtotime($blog->created_at))}}</div>
                                    <span class="blog-entry-meta-divider mx-2"></span>واحد تولید محتوا
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</div>


