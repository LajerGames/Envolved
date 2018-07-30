@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">Stories</div>
            
            <div class="panel-body">
                @if(count($stories) > 0)
                    @foreach($stories as $story)
                        <a href="/stories/{{$story->id}}" class="show well">
                            <h3>{{$story->title}}</h3>
                            <small>Last updated {{$story->updated_at}}</small>
                        </a>
                    @endforeach
                    {{$stories->links()}}
                @else
                    <p>No stories found</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection