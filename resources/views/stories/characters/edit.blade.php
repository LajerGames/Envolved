@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Update Character</div>
            
            <div class="panel-body">

                @php
                    $story = $info['story'];
                    $characterID = $info['id'];
                    $character = $story->characters->find($characterID);
                @endphp
                {!! Form::open([
                        'action'    => ['CharactersController@update', $characterID, $story->id],
                        'method'    => 'post',
                        'enctype'   => 'multipart/form-data',
                        'id'        => 'character-form'
                ])!!}
                    <div class="form-group">
                        {{Form::label('first_name', 'First name')}}
                        {{Form::text('first_name', $character->first_name, ['class' => 'form-control', 'placeholder' => 'First name'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('middle_names', 'Middle names')}}
                        {{Form::text('middle_names', $character->middle_names, ['class' => 'form-control', 'placeholder' => 'Middle names'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('last_name', 'Last name')}}
                        {{Form::text('last_name', $character->last_name, ['class' => 'form-control', 'placeholder' => 'Last name'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('role', 'Role')}}
                        {{Form::select('role', config('constants.character_roles'), $character->role, ['class' => 'form-control'])}}
                    </div>

                    <div class="form-group">
                        {{Form::file('avatar')}}
                    </div>

                    {{Form::hidden('_method', 'PUT')}}
                {!! Form::close() !!}

                
                    @if(!empty($character->avatar_url))
                        <div class="image-line">
                            {!!Form::open([
                                'action'    => ['CharactersController@destroy', $story->id, $characterID, 'deleteImageOnly=true'],
                                'method'    => 'post'
                            ])!!}
                                {{Form::hidden('_method', 'DELETE')}}
                                {{Form::submit('Delete image', ['class' => 'btn btn-danger'])}}
                                &nbsp;&nbsp;</a><a href="/storage/stories/{{$story->id}}/characters/{{$character->avatar_url}}" target="_blank">{{$character->avatar_url}}</a>
                            {!!Form::close()!!}
                        </div>
                    @endif
                <a href="/stories/{{$story->id}}/characters" class="btn btn-default">Back</a>
                <a href="javascript:void(0);" class="btn btn-primary pull-right submit-form" data-submit="character-form">Update</a>
            </div>
        </div>
    </div>
</div>
@endsection