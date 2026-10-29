@php
    $seo = $seo ?? null;
    $page = (int) request()->query('page', 1);
    // Canonical without tracking/query noise; paginated pages keep their own ?page so they aren't folded into page 1.
    $canonical = urldecode(URL::current()) . ($page >= 2 ? '?page=' . $page : '');
    if (!empty($seo['image'])) {
        $seoImage = URL::asset($seo['image']);
    } elseif (!empty($image)) {
        $seoImage = URL::asset($image);
    } else {
        $seoImage = secure_url('upload/logo/logo-v5.jpg');
    }
@endphp
@if(!empty($seo['meta_desc']))
<meta name="description" content="{{$seo['meta_desc']}}">
@endif
@if(!empty($noindex))
<meta name="robots" content="noindex,follow"/>
@elseif($page >= 2)
{{-- deeper list pages: keep out of the index but let crawlers follow through to the posts --}}
<meta name="robots" content="noindex,follow"/>
@else
<meta name="robots" content="index,follow"/>
@endif
<meta name="format-detection" content="telephone=no">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@shopmajazi">
@if(!empty($seo['meta_title']))
<meta name="twitter:title" content="{{$seo['meta_title']}}">
<meta property="og:title" content="{{$seo['meta_title']}}">
@endif
@if(!empty($seo['meta_desc']))
<meta name="twitter:description" content="{{$seo['meta_desc']}}">
<meta property="og:description" content="{{$seo['meta_desc']}}">
@endif
<meta name="twitter:image" content="{{$seoImage}}">
<meta property="og:image" content="{{$seoImage}}">
<meta property="og:type" content="{{$ogType ?? 'website'}}">
<meta property="og:url" content="{{$canonical}}">
<meta property="og:site_name" content="شاپ مجازی">
<meta property="og:locale" content="fa_IR">
@if(empty($noindex))
<link rel="canonical" href="{{$canonical}}"/>
@endif
@if(!empty($faq) && is_array($faq))
    @include('site-v2.layout.faq-schema',['FAQ' => $faq])
@endif
@if(!empty($seo['seo']))
    {!! $seo['seo'] !!}
@endif
