<div class="form-group">
    {{Form::label('sender', 'Sender')}}
    {{Form::select('sender', ['protagonist' => 'Protagonist', 'number' => 'Number'], ($isEdit ? $text->sender : ''), ['class' => 'form-control'])}}
</div>

<div class="form-group">
    {{Form::label('sent_on', 'Sent on')}}
    {{Form::dateTimeLocal('sent_on', $sent_on, ['class' => 'form-control'])}}
</div>

<div class="form-group">
    {{Form::label('text', 'Message')}}
    {{Form::textArea('text', ($isEdit ? $text->text : ''), ['class' => 'form-control text-textarea', 'readonly' => ($isEdit && !empty($text->filename))])}}
</div>

<div class="form-group">
    {{Form::file('mms', ['disabled' => ($isEdit && empty($text->filename))])}}
</div>
@if(!empty($text->filename))
    <div class="image-line">
        File: (<strong>{{(ucfirst($text->filetype))}}</strong>) <a href="/storage/stories/{{$phone_number->story_id}}/texts/{{$text->filename}}" target="_blank">{{$text->filename}}</a>
    </div>
@endif