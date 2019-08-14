@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Create Character</div>
            
            <div class="panel-body">
                @php
                    $story = $info['story'];
                    $protagonist = $story->characters->where('role', 'protagonist')->first();
                    $strRoles = config('constants.character_roles');
                @endphp
                @if(!empty($protagonist))
                    @php
                        $strRoles = config('constants.character_roles_wo_protagonist');
                    @endphp
                @endif
                
                {!! Form::open(['action' => ['CharactersController@store', $story->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}
                    <div class="form-group">
                        {{Form::label('first_name', 'First name')}}
                        {{Form::text('first_name', '', ['class' => 'form-control', 'placeholder' => 'First name'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('middle_names', 'Middle names')}}
                        {{Form::text('middle_names', '', ['class' => 'form-control', 'placeholder' => 'Middle names'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('last_name', 'Last name')}}
                        {{Form::text('last_name', '', ['class' => 'form-control', 'placeholder' => 'Last name'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('gender', 'Gender')}}
                        {{Form::select('gender', config('constants.genders'), null, ['class' => 'form-control'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('role', 'Role')}}
                        {{Form::select('role', $strRoles, null, ['class' => 'form-control'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('in_contacts', 'In contacts')}}
                        {{Form::select('in_contacts', [0 => 'No', 1 => 'Yes'], '', ['class' => 'form-control'])}}
                    </div>

                    <h3 class="character-settings-expandable character-settings-phone-expandable"><span class="glyphicon glyphicon-plus character-settings-expand-plus"></span>Phone call stats</h3>

                    <div class="indent-20 character-expandable character-phone-expandable">
                        <div class="form-group">
                            {{Form::label('phone_ring_patience', 'Ring patience (seconds)')}}
                            {{Form::number('settings[phone_ring_patience]', intval($info['settings']['phone_ring_patience']), ['class' => 'form-control', 'min'=> '0'])}}
                        </div>
                    </div>

                    <h3 class="character-settings-expandable character-settings-text-expandable"><span class="glyphicon glyphicon-plus character-settings-expand-plus"></span>Texts stats</h3>

                    <div class="indent-20 character-expandable character-text-expandable">

                        <div class="form-group">
                            {{Form::label('text_time_before_read', 'Time before read (seconds)')}}
                            {{Form::number('settings[text_time_before_read]', intval($info['settings']['text_time_before_read']), ['class' => 'form-control', 'min'=> '0'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('text_time_to_read', 'Time to read (words per minute)')}}
                            {{Form::number('settings[text_time_to_read]', intval($info['settings']['text_time_to_read']), ['class' => 'form-control', 'min'=> '0'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('text_time_to_reply', 'Time to reply (characters per minute)')}}
                            {{Form::number('settings[text_time_to_reply]', intval($info['settings']['text_time_to_reply']), ['class' => 'form-control', 'min'=> '0'])}}
                        </div>

                    </div>

                    <div class="form-group">
                        {{Form::file('avatar')}}
                    </div>
                    
                    <a href="/stories/{{$story->id}}/characters" class="btn btn-default">Back</a>
                    {{Form::submit('Create', ['class' => 'btn btn-primary pull-right'])}}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection