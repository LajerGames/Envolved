@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Editor Settings</div>
            
            <div class="panel-body">

                {!! Form::open([
                        'action'    => ['SettingsEditorsController@update', $info['story']->id],
                        'method'    => 'post',
                        'id'        => 'editor-settings'
                ])!!}

                    <h2>Default editor values</h2>

                    <h3>Phone</h3>

                    <div class="indent-20">
                        <div class="form-group">
                            {{Form::label('phone_ring_patience', 'Ring patience (seconds)')}}
                            {{Form::number('phone_ring_patience', intval($info['settings']->phone_ring_patience), ['class' => 'form-control', 'min'=> '0'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('phone_time_between_call_logs_pregame', 'Time between call logs (minutes - pre game)')}}
                            {{Form::number('phone_time_between_call_logs_pregame', intval($info['settings']->phone_time_between_call_logs_pregame), ['class' => 'form-control', 'min'=> '0'])}}
                        </div>
                    </div>

                    <h3>Texts</h3>

                    <div class="indent-20">

                        <div class="form-group">
                            {{Form::label('text_time_before_read', 'Time before read (seconds)')}}
                            {{Form::number('text_time_before_read', intval($info['settings']->text_time_before_read), ['class' => 'form-control', 'min'=> '0'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('text_time_to_read', 'Time to read (words per minute)')}}
                            {{Form::number('text_time_to_read', intval($info['settings']->text_time_to_read), ['class' => 'form-control', 'min'=> '0'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('text_time_to_reply', 'Time to reply (characters per minute)')}}
                            {{Form::number('text_time_to_reply', intval($info['settings']->text_time_to_reply), ['class' => 'form-control', 'min'=> '0'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('text_time_between_texts_prestory', 'Time between texts (miutes - pre story)')}}
                            {{Form::number('text_time_between_texts_prestory', intval($info['settings']->text_time_between_texts_prestory), ['class' => 'form-control', 'min'=> '0'])}}
                        </div>

                    </div>

                    <h3>Photos</h3>

                    <div class="indent-20">
                        <div class="form-group">
                            {{Form::label('photos_time_between_photos', 'Time between photos (minutes)')}}
                            {{Form::number('photos_time_between_photos', intval($info['settings']->photos_time_between_photos), ['class' => 'form-control', 'min'=> '0'])}}
                        </div>
                    </div>

                    <a href="javascript:void(0);" class="btn btn-success pull-right add-tab"><span class="glyphicon glyphicon-plus"></span></a>
                    <h2>Editor tabs</h2>

                    <div id="tabs-container" class="sortable tabs-container">{!!$info['tabs']!!}</div>

                    {{Form::hidden('_method', 'PUT')}}
                    {{Form::submit('Update', ['class' => 'btn btn-primary pull-right'])}}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection