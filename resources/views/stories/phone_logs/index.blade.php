@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Pre-game phone log for: {{$info['story']->title}}</div>
            <div class="panel-body">
            <div class="spacer">  

                {!! Form::open([
                    'action'    => ['PhoneLogsController@store', $info['story']->id],
                    'method'    => 'post',
                    'class'     => 'phonelog-form onload-anchor'
                ]) !!}

                    <div class="form-group">
                        {{Form::label('phone_number_id', 'Character')}}
                        {{Form::select('phone_number_id', $info['phone_numbers_select'], '', ['class' => 'form-control'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('days_ago', 'Days ago')}}
                        {{Form::number('days ago', '', ['class' => 'form-control', 'min'=> '0'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('time', 'Time')}}
                        {{Form::time('time', '', ['class' => 'form-control'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('minutes', 'Minutes')}}
                        {{Form::number('minutes', 5, ['class' => 'form-control'])}}
                    </div>

                    <a href="/stories/{{$info['story']->id}}/texts" class="btn btn-default">Back</a>
                    {{Form::submit('Save', ['class' => 'btn btn-primary pull-right'])}}

                {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection