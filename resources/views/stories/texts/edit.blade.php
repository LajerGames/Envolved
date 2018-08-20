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
                    $phoneNumber = $info['phone_number'];
                    $texts = $info['texts'];
                    $textEdit = $info['text'];
                    $sentOnDate = $info['sent_on_date'];

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
                                    {!!nl2br($text->text)!!}
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @else
                    <p>No texts added</p>
                @endif
                <div class="message-text-clear">
                    
                        
                        @if(intval($info['edit_id']) == 0)
                            @include('stories.texts.include.form', ['text' => '', 'phone_number' => $phoneNumber, 'sent_on' => $sentOnDate->format('Y-m-d\TH:i'), 'isEdit' => false])
                        @else
                            @include('stories.texts.include.form', ['text' => $textEdit, 'phone_number' => $phoneNumber, 'sent_on' => $sentOnDate->format('Y-m-d\TH:i'), 'isEdit' => true])
                        @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection