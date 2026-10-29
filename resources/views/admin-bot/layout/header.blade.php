<!DOCTYPE html>
<html direction="rtl" dir="rtl" style="direction: rtl">
<head>
    <base href="{{url('/')}}"/>
    <title>{{isset($metaTitle) ? $metaTitle .' | پنل مدیریت ' : 'پنل مدیریت'}}</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta property="og:locale" content="en_US"/>
    <meta property="og:type" content="article"/>
    <meta property="og:title" content="پنل ادمین مترونیک  "/>
    <meta property="og:url" content="https://keenthemes.com/metronic"/>
    <meta property="og:site_name" content="مترونیک by ساتراس وب"/>
    <link rel="canonical" href="http://dashboards/bidding.html"/>
    <link rel="shortcut icon" href="{{url('bot-folder')}}/assets/media/logos/favicon.ico"/>

    <link href="{{url('bot-folder')}}/assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet"
          type="text/css"/>

    <link href="{{url('bot-folder')}}/assets/plugins/global/plugins.bundle.rtl.css" rel="stylesheet" type="text/css"/>
    <link href="{{url('bot-folder')}}/assets/css/style.bundle.rtl.css" rel="stylesheet" type="text/css"/>
    @yield('header')
    <script>// Frame-busting to prevent site from being loaded within a frame without permission (click-jacking) if (window.top != window.self) { window.top.location.replace(window.self.location.href); }</script>
</head>


<body>

<script>
    var defaultThemeMode = "light";
    var thememode;
    if (document.documentElement) {
        if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
            thememode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
            if (localStorage.getitem("data-bs-theme") !== null) {
                thememode = localStorage.getitem("data-bs-theme");
            } else {
                thememode = defaultThemeMode;
            }
        }
    }
</script>
