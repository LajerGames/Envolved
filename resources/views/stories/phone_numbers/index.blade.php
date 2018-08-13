@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Numbers for: {{$story->title}}</div>
            <div class="panel-body">
                <table class="table">
                    <tr>
                        <th scope="col" class="icon"></th>
                        <th scope="col">Phone number</th>
                        <th scope="col">Name (Just for Editor)</th>
                        <th scope="col">Character conn.</th>
                        <th scope="col" class="icon text-center"><a href="/stories/{{$story->id}}/phone_numbers/create" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span></a></th>
                    </tr>
                    
                    @php
                        $phoneNumbers = $story->phonenumber;
                    @endphp
                    @if(count($phoneNumbers) > 0)
                        @foreach($phoneNumbers as $phoneNumber)
                        
                            <tr>
                                <td scope="col">
                                    {!!Form::open([
                                        'action' => ['PhoneNumbersController@destroy', $story->id, $phoneNumber->id],
                                        'method' => 'post'
                                    ])!!}
                                        {{Form::hidden('_method', 'DELETE')}}
                                        {{Form::submit('Delete', ['class' => 'btn btn-danger btn-delete'])}}
                                    {!!Form::close()!!}
                                </td>
                                <td scope="col">{{$phoneNumber->number}}</td>
                                <td scope="col">{{$phoneNumber->name}}</td>
                                <td scope="col">{{($phoneNumber->character_id > 0 ? 'Yes' : 'No')}}</td>
                                <td scope="col"><a href="/stories/{{$story->id}}/phone_numbers/{{$phoneNumber->id}}/edit" class="btn btn-primary">Edit</a></td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center">No numbers added</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection