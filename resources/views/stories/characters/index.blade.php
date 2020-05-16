@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Characters for: {{$story->title}}</div>
            <div class="panel-body">
                <table class="table">
                    <tr>
                        <th scope="col" class="icon"></th>
                        <th scope="col">First name</th>
                        <th scope="col">Middle names</th>
                        <th scope="col">Last name</th>
                        <th scope="col">Gender</th>
                        <th scope="col" class="icon text-center"><a href="/stories/{{$story->id}}/characters/create" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span></a></th>
                    </tr>
                    
                    @php
                        $role = '';
                        $characters = $story->characters;
                    @endphp
                    @if(count($characters) > 0)
                        @foreach($characters as $character)
                        
                            @if($role != $character->role)
                                <tr>
                                    <th scope="col" colspan="6" class="text-center divider-headline">
                                        @if($character->role == 'protagonist')
                                            {{ucfirst(str_replace('_', ' ', $character->role))}}
                                        @else
                                            {{str_plural(ucfirst(str_replace('_', ' ', $character->role)))}}
                                        @endif
                                    </th>
                                </tr>
                                @php
                                    $role = $character->role
                                @endphp
                            @endif
                            <tr>
                                <td scope="col">
                                    {!!Form::open([
                                        'action' => ['CharactersController@destroy', $story->id, $character->id, 'deleteImageOnly=false'],
                                        'method' => 'post'
                                    ])!!}
                                        {{Form::hidden('_method', 'DELETE')}}
                                        {{Form::submit('Delete', ['class' => 'btn btn-danger btn-delete'])}}
                                    {!!Form::close()!!}
                                </td>
                                <td scope="col">{{$character->first_name}}</td>
                                <td scope="col">{{$character->middle_names}}</td>
                                <td scope="col">{{$character->last_name}}</td>
                                <td scope="col">{{ucfirst($character->gender)}}</td>
                                <td scope="col"><a href="/stories/{{$story->id}}/characters/{{$character->id}}/edit" class="btn btn-primary">Edit</a></td>
                            </tr>
                            <!--a href="/stories/{{$character->id}}" class="show well">
                                <h3></h3>
                                <small>Last updated {{$character->updated_at}}</small>
                            </a-->
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center">No characters added</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection