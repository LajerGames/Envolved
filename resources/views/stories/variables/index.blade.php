@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Variables for: {{$story->title}}</div>
            <div class="panel-body">
                <table class="table">
                    <tr>
                        <th scope="col" class="icon"></th>
                        <th scope="col">Key</th>
                        <th scope="col">Default value</th>
                        <th scope="col" class="icon text-center"><a href="/stories/{{$story->id}}/variables/create" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span></a></th>
                    </tr>
                    
                    @php
                        $type = '';
                        $variables = $story->variables;
                    @endphp
                    @if(count($variables) > 0)
                        @foreach($variables as $variable)
                        
                            @if($type != $variable->type)
                                <tr>
                                    <th scope="col" colspan="5" class="text-center divider-headline">
                                        {{ucfirst($variable->type)}}
                                    </th>
                                </tr>
                                @php
                                    $type = $variable->type;
                                @endphp
                            @endif
                            <tr>
                                <td scope="col">
                                    {!!Form::open([
                                        'action' => ['VariablesController@destroy', $story->id, $variable->id],
                                        'method' => 'post'
                                    ])!!}
                                        {{Form::hidden('_method', 'DELETE')}}
                                        {{Form::submit('Delete', ['class' => 'btn btn-danger btn-delete'])}}
                                    {!!Form::close()!!}
                                </td>
                                <td scope="col">{{$variable->key}}</td>
                                <td scope="col">{{$variable->value}}</td>
                                <td scope="col"><a href="/stories/{{$story->id}}/variables/{{$variable->id}}/edit" class="btn btn-primary">Edit</a></td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center">No variables added</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection