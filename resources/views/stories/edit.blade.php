@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">Edit Story</div>
            
            <div class="panel-body">
                {!! Form::open(['action' => ['StoriesController@update', $story->id], 'method' => 'post']) !!}
                    <div class="form-group">
                        {{Form::label('title', 'Title')}}
                        {{Form::text('title', $story->title, ['class' => 'form-control', 'placeholder' => 'Title'])}}
                    </div>
                    <div class="form-group">
                        {{Form::label('short_description', 'Short description')}}
                        {{Form::textarea('short_description', $story->short_description, ['class' => 'form-control', 'placeholder' => 'Title', 'id' => 'article-ckeditor'])}}
                    </div>
                    
                    <div class="form-group">
                        {{Form::label('description', 'Description')}}
                        {{Form::textarea('description', $story->description, ['class' => 'form-control', 'placeholder' => 'Title', 'id' => 'article-ckeditor2'])}}
                    </div>
                    {{Form::hidden('_method', 'PUT')}}
                    {{Form::submit('Create', ['class' => 'btn btn-primary'])}}
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection