@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Edit News Item</div>
            
            <div class="panel-body">
                
                {!! Form::open(['action' => ['NewsController@update', $info['story']->id, $info['news_item']->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}

                    @include(
                        'stories.modules.news.include.inputs',
                        [
                            'characters_list' => $info['characters_list'],
                            'news_item' => $info['news_item'],
                            'sections' => $info['sections']
                        ]
                    )
                    
                    <a href="/stories/{{$info['story']->id}}/modules/news" class="btn btn-default">Back</a>
                    {{Form::submit('Update', ['class' => 'btn btn-primary pull-right'])}}
                    {{Form::hidden('_method', 'PUT')}}
                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>
@endsection