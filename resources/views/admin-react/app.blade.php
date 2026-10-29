<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>مدیریت شاپ مجازی</title>
    @viteReactRefresh
    @vite('resources/admin/main.tsx')
</head>
<body>
    <div id="admin-root" data-boot='@json($boot)'></div>
</body>
</html>
