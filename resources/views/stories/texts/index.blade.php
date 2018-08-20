@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Pre-existing texts for: {{$story->title}}</div>
            <div class="panel-body">
                <table class="table">
                    <tr>
                        <th scope="col">Phone number</th>
                        <th scope="col">Name</th>
                        <th scope="col">Texts</th>
                        <th scope="col" class="icon"></th>
                    </tr>
                    
                    @php
                        $phoneNumbers = $story->phonenumber;
                    @endphp
                    @if(count($phoneNumbers) > 0)
                        @foreach($phoneNumbers as $phoneNumber)
                            <tr>
                                <td scope="col"><a href="/stories/{{$phoneNumber->story_id}}/phone_numbers/{{$phoneNumber->id}}/edit">{{$phoneNumber->number}}</a></td>
                                <td scope="col">
                                    @if($phoneNumber->character_id > 0)
                                        <a href="/stories/{{$phoneNumber->story_id}}/characters/{{$phoneNumber->character_id}}/edit">{{$phoneNumber->name}}</a></td> 
                                    @else
                                        {{$phoneNumber->name}}
                                    @endif
                                </td>
                                <td scope="col">{{count($phoneNumber->texts)}}</td>
                                <td scope="col"><a href="/stories/{{$story->id}}/texts/{{$phoneNumber->id}}/edit" class="btn btn-primary">Edit texts</a></td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="text-center">No numbers added</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection