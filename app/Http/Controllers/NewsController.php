<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Story;
use App\NewsItem;
use App\Common\Permission;
use App\Common\BuildSelectOptions;
use App\Common\HandleFiles;
use App\Rules\ValidFile;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  int  $story_id
     * @return \Illuminate\Http\Response
     */
    public function index($story_id)
    {
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        return view('stories.modules.news.index')->with('story', $story);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  int  $story_id
     * @return \Illuminate\Http\Response
     */
    public function create($story_id)
    {
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        $info = [
            'story' => $story,
            'characters_list' => BuildSelectOptions::Build($story->characters->where('role', 'journalist'), 'id', ['first_name', 'middle_names', 'last_name'], ' ', 'None')
        ];

        return view('stories.modules.news.create')->with('info', $info);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $story_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($story_id, Request $request)
    {
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        $this->ValidateRequest($request);

        // Generate JSON article
        $JSONArticle = $this->JSONifyArticle($story->id, $request);

        // Upload image
        $imageName = HandleFiles::UploadFile(
            $request,
            'image',
            'public/stories/'.$story_id.'/news/'
        );

        $newItem = new NewsItem;
        $this->SaveRequest($newItem, $story_id, $imageName, $request, $JSONArticle);

        return redirect('/stories/'.$story_id.'/modules/news')->with('success', 'News item created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Validation for both store and update is the same, so we'll just call this method
     */
    public function ValidateRequest(Request $request)
    {
        $this->validate($request, [
            'character_id' => 'required|not_in:0',
            'headline' => 'required',
            'image' => [new ValidFile(true, false)],
            'teaser_text' => 'required',
            'days_ago' => 'required',
            'time' => 'required'
        ]);
    }

    /**
     * Once News Item is instanciated or found (::find), the saving process is the same for both store and update
     */
    public function SaveRequest(NewsItem $newsItem, $story_id, $imageName, Request $request, $JSONArticle)
    {
        $newsItem->story_id =  $story_id;
        $newsItem->character_id = "{$request->input('character_id')}";
        $newsItem->headline = "{$request->input('headline')}";
        if(!empty($imageName))
            $newsItem->image = "{$imageName}";
        $newsItem->teaser_text = "{$request->input('teaser_text')}";
        $newsItem->article_json = "{$JSONArticle}";
        $newsItem->days_ago = "{$request->input('days_ago')}";
        $newsItem->time = "{$request->input('time')}";
        $newsItem->save();
    }

    public function MakeNewsSection($type)
    {
        $uniqueID = uniqid();
        switch($type) {
            case 'headline' :
                $return = '
                    <input type="hidden" name="article['.$uniqueID.'][type]" value="'.$type.'" />
                    <label for="article['.$uniqueID.'][content]">Headline</label>
                    <input placeholder="Headline" name="article['.$uniqueID.'][content]" type="text" value="" id="article['.$uniqueID.'][content]" class="form-control"
                ';
                break;
            case 'sub_headline' :
                $return = '
                    <input type="hidden" name="article['.$uniqueID.'][type]" value="'.$type.'" />
                    <label for="article['.$uniqueID.'][content]">Sub headline</label>
                    <input placeholder="Sub headline" name="article['.$uniqueID.'][content]" type="text" value="" id="article['.$uniqueID.'][content]" class="form-control">
                ';
                break;
            case 'image' :
                $return = '
                    <input type="hidden" name="article['.$uniqueID.'][type]" value="'.$type.'" />
                    <label for="article['.$uniqueID.'][content]">Image</label>
                    <input name="article['.$uniqueID.'][content]" type="file" id="article['.$uniqueID.'][content]">
                ';
                break;
            default :
                $return = '
                    <input type="hidden" name="article['.$uniqueID.'][type]" value="paragraph" />
                    <label for="article['.$uniqueID.'][content]">Paragraph</label>
                    <textarea placeholder="Paragraph" name="article['.$uniqueID.'][content]" id="article['.$uniqueID.'][content]" class="form-control"></textarea>
                ';
                break;
        }

        return '
            <div class="form-group">
                <a href="javascript:void(0);" class="remove-link">Remove</a>
                '.$return.'
            </div>
        ';
    }

    private function JSONifyArticle($story_id, $request) {

        $article = $request->all('article');
        $sections = [];

        // Loop through all sections in the article and create a
        if(!empty($article) && count($article['article']) > 0) {

            foreach($article['article'] as $uniqueID => $section) {

                // Ignore if content is empty, not need to do anything.
                if(empty($section['content']))
                    continue;

                // What type of section is this?
                switch($section['type']) {
                    case 'headline' :
                        $sections[] = $section;
                        break;
                    case 'sub_headline' :
                        $sections[] = $section;
                        break;
                    case 'image' :
                        
                        // Upload image and save filname
                        $imageName = HandleFiles::UploadFile(
                            $request,
                            'article.'.$uniqueID.'.content',
                            'public/stories/'.$story_id.'/news/'
                        );

                        // TODO: Check filetype, pretty important for when other ppl get access to this.

                        // If no file was saved, just mozy on!
                        if(empty($imageName))
                            continue;

                        // Set the content to the image name since it's been uploaded
                        $section['content'] = $imageName;

                        $sections[] = $section;
                        break;
                    default :
                        $sections[] = $section;
                        break;
                }

            }

        }

        return json_encode($sections);
    }
}
