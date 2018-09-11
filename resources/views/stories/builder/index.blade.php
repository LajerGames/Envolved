@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Builder</div>
            
            <div class="panel-body">

                <div class="container">
                    
                    <div class="scroller scroller-right"><i class="glyphicon glyphicon-chevron-right"></i></div>
                    <div class="scroller scroller-left"><i class="glyphicon glyphicon-chevron-left"></i></div>
                    <div class="wrapper">
                        <ul class="nav nav-tabs list" id="myTab">
                            @foreach($info['settings']->tabs as $id => $tabInfo)
                                <li class="hastip {{($info['show'] == $id ? 'active' : '')}}" data-moretext="{{$tabInfo->description}}"><a href="/stories/4/builder/{{$id}}">{{$tabInfo->name}}</a></li>
                            @endforeach
                            <li class="add-item hastip" data-moretext="Add a new storyarch"><a href="/stories/4/builder/add"><span class="glyphicon glyphicon-plus"></span> Add arch</a></li>
                        </ul>
                    </div>
                

                    <div id="story-arch-container">
                        
                        @if($info['show'] == 'add')
                            @include('stories.builder.include.add_tab', ['info' => $info])
                        @else
                            @foreach($info['story_archs'] as $storyArch)

                                <div class="arch-container hastip" data-moretext="{{$storyArch->description}}">
                                    <span class="id-number">1</span>
                                    {{$storyArch->name}}
                                    <a href="javascript:void(0);" class="arch-options-menu glyphicon glyphicon-option-vertical"></a>
                                </div>

                            @endforeach
                        @endif

                    </div>

                </div>
                
            </div>
        </div>
    </div>
</div>
@endsection