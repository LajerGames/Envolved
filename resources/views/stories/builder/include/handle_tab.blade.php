@php
    $tabs = [];
    foreach($info['settings']->tabs as $id => $tab) {
        $tabs[$id] = $tab->name;
    }
    $editArch = is_object($info['edit_arch']) ? $info['edit_arch'] : '';
@endphp
{!! Form::open(
    [
        'action' => [
            is_object($editArch) ? 'StoryArchesController@update' : 'StoryArchesController@store',
            $info['story']->id,
            is_object($editArch) ? $info['edit_id'] : 'tab'
        ],
        'method' => 'post'
    ]) !!}

<div class="form-group">
    {{Form::label('number', 'Number')}}
    {{Form::number('number', !empty($editArch->number) ? $editArch->number : $info['next_number'], ['class' => 'form-control', 'placeholder' => 'Number'])}}
</div>

    <div class="form-group">
        {{Form::label('tab', 'Tab')}}
        {{Form::select('tab', $tabs, !empty($editArch->tab_id) ? $editArch->tab_id : '', ['class' => 'form-control'])}}
    </div>

    <div class="form-group">
        {{Form::label('name', 'Name')}}
        {{Form::text('name', !empty($editArch->name) ? $editArch->name : '', ['class' => 'form-control', 'placeholder' => 'Name'])}}
    </div>

    <div class="form-group">
        {{Form::label('description', 'Description')}}
        {{Form::text('description', !empty($editArch->description) ? $editArch->description : '', ['class' => 'form-control', 'placeholder' => 'Description'])}}
    </div>

    @if(is_object($info['edit_arch']))
        {{Form::hidden('_method', 'PUT')}}
    @endif

    <a href="/stories/{{$info['story']->id}}/modules/news" class="btn btn-default">Back</a>
    {{Form::submit(is_object($editArch) ? 'Update' : 'Create', ['class' => 'btn btn-primary pull-right'])}}
{!! Form::close() !!}
