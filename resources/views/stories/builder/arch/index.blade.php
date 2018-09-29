@extends('layouts.editor', ['js' => ['builder-story-point.js']])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Arch: {{$info['story_arch']->name}}</div>
            
            <div class="panel-body">
                
            <a href="/stories/{{$info['story']->id}}/builder/arch/{{$info['story_arch']->id}}/" class="pull-right btn btn-success more-button hastip" data-moretext="Add a <b>story point</b> to this <b>story arch</b>.<br /><br />Shortcut: <b>ctrl + n</b>"><span class="glyphicon glyphicon-plus"></span></a>
                HEJSA
                
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="new-story-point-window" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalLabel">New story point</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" value="" name="chosen_story_point_type" />
                <input list="story-point-list" name="story_point_type" type="text" class="form-control" placeholder="Type story point type" />
                <datalist id="story-point-list">
                    @foreach(config('constants.story_points') as $storyPointType => $storyPoint)
                        <option data-value="{{$storyPointType}}" value="{{$storyPoint[0]}}" />
                    @endforeach
                </datalist-->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection