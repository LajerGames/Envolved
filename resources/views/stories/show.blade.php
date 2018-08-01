@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">{{$story->title}}</div>
            
            <div class="panel-body">
                A lot will go here
                Temp link <a href="/stories/{{$story->id}}/edit" class="btn btn-default">Edit</a>
                {!!Form::open([
                    'action' => ['StoriesController@destroy',
                    $story->id], 'method' => 'post'
                ])!!}
                    {{Form::hidden('_method', 'DELETE')}}
                    {{Form::submit('Delete', ['class' => 'btn btn-danger'])}}
                {!!Form::close()!!}
            </div>
        </div>
    </div>
</div>
@endsection