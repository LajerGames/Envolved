@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Edit pre-story texts</div>
            <div class="panel-body">
                @php
                    $story = $info['story'];
                    $phoneNumberID = $info['phone_number_id'];
                    $phoneNumber = $story->phonenumber->find($phoneNumberID);
                    $texts = $phoneNumber->texts;

                    // Sorry, shouldn't belong here, but it's here... !
                    $sentOnNewest = now();
                    $iNewestID = 0;
                    foreach($texts as $text) {
                        if($text->id > $iNewestID) {
                            $sentOnNewest = $text->seen_on;
                            $iNewestID = $text->id;
                        }
                    }
                    $sentOnNewest = new DateTime($sentOnNewest);
                    $sentOnNewest->add(new DateInterval('PT2M'));
                @endphp
                @if(count($texts) > 0)
                    @foreach($texts as $text)
                    <div>
                        <div class="
                            text-message
                            {{($text->sender == 'protagonist' ? 'text-from-protagonist' : 'text-from-number')}}
                        ">
                        <div class="control-buttons">
                            <div>
                                <a href="/stories/{{$info['story']->id}}/texts/{{$phoneNumberID}}/edit/{{$text->id}}" class="btn btn-default">Edit</a>
                            </div>
                            <div>
                                {!!Form::open([
                                    'action' => ['TextsController@destroy', $story->id, $phoneNumberID, 'textID='.$text->id],
                                    'method' => 'post'
                                ])!!}
                                    {{Form::hidden('_method', 'DELETE')}}
                                    {{Form::submit('Delete', ['class' => 'btn btn-danger btn-delete'])}}
                                {!!Form::close()!!}
                            </div>
                        </div>
                            <div class="pull-right">{{$text->sent_on}}</div>
                            @if(!empty($text->filename))
                                <div class="text-center clear-both">
                                    @if($text->filetype == 'video')
                                    <video width="100%" controls>
                                        <source src="/storage/stories/{{$story->id}}/texts/{{$text->filename}}" type="{{$text->filemime}}">
                                        Your browser does not support the video tag.
                                    </video>
                                    @elseif($text->filetype == 'image')
                                        <a href="/storage/stories/{{$story->id}}/texts/{{$text->filename}}" target="_blank"><img src="/storage/stories/{{$story->id}}/texts/{{$text->filename}}" class="text-image" alt="" /></a>
                                    @endif
                                </div>
                            @else
                                <div class="clear-both">
                                    {{$text->text}}
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @endif
                <div class="message-text-clear">
                    {!! Form::open([
                        'action' => ['TextsController@store', $story->id, 'phone_number_id='.$phoneNumberID],
                        'method' => 'post',
                        'enctype'   => 'multipart/form-data',
                        'class' => 'texts-form onload-anchor'
                    ]) !!}
                        
                        @if(intval($info['edit_id']) == 0)
                            @include('stories.texts.include.form', ['sent_on' => $sentOnNewest->format('Y-m-d\TH:i')])
                        @else
                            @php
                                $text = $phoneNumber->texts->find($info['edit_id']);
                            @endphp

                            @include('stories.texts.include.form', ['text' => $text])
                        @endif
                        
                        <a href="/stories/{{$info['story']->id}}/texts" class="btn btn-default">Back</a>
                        {{Form::submit('save', ['class' => 'btn btn-primary pull-right'])}}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection