@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">News for: {{$story->title}}</div>
            <div class="panel-body">
                <table class="table">
                    <tr>
                        <th scope="col" class="icon"></th>
                        <th scope="col">Headline</th>
                        <th scope="col">Days ago</th>
                        <th scope="col">Time</th>
                        <th scope="col" class="icon text-center"><a href="/stories/{{$story->id}}/modules/news/create" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span></a></th>
                    </tr>
                    
                    @php
                        $role = '';
                        $news = $story->news;
                    @endphp
                    @if(count($news) > 0)
                        @foreach($news as $newsItem)
                        
                            <tr>
                                <td scope="col">
                                    {!!Form::open([
                                        'action' => ['NewsController@destroy', $story->id, $newsItem->id],
                                        'method' => 'post'
                                    ])!!}
                                        {{Form::hidden('_method', 'DELETE')}}
                                        {{Form::submit('Delete', ['class' => 'btn btn-danger btn-delete'])}}
                                    {!!Form::close()!!}
                                </td>
                                <td scope="col">{{$newsItem->headline}}</td>
                                <td scope="col">{{$newsItem->days_ago}}</td>
                                <td scope="col">{{$newsItem->time}}</td>
                                <td scope="col"><a href="/stories/{{$story->id}}/modules/news/{{$newsItem->id}}/edit" class="btn btn-primary">Edit</a></td>
                            </tr>
                            
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center">No news added</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection