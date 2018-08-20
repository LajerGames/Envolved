
<div class="form-group">
    {{Form::label('sender', 'Sender')}}
    {{Form::select('sender', ['protagonist' => 'Protagonist', 'number' => 'Number'], '', ['class' => 'form-control'])}}
</div>

<div class="form-group">
    {{Form::label('sent_on', 'Sent on')}}
    {{Form::dateTimeLocal('sent_on', $sent_on, ['class' => 'form-control'])}}
</div>

<div class="form-group">
    {{Form::label('text', 'Message')}}
    {{Form::textArea('text', '', ['class' => 'form-control text-textarea'])}}
</div>

<div class="form-group">
    {{Form::file('mms')}}
</div>