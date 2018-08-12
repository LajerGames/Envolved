@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Update Character</div>
            
            <div class="panel-body">

                @php
                    $story = $info['story'];
                    $variableID = $info['id'];
                    $variable = $story->variables->find($variableID);
                @endphp
                {!! Form::open([
                        'action'    => ['VariablesController@update', $variableID, $story->id],
                        'method'    => 'post'
                ])!!}
                <div class="form-group">
                        {{Form::label('key', 'Key')}}
                        {{Form::text('key', $variable->key, ['class' => 'form-control', 'placeholder' => 'Enter variable key'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('value', 'Default value')}}
                        {{Form::text('value', $variable->value, ['class' => 'form-control', 'placeholder' => 'Enter default value'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('type', 'Type')}}
                        {{Form::select('type', config('constants.variable_types'), $variable->type, ['class' => 'form-control'])}}
                    </div>

                    {{Form::hidden('_method', 'PUT')}}
                    <a href="/stories/{{$story->id}}/variables" class="btn btn-default">Back</a>
                    {{Form::submit('Update', ['class' => 'btn btn-primary pull-right'])}}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection