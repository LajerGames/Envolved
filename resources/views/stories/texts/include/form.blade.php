{!! Form::open([
    'action' => [
        ($isEdit ? 'TextsController@update' : 'TextsController@store'),
        $phone_number->story_id,
        ($isEdit ? $phone_number->id : 'phone_number_id='.$phone_number->id),
        'id='.($isEdit ? $text->id : 0)
    ],
    'method' => 'post',
    'enctype'   => 'multipart/form-data',
    'class' => 'texts-form onload-anchor'
]) !!}

@include('stories.texts.include.inputs', ['text' => $text, 'days_ago' => $days_ago, 'time' => $time, 'isEdit' => $isEdit])

<a href="/stories/{{$story->id}}/texts" class="btn btn-default">Back</a>
{{Form::submit(($isEdit ? 'Update' : 'Save'), ['class' => 'btn btn-primary pull-right'])}}
@if($isEdit)
    <a href="/stories/{{$story->id}}/texts/{{$phone_number->id}}/edit" class="btn btn-success new-text pull-right">New</a>
@endif
@if($isEdit)
{{Form::hidden('_method', 'PUT')}}
@endif
{!! Form::close() !!}