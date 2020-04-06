@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Pre-game photos for: {{$info['story']->title}}</div>
            <div class="panel-body">
                @php
                    $photos = $info['photos'];
                    $chosenTime = $info['time']->format('H:i');
                    $daysAgo = $info['days_ago'];
                    $currentDay = -1;
                @endphp
                @if(count($photos) > 0)
                    @foreach($photos as $photo)

                        @php
                            $createdOn = new \DateTime($photo->time)
                        @endphp
                       
                        @if($currentDay != $photo->days_ago)
                            <div class="divider-headline text-center">
                                <a href="/stories/{{$photo->story_id}}/photos/?days_ago={{$photo->days_ago}}&pre-time=12:00" class="pull-right btn btn-success more-button"><span class="glyphicon glyphicon-plus"></span></a>
                                {{$photo->days_ago}} day{{($photo->days_ago == 1 ? '' : 's')}} ago.
                            </div>
                            @php
                                $currentDay = $photo->days_ago;
                            @endphp
                        @endif
                        <div
                            class="photos-image-container control-buttons-parent"
                            style="background-image:url('/storage/stories/{{$photo->story_id}}/photos/{{$photo->image_path}}')
                        ">
                            <div class="control-buttons">
                                <div>
                                    {!!Form::open([
                                        'action' => ['PhotosController@destroy', $photo->story_id, $photo->id],
                                        'method' => 'post'
                                    ])!!}
                                        {{Form::hidden('_method', 'DELETE')}}
                                        {{Form::submit('Delete', ['class' => 'btn btn-danger btn-delete'])}}
                                    {!!Form::close()!!}
                                </div>
                            </div>
                            <a href="/stories/{{$photo->story_id}}/photos/?days_ago={{$photo->days_ago}}&pre-time={{$createdOn->format('H:i')}}" class="image-time-container">{{$createdOn->format('H:i')}}</a>
                        </div>
                    @endforeach
                @else
                    <p>No photos yet</p>
                @endif
                <div class="spacer">  

                    {!! Form::open([
                        'action'    => ['PhotosController@store', $info['story']->id],
                        'method'    => 'post',
                        'enctype'   => 'multipart/form-data',
                        'class'     => 'photos-form onload-anchor'
                    ]) !!}

                        <div class="form-group">
                            {{Form::label('days_ago', 'Days ago')}}
                            {{Form::number('days ago', $daysAgo, ['class' => 'form-control', 'min'=> '0'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('time', 'Time')}}
                            {{Form::time('time', $chosenTime, ['class' => 'form-control'])}}
                        </div>

                        <div class="form-group">
                            {{Form::file('photo')}}
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