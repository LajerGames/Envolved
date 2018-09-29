@extends('layouts.editor', ['js' => ['builder-arch.js']])

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
                        <li class="add-item hastip" data-moretext="Add a new story arch"><a href="/stories/{{$info['story']->id}}/builder/handle"><span class="glyphicon glyphicon-plus"></span> Add arch</a></li>
                        </ul>
                    </div>
                

                    <div id="story-arch-container">
                        
                        @if($info['show'] == 'handle')
                            @include('stories.builder.include.handle_tab', ['info' => $info])
                        @else
                            @foreach($info['story_archs'] as $storyArch)

                                <div class="arch-container {{($storyArch->id == $info['highlight_arch_id'] ? 'highlighted-arch' : '')}}">
                                    &nbsp;
                                    <span class="{{($storyArch->id == $info['highlight_arch_id'] ? 'highlighted-id-number' : 'id-number')}}">{{$storyArch->number}}</span>
                                    <div class="arch-enter hastip" data-moretext="{{$storyArch->description}}" data-story-id="{{$storyArch->story_id}}" data-id="{{$storyArch->id}}">{{$storyArch->name}}</div>
                                    <a href="javascript:void(0);" class="arch-options-menu glyphicon glyphicon-option-vertical"></a>
                                    <div class="arch-options-container popup-menu">
                                        <a href="/stories/{{$info['story']->id}}/builder/handle?id={{$storyArch->id}}">Edit</a>
                                        {!!Form::open([
                                            'action' => ['StoryArchesController@destroy', $info['story']->id, $storyArch->id],
                                            'method' => 'post',
                                            'id' => 'delete_'.$storyArch->id
                                        ])!!}
                                            {{Form::hidden('_method', 'DELETE')}}
                                            <a href="javascript:void(0);" class="submit-form btn-delete" data-submit="delete_{{$storyArch->id}}">Delete</a>
                                        {!!Form::close()!!}
                                    </div>
                                </div>

                            @endforeach
                        @endif

                        <span class="glyphicon glyphicon-question-sign hastip" data-moretext="<u>Tips:</u><br /> - <b>shift + f</b> to search"></span>

                    </div>

                </div>
                
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="search-window" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalLabel">Search</h4>
            </div>
            <div class="modal-body">
                <input list="story-archs" name="search-story-arch" type="text" class="form-control" placeholder="Type searchword" />
                <datalist id="story-archs">
                    @foreach($info['story']->storyarchs->all() as $storyArch)
                        <option data-tab-id="{{$storyArch->tab_id}}" data-id="{{$storyArch->id}}" data-story-id="{{$storyArch->story_id}}" value="{{$storyArch->name}}">{{$storyArch->number}}</option>
                    @endforeach
                </datalist>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection