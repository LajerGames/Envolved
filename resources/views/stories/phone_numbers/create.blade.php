@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Create Phone Number</div>
            
            <div class="panel-body">
                
                {!! Form::open(['action' => ['PhoneNumbersController@store', $info['story']->id], 'method' => 'post']) !!}
                    <div class="form-group">
                        {{Form::label('number', 'Phone number')}}
                        {{Form::text('number', '', ['class' => 'form-control', 'placeholder' => 'Enter phone number'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('character_id', 'Character')}}
                        {{Form::select('character_id', $info['characters_list'], null, ['class' => 'form-control overwrite-field-onchange', 'data-field-to-overwrite' => 'name'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('name', 'Name')}}
                        {{Form::text('name', '', ['class' => 'form-control', 'placeholder' => 'Enter Name (Not only for editor)'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('settings[messagable]', 'Can receive messages')}}
                        {{Form::select('settings[messagable]', [0 => 'No', 1 => 'Yes'], 1, ['class' => 'form-control'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('settings[text_story_arch]', 'Message story arch')}}
                        {{Form::select('settings[text_story_arch]', $info['story_archs'], null, ['class' => 'form-control'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('settings[call_story_arch]', 'Dial up story arch')}}
                        {{Form::select('settings[call_story_arch]', $info['story_archs'], null, ['class' => 'form-control'])}}
                    </div>
                    
                    <a href="/stories/{{$info['story']->id}}/phone_numbers" class="btn btn-default">Back</a>
                    {{Form::submit('Create', ['class' => 'btn btn-primary pull-right'])}}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection