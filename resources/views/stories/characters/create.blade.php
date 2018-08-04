@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Create Character</div>
            
            <div class="panel-body">
                
                {!! Form::open(['action' => ['CharactersController@store', $story->id], 'method' => 'post']) !!}
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
                        {{Form::label('role', 'Role')}}
                        {{Form::select('role', config('constants.character_roles'), null, ['class' => 'form-control'])}}
                    </div>
                    
                    <a href="/stories/{{$story->id}}/characters" class="btn btn-default">Back</a>
                    {{Form::submit('Create', ['class' => 'btn btn-primary pull-right'])}}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection