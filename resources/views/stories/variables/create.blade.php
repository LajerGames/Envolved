@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Create Variable</div>
            
            <div class="panel-body">
                
                {!! Form::open(['action' => ['VariablesController@store', $story->id], 'method' => 'post']) !!}
                    <div class="form-group">
                        {{Form::label('key', 'Key')}}
                        {{Form::text('key', '', ['class' => 'form-control', 'placeholder' => 'Enter variable key'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('value', 'Default value')}}
                        {{Form::text('value', '', ['class' => 'form-control', 'placeholder' => 'Enter default value'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('type', 'Type')}}
                        {{Form::select('type', config('constants.variable_types'), null, ['class' => 'form-control'])}}
                    </div>
                    
                    <a href="/stories/{{$story->id}}/variables" class="btn btn-default">Back</a>
                    {{Form::submit('Create', ['class' => 'btn btn-primary pull-right'])}}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection