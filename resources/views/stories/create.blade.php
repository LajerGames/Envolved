@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">Create Story</div>
            
            <div class="panel-body">
                {!! Form::open(['action' => 'StoriesController@store', 'method' => 'post']) !!}
                    <div class="form-group">
                        {{Form::label('title', 'Title')}}
                        {{Form::text('title', '', ['class' => 'form-control', 'placeholder' => 'Title'])}}
                    </div>
                    <div class="form-group">
                        {{Form::label('short_description', 'Short description')}}
                        {{Form::textarea('short_description', '', ['class' => 'form-control', 'placeholder' => 'Title', 'id' => 'article-ckeditor'])}}
                    </div>
                    
                    <div class="form-group">
                        {{Form::label('description', 'Description')}}
                        {{Form::textarea('description', '', ['class' => 'form-control', 'placeholder' => 'Title', 'id' => 'article-ckeditor2'])}}
                    </div>
                    <a href="/home" class="btn btn-default">Back</a>
                    {{Form::submit('Create', ['class' => 'btn btn-primary pull-right'])}}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection