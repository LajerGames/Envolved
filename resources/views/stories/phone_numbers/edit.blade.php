@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Edit Phone Number</div>
            
            <div class="panel-body">
                @php
                    $story = $info['story'];
                    $phoneNumberID = $info['id'];
                    $phoneNumber = $story->phonenumber->find($phoneNumberID);
                @endphp
                {!! Form::open([
                    'action' => ['PhoneNumbersController@update', $phoneNumberID, $story->id],
                    'method' => 'post'
                ]) !!}
                    <div class="form-group">
                        {{Form::label('number', 'Phone number')}}
                        {{Form::text('number', $phoneNumber->number, ['class' => 'form-control', 'placeholder' => 'Enter phone number'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('character_id', 'Character')}}
                        {{Form::select('character_id', $info['characters_list'], $phoneNumber->character_id, ['class' => 'form-control overwrite-field-onchange', 'data-field-to-overwrite' => 'name'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('name', 'Name')}}
                        {{Form::text('name', $phoneNumber->name, ['class' => 'form-control', 'placeholder' => 'Enter Name (Not only for editor)'])}}
                    </div>
                    
                    <a href="/stories/{{$info['story']->id}}/phone_numbers" class="btn btn-default">Back</a>
                    {{Form::submit('Update', ['class' => 'btn btn-primary pull-right'])}}
                    {{Form::hidden('_method', 'PUT')}}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection