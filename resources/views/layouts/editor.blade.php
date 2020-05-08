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
                <p>Save</p>
                <li><a href="/stories/{{request()->segment(2)}}/backup" id="backup-story">Backup</a></li>
                <li><a href="javascript:void(0);" class="hastip" id="export-to-sqlite-link" data-moretext="Shortcut: Press <b>ctrl</b> + <b>shift</b> + <b>s</b>">Export Story</a></li>
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
    <div class="modal fade" id="save_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalLabel">Save</h4>
                </div>
                <div class="modal-body">
                    <label for="save_type">Action</label><br />
                    <select name="save_type" id="save_type" class="form-control">
                        <option value="backup">Backup</option>
                        <option value="export">Export</option>
                    </select><br />
                    <label for="save_as">Save as</label><br />
                    <input type="text" name="save_as" id="save_as" value="" class="form-control" placeholder="Save As" data-story-id="{{request()->segment(2)}}" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-story-id="{{request()->segment(2)}}" data-dismiss="modal">Go!</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/editor.js') }}"></script>
    @if(isset($js) && is_array($js) && count($js) > 0)
        @foreach($js as $script)
            <script src="{{ asset('js/'.$script) }}"></script>
        @endforeach
    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
</body>
</html>
