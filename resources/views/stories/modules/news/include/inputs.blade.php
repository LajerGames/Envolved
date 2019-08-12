<div class="form-group">
    {{Form::label('character_id', 'Journalist')}}
    {{Form::select('character_id', $characters_list, (isset($news_item) ? $news_item->character_id : ''), ['class' => 'form-control'])}}
</div>

<div class="form-group">
    {{Form::label('headline', 'Headline')}}
    {{Form::text('headline', (isset($news_item) ? $news_item->headline : ''), ['class' => 'form-control', 'placeholder' => 'Headline'])}}
</div>

<div class="form-group">
    {{Form::label('image', 'Image')}}
    {{Form::file('image')}}
    @if(isset($news_item))
    <a href="/storage/stories/{{$news_item->story_id}}/news/{{$news_item->image}}" target="_blank">{{$news_item->image}}</a>
    @endif
</div>

<div class="form-group">
    {{Form::label('teaser_text', 'Teaser text')}}
    {{Form::textArea('teaser_text', (isset($news_item) ? $news_item->teaser_text : ''), ['class' => 'form-control', 'placeholder' => 'Article teaser text for app frontpage', 'rows' => 3])}}
</div>

<div class="form-group">
    {{Form::label('published', 'Published')}}
    {{Form::select('published', [1 => 'Yes', 0 => 'No'], (isset($news_item) ? $news_item->published : 0), ['class' => 'form-control'])}}
</div>

<div class="form-group">
    {{Form::label('days_ago', 'Days ago')}}
    {{Form::number(
        'days_ago',
        (isset($news_item) ? $news_item->days_ago : ''),
        [
            'class' => 'form-control',
            'placeholder' => 'Days ago',
            'disabled' => (isset($news_item) && $news_item->published == 1 ? false : true)
        ]
    )}}
</div>

<div class="form-group">
    {{Form::label('time', 'Time')}}
    {{Form::time(
        'time',
        (isset($news_item) ? $news_item->time : ''),
        [
            'class' => 'form-control',
            'disabled' => (isset($news_item) && $news_item->published == 1 ? false : true)
        ]
    )}}
</div>

<h2>Build article</h2>

<div class="section">
    
    <div class="form-group tiny-section">
        {{Form::label('add_section', 'Add section')}} <span class="greyd">(Don't make any mistakes, article sections arent saved on error)</span><br />
        {{Form::select('add_section', ['headline' => 'Headline', 'sub_headline' => 'Sub headline', 'image' => 'Image', 'paragraph' => 'Paragraph'], '', ['class' => 'form-control add-section-select'])}}
        <a class="btn btn-default add-section-button">Add</a>
    </div>

    <div id="article-parts" class="indent-20 sortable">
        @if(!empty($sections))
            {!! $sections !!}
        @endif
    </div>

</div>