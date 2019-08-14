@extends('layouts.editor', ['js' => ['builder-story-point.js']])

@section('content')
<input type="hidden" value="{{$info['story']->id}}" id="story_id" />
<input type="hidden" value="{{$info['story_arch']->id}}" id="story_arch_id" />
<input type="hidden" value="{{$info['story_arch']->start_story_point_id}}" id="story_arch_start_story_point_id" />
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Arch: {{$info['story_arch']->name}}</div>
            
            <div class="panel-body">
                
                {!!$info['storyPointHTML']!!}


            <a href="javascript:void(0);" class="add-story-point btn btn-success more-button hastip {{$info['add-button-disabled']}}" data-moretext="Add a <b>story point</b> to this <b>story arch</b>.<br /><br />Shortcut: <b>ctrl + shift + x</b>"><span class="glyphicon glyphicon-plus"></span></a>

                <span class="glyphicon glyphicon-question-sign story-points hastip" data-moretext="<u>Tips:</u><br /> - <b>ctrl + shift + f</b> to select story point"></span>
                
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
                <input type="hidden" value="" name="parent_id" />
                <input type="number" min="0" name="story_point_parent" class="form-control story-point-parent-no" placeholder="Type number of parent" />
                <input type="hidden" value="" name="chosen_story_point_type" />
                <input list="story-point-list" name="story_point_type" type="text" class="form-control" placeholder="Type story point type" />
                <datalist id="story-point-list">
                    @foreach(config('constants.story_points') as $storyPointType => $storyPoint)
                        <option data-value="{{$storyPointType}}" value="{{$storyPoint[0]}}" />
                    @endforeach
                </datalist>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="select-story-point" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalLabel">Select Story Point</h4>
            </div>
            <div class="modal-body">
                <input name="select-story-point" type="number" class="form-control" placeholder="Choose story point number" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Select</button>
            </div>
        </div>
    </div>
</div>
@endsection