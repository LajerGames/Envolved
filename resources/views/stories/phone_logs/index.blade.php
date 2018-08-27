@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Pre-game phone log for: {{$info['story']->title}}</div>
            <div class="panel-body">

                @php
                    $phoneLogs = $info['phone_logs'];
                    $currentDaysAgo = -1;
                @endphp
                <table class="table">
                    <tr>
                        <th scope="col" class="icon"></th>
                        <th scope="col">Character</th>
                        <th scope="col">Start time</th>
                        <th scope="col" class="text-right">Duration (min.)</th>
                        <th scope="col" class="text-right">Direction</th>
                    </tr>
                    @if(count($phoneLogs))
                        @foreach($phoneLogs as $phoneLog)
                            @php
                                $phoneNumber = $info['story']->phonenumber->find($phoneLog->phone_number_id);
                                $time = new \DateTime($phoneLog->start_time);
                            @endphp
                            @if($currentDaysAgo != $phoneLog->days_ago)

                                <tr>
                                    <th class="text-center divider-headline" colspan="5">{{$phoneLog->days_ago}} days ago</th>
                                </tr>

                                @php
                                    $currentDaysAgo = $phoneLog->days_ago;
                                @endphp
                            @endif
                            <tr>
                                <td>
                                    {!!Form::open([
                                        'action' => ['PhoneLogsController@destroy', $phoneLog->story_id, $phoneLog->id],
                                        'method' => 'post'
                                    ])!!}
                                        {{Form::hidden('_method', 'DELETE')}}
                                        {{Form::submit('Delete', ['class' => 'btn btn-danger btn-delete'])}}
                                    {!!Form::close()!!}
                                </td>
                                <td>
                                    <a href="/stories/{{$phoneNumber->story_id}}/phone_numbers/{{$phoneNumber->character_id}}/edit">{{$phoneNumber->number}}</a>
                                    ({!!($phoneNumber->character_id > 0 ? '<a href="/stories/'.$phoneNumber->story_id.'/characters/'.$phoneNumber->character_id.'/edit">'.$phoneNumber->name.'</a>' : $phoneNumber->name)!!})</td>
                                <td>{{$time->format('H:i')}}</td>
                                <td class="text-right">{{$phoneLog->minutes}}</td>
                                <td class="text-right">{{(ucfirst($phoneLog->direction))}}</td>
                            </tr>
                        @endforeach
                    @endif
                </table>

                <div class="spacer">  

                    {!! Form::open([
                        'action'    => ['PhoneLogsController@store', $info['story']->id],
                        'method'    => 'post',
                        'class'     => 'phonelog-form onload-anchor'
                    ]) !!}

                        <div class="form-group">
                            {{Form::label('phone_number_id', 'Number (Character)')}}
                            {{Form::select('phone_number_id', $info['phone_numbers_select'], '', ['class' => 'form-control'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('days_ago', 'Days ago')}}
                            {{Form::number('days_ago', $info['days_ago'], ['class' => 'form-control', 'min'=> '0'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('time', 'Time')}}
                            {{Form::time('time', $info['time']->format('H:i'), ['class' => 'form-control'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('minutes', 'Minutes')}}
                            {{Form::number('minutes', 5, ['class' => 'form-control'])}}
                        </div>

                        <div class="form-group">
                            {{Form::label('direction', 'Direction')}}
                            {{Form::select('direction', ['in' => 'In', 'out' => 'Out'], '', ['class' => 'form-control'])}}
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