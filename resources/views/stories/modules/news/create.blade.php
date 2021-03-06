@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Create News Item</div>
            
            <div class="panel-body">
                
                {!! Form::open(['action' => ['NewsController@store', $info['story']->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}

                    @include(
                        'stories.modules.news.include.inputs',
                        [
                            'characters_list' => $info['characters_list']
                        ]
                    )
                    
                    <a href="/stories/{{$info['story']->id}}/modules/news" class="btn btn-default">Back</a>
                    {{Form::submit('Create', ['class' => 'btn btn-primary pull-right'])}}
                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>
@endsection