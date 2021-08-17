<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <title>{{ $config->get('App Name') }} - @yield('title')</title>
    <!-- Load jQuery -->
    <script src="/public/js/jquery.min.js"></script>
    <!-- Load fomantic  -->
    <link rel="stylesheet" type="text/css" href="/public/semantic/semantic.min.css">
    <script src="/public/semantic/semantic.min.js"></script>
    <!-- Load custom css/js  -->
    <link rel="stylesheet" type="text/css" href="/public/css/style.css">
    <!-- Temp till I decide which lib to use -->
    <script src="/public/js/sortable.min.js"></script>
    <script src="/public/js/sortable.jquery.min.js"></script>
    <!-- Other JS -->
    <script src="/public/js/string.js"></script>
    <script src="/public/js/timeago.min.js"></script>
    <!-- Quill Editor -->
    <link rel="stylesheet" type="text/css" href="/public/quill/snow.min.css">
    <script src="/public/quill/quill.min.js"></script>
    <!-- Full Calendar -->
    <link href='/public/fullcalendar/main.css' rel='stylesheet' />
    <script src='/public/fullcalendar/main.js'></script>

</head>
<body class="background">
    <div class="ui vertical inverted left visible sidebar menu">
        @include('partials.navigation')
    </div>
    <div class="pusher">
        <div class="container" style="padding:1.5em calc(260px + 1.5em) 1.5em 1.5em;">
            @yield('content')
        </div>
    </div>
</body>
</html>