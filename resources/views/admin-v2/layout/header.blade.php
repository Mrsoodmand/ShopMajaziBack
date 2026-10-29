<!DOCTYPE html>
<html direction="rtl" dir="rtl" style="direction: rtl" >
<!--begin::Head-->
<head><base href="">
    <title>{{isset($MetaTitle) ? $MetaTitle. "| پنل مدیریت" : 'پنل مدیریت'}}</title>
    <meta charset="utf-8" />
    <meta name="description" content="پنل مدیریت شاپ مجازی" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="پنل مدیریت شاپ مجازی" />
    <meta property="og:url" content="https://keenthemes.com/metronic" />
    <meta property="og:site_name" content="پنل مدیریت شاپ مجازی" />
    <link rel="canonical" href="Https://preview.keenthemes.com/metronic8" />
    <link rel="shortcut icon" href="{{url('/admin-v2/assets/media/logos/favicon.ico')}}" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Page Vendor Stylesheets(used by this page)-->
    <link href="{{secure_url('/admin-v2/assets/plugins/custom/fullcalendar/fullcalendar.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{secure_url('/admin-v2/assets/plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
    <!--end::Page Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
{{--    <link href="{{secure_url('/admin-v2/assets/plugins/global/plugins.dark.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />--}}
{{--    <link href="{{secure_url('/admin-v2/assets/css/style.dark.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />--}}

    <link href="{{secure_url('admin-v2')}}/assets/plugins/global/plugins.bundle.rtl.css" rel="stylesheet" type="text/css">
    <link href="{{secure_url('admin-v2')}}/assets/css/style.bundle.rtl.css" rel="stylesheet" type="text/css">

    <link rel="stylesheet" media="screen" href="{{URL::asset('fontawesome/web/css/all.css?v=1')}}">
    <!--end::Global Stylesheets Bundle-->
    @yield('header')
</head>
