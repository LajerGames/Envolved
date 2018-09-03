@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Create News Item</div>
            
            <div class="panel-body">
                
                {!! Form::open(['action' => ['NewsController@store', $info['story']->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}

                    <div class="form-group">
                        {{Form::label('character_id', 'Journalist')}}
                        {{Form::select('character_id', $info['characters_list'], '', ['class' => 'form-control'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('headline', 'Headline')}}
                        {{Form::text('headline', '', ['class' => 'form-control', 'placeholder' => 'Headline'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('image', 'Image')}}
                        {{Form::file('image')}}
                    </div>

                    <div class="form-group">
                        {{Form::label('teaser_text', 'Teaser text')}}
                        {{Form::textArea('teaser_text', '', ['class' => 'form-control', 'placeholder' => 'Article teaser text for app frontpage', 'rows' => 3])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('days_ago', 'Days ago')}}
                        {{Form::number('days_ago', '', ['class' => 'form-control', 'placeholder' => 'Days ago'])}}
                    </div>

                    <div class="form-group">
                        {{Form::label('time', 'Time')}}
                        {{Form::time('time', '', ['class' => 'form-control'])}}
                    </div>

                    <h2>Build article</h2>

                    <div class="section">
                        
                        <div class="form-group tiny-section">
                            {{Form::label('add_section', 'Add section')}}<br />
                            {{Form::select('add_section', ['headline' => 'Headline', 'sub_headline' => 'Sub headline', 'image' => 'Image', 'paragraph' => 'Paragraph'], '', ['class' => 'form-control add-section-select'])}}
                            <a class="btn btn-default add-section-button">Add</a>
                        </div>

                        <div id="article-parts" class="indent-20 sortable"></div>

                    </div>
                    
                    <a href="/stories/{{$info['story']->id}}/modules/news" class="btn btn-default">Back</a>
                    {{Form::submit('Create', ['class' => 'btn btn-primary pull-right'])}}
                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>
@endsection