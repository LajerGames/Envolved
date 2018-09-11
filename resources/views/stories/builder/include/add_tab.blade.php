@php
    $tabs = [];
    foreach($info['settings']->tabs as $id => $tab) {
        $tabs[$id] = $tab->name;
    }
@endphp
{!! Form::open(['action' => ['StoryArchesController@store', $info['story']->id, 'tab'], 'method' => 'post']) !!}

    <div class="form-group">
        {{Form::label('tab', 'Tab')}}
        {{Form::select('tab', $tabs, '', ['class' => 'form-control'])}}
    </div>

    <div class="form-group">
        {{Form::label('name', 'Name')}}
        {{Form::text('name', '', ['class' => 'form-control', 'placeholder' => 'Name'])}}
    </div>

    <div class="form-group">
        {{Form::label('description', 'Description')}}
        {{Form::text('description', '', ['class' => 'form-control', 'placeholder' => 'Description'])}}
    </div>

    <a href="/stories/{{$info['story']->id}}/modules/news" class="btn btn-default">Back</a>
    {{Form::submit('Create', ['class' => 'btn btn-primary pull-right'])}}
{!! Form::close() !!}
