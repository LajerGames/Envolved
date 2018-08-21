@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Pre-game photos for: {{$story->title}}</div>
            <div class="panel-body">
                @php
                    $photos = $story->photos;
                    $chosenDate = isset($_GET['date']) ? $_GET['date'] : now();
                    $newestID = 0;
                    $currentLoopDate = '';
                @endphp
                @if(count($photos) > 0)
                    @foreach($photos as $photo)
                        @php
                            $takenOn = new \DateTime($photo->taken_on);
                        @endphp
                        @if($takenOn->format('Y-m-d') != $currentLoopDate)
                            @php
                                $currentLoopDate = $takenOn->format('Y-m-d');
                            @endphp
                            
                            <div class="divider-headline text-center">
                                <a href="/stories/{{$photo->story_id}}/photos/?date={{$takenOn->format('Y-m-d 06:00')}}" class="pull-right btn btn-success more-button"><span class="glyphicon glyphicon-plus"></span></a>
                                {{$currentLoopDate}}
                            </div>

                        @endif
                        <div
                            class="photos-image-container control-buttons-parent"
                            style="background-image:url('/storage/stories/{{$photo->story_id}}/photos/{{$photo->image_name}}')
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
                            <a href="/stories/{{$photo->story_id}}/photos/?date={{$takenOn->format('Y-m-d H:i')}}" class="image-time-container">{{$takenOn->format('H:i')}}</a>
                        </div>
                        @php
                            if($photo->id > $newestID && !isset($_GET['date'])) {
                                $newestID = $photo->id; 
                                $chosenDate = $photo->taken_on;
                            }
                        @endphp
                    @endforeach
                @else
                    NO PHOTOS ADDED
                @endif
                <div class="spacer">  

                    {!! Form::open([
                        'action'    => ['PhotosController@store', $story->id],
                        'method'    => 'post',
                        'enctype'   => 'multipart/form-data',
                        'class'     => 'photos-form onload-anchor'
                    ]) !!}

                        <div class="form-group">
                            @php
                                $chosenDate = new \DateTime($chosenDate);
                                $chosenDate->add(new \DateInterval('PT10M'));
                            @endphp
                            {{Form::label('taken_on', 'Taken on')}}
                            {{Form::dateTimeLocal('taken_on', $chosenDate->format('Y-m-d\TH:i'), ['class' => 'form-control'])}}
                        </div>

                        <div class="form-group">
                            {{Form::file('photo')}}
                        </div>

                        <a href="/stories/{{$story->id}}/texts" class="btn btn-default">Back</a>

                        {{Form::submit('Save', ['class' => 'btn btn-primary pull-right'])}}

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection