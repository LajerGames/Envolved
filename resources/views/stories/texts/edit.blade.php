@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Edit pre-story texts</div>
            <div class="panel-body">
                @php
                    $story = $info['story'];
                    $phoneNumberID = $info['id'];
                    $phoneNumber = $story->phonenumber->find($phoneNumberID);
                    $texts = $phoneNumber->texts;
                @endphp
                @if(count($texts) > 0)
                    @foreach($texts as $text)
                    <div>
                        <div class="
                            text-message
                            {{($text->sender == 'protagonist' ? 'text-from-protagonist' : 'text-from-number')}}
                            {{(!empty($text->filename) ? 'text-center' : '')}}
                        ">
                            @if(!empty($text->filename))
                                @if($text->filetype == 'video')
                                <video width="100%" controls>
                                    <source src="/storage/stories/{{$story->id}}/texts/{{$text->filename}}" type="{{$text->filemime}}">
                                    Your browser does not support the video tag.
                                    </video>
                                @elseif($text->filetype == 'image')
                                    <a href="/storage/stories/{{$story->id}}/texts/{{$text->filename}}" target="_blank"><img src="/storage/stories/{{$story->id}}/texts/{{$text->filename}}" class="text-image" alt="" /></a>
                                @endif
                            @else
                                {{$text->text}}
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
                        <div class="form-group">
                            {{Form::label('sender', 'Sender')}}
                            {{Form::select('sender', ['protagonist' => 'Protagonist', 'number' => 'Number'], '', ['class' => 'form-control'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('text', 'Message')}}
                            {{Form::textArea('text', '', ['class' => 'form-control text-textarea'])}}
                        </div>

                        <div class="form-group">
                            {{Form::file('mms')}}
                        </div>
                        
                        <a href="/stories/{{$info['story']->id}}/texts" class="btn btn-default">Back</a>
                        {{Form::submit('save', ['class' => 'btn btn-primary pull-right'])}}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection