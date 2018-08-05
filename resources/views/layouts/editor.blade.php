<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ Session::token() }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Envolved') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

</head>
<body>
    <div id="app">

        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>Story Editor</h3>
            </div>

            <ul class="list-unstyled components">
                <!--p>Dummy Heading</p-->
                <!--li class="active">
                    <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false">Home</a>
                    <ul class="collapse list-unstyled" id="homeSubmenu">
                        <li><a href="#">Home 1</a></li>
                        <li><a href="#">Home 2</a></li>
                        <li><a href="#">Home 3</a></li>
                    </ul>
                </li-->
                <li>
                    <a href="/stories/{{request()->segment(2)}}">Main</a>
                    <a href="/stories/{{request()->segment(2)}}/characters">Characters</a>
                </li>
            </ul>

        </nav>

        <div id="content">

            @include('include.navbar')

            <button type="button" id="sidebarCollapse" class="btn btn-default">
                <i class="glyphicon glyphicon-menu-left"></i>
            </button>

            <div class="container">
                @include('include.messages')
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="/vendor/unisharp/laravel-ckeditor/ckeditor.js"></script>
    <script>
        CKEDITOR.replace( 'article-ckeditor' );
        CKEDITOR.replace( 'article-ckeditor2' );
    </script>
</body>
</html>
