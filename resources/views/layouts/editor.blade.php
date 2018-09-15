<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ Session::token() }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Entaled') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" rel="stylesheet">

</head>
<body>
    <div id="app">
        <div id="speechbubble"></div>

        @php
            $sidebarClass = '';
            $sidebarTogglerGlyph = 'glyphicon-menu-left';
        @endphp
        @if(!Session::get('ShowSidebar'))
            @php
                $sidebarClass = 'inactive';
                $sidebarTogglerGlyph = 'glyphicon-menu-right';
            @endphp
        @endif

        <nav id="sidebar" class="{{$sidebarClass}}">
            <div class="sidebar-header">
                <h3 class="editor-headline">Story Editor</h3>
            </div>

            <ul class="list-unstyled components">
                
                <!--li class="active">
                    <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false">Home</a>
                    <ul class="collapse list-unstyled" id="homeSubmenu">
                        <li><a href="#">Home 1</a></li>
                        <li><a href="#">Home 2</a></li>
                        <li><a href="#">Home 3</a></li>
                    </ul>
                </li-->
                <p>Main</p>
                <li><a href="/stories/{{request()->segment(2)}}/builder/0">Builder</a></li>
                <li><a href="/stories/{{request()->segment(2)}}/characters">Characters</a></li>
                <li><a href="/stories/{{request()->segment(2)}}/phone_numbers">Phone Numbers</a></li>
                <p>Pre-story data</p>
                <li><a href="/stories/{{request()->segment(2)}}/texts">Texts</a></li>
                <li><a href="/stories/{{request()->segment(2)}}/photos">Photos</a></li>
                <li><a href="/stories/{{request()->segment(2)}}/phonelogs">Phone log</a></li>
                <p>Modules</p>
                <li><a href="/stories/{{request()->segment(2)}}/modules/news">News</a></li>
                <p>More</p>
                <li><a href="/stories/{{request()->segment(2)}}/variables">Variables</a></li>
                <p>Settings</p>
                <li><a href="/stories/{{request()->segment(2)}}/story_settings/edit">Story</a></li>
                <li><a href="/stories/{{request()->segment(2)}}/editor_settings/edit">Editor</a></li>
            </ul>

        </nav>

        <div id="content">

            @include('include.navbar')

            <button type="button" id="sidebarCollapse" class="btn btn-default {{$sidebarClass}}">
                <i class="glyphicon {{$sidebarTogglerGlyph}}"></i>
            </button>

            <div class="container">
                @include('include.messages')
                @yield('content')
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @if(is_array($js))
        @foreach($js as $script)
            <script src="{{ asset('js/'.$script) }}"></script>
        @endforeach
    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
</body>
</html>
