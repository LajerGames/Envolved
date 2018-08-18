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
                        <th scope="col">texts</th>
                        <th scope="col" class="icon"></th>
                    </tr>
                    
                    @php
                        $phoneNumbers = $story->phonenumber;
                    @endphp
                    @if(count($phoneNumbers) > 0)
                        @foreach($phoneNumbers as $phoneNumber)
                            <tr>
                                <td scope="col">{{$phoneNumber->number}}</td>
                                <td scope="col">{{$phoneNumber->name}}</td>
                                <td scope="col">0</td>
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