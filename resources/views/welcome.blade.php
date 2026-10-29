<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="enamad" content="838208" />
    <title>{{ isset($MetaTitle) ? $MetaTitle : 'شاپ مجازی' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="خرید شماره مجازی، اپل آیدی و خرید ممبر فیک و واقعی تلگرام و انواع خدمات مجازی">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ secure_url('/') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ secure_url('/') }}/upload/logo/apple-fav.jpg">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ secure_url('/') }}/upload/logo/32x32.jpg">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ secure_url('/') }}/upload/logo/16x16.jpg">
    <link rel="stylesheet" href="{{ secure_url('site-v2/css/theme.min.css?v25') }}">
    <link rel="stylesheet" href="{{ secure_url('admin/vendor/toastr/css/toastr.min.css?v=4') }}">

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-M9NKWXDK');</script>
    <!-- End Google Tag Manager -->

    <!-- Microsoft Clarity -->
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "lbb2ig9kcg");
    </script>

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "SiteNavigationElement",
            "name": "Website Menu",
            "hasPart": [
                { "@type": "SiteNavigationElement", "name": "Telegram Services", "url": "{{ secure_url('/virtual-service/telegram') }}" },
                { "@type": "SiteNavigationElement", "name": "Buy Virtual Number", "url": "{{ secure_url('/virtual-numbers') }}" },
                { "@type": "SiteNavigationElement", "name": "Buy Apple ID", "url": "{{ secure_url('/apple-id') }}" },
                { "@type": "SiteNavigationElement", "name": "Buy Premium Accounts", "url": "{{ secure_url('/accounts') }}" }
            ]
        }
    </script>
</head>
<body>
<header class="bg-light shadow-sm navbar-sticky">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="{{ secure_url('/') }}">
                <img src="{{ secure_url('/upload/logo/logo.svg') }}" width="142" height="38" alt="ShopMajazi.com">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#menu-sidebar" aria-label="منو">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse me-auto order-lg-2" id="navbarCollapse">
                <ul class="navbar-nav navbar-mega-nav">
                    <li><a href="{{ secure_url('/virtual-numbers') }}" class="nav-link">خرید شماره مجازی</a></li>
                    <li><a href="{{ secure_url('/apple-id') }}" class="nav-link">خرید اپل آیدی</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">خدمات مجازی</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ secure_url('/virtual-service/telegram/buy-real-telegram-members') }}">خرید ممبر واقعی تلگرام</a></li>
                            <li><a class="dropdown-item" href="{{ secure_url('/virtual-service/telegram/buy-fake-telegram-member') }}">خرید ممبر فیک تلگرام</a></li>
                            <li><a class="dropdown-item" href="{{ secure_url('/virtual-service/telegram/buy-telegram-last-post-view') }}">خرید ویو تلگرام</a></li>
                            <li><a class="dropdown-item" href="{{ secure_url('/virtual-service/telegram/buy-telegram-vote-and-like') }}">خرید رای و لایک تلگرام</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ secure_url('/accounts') }}" data-bs-toggle="dropdown">خرید اکانت های خارجی</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ secure_url('/accounts/telegram-premium') }}">خرید تلگرام پرمیوم</a></li>
                            <li><a class="dropdown-item" href="{{ secure_url('/accounts/trading-view') }}">خرید اکانت تریدینگ ویو</a></li>
                        </ul>
                    </li>
                    <li><a href="{{ secure_url('/blog') }}" class="nav-link">وبلاگ</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
