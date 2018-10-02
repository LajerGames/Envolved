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

        $newsItem = new NewsItem;
        $this->SaveRequest($newsItem, $story_id, $imageName, $request, $JSONArticle);

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
     * @param  int  $story_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($story_id, $id)
    {
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        // Det the newsitem
        $newsItem = $story->news->find($id);

        // Build sections
        // Loop through the article sections (JSON) and delete all images we find
        $article = json_decode($newsItem->article_json);

        $sections = '';
        if(is_array($article) && count($article) > 0) {
            
            // Loop through sections and delete any image we might find
            foreach($article as $section) {
                $sections .= $this->MakeNewsSection($section->type, $story_id, $section->content);
            }

        }

        $info = [
            'story' => $story,
            'news_item' => $newsItem,
            'sections' => $sections,
            'characters_list' => BuildSelectOptions::Build($story->characters->where('role', 'journalist'), 'id', ['first_name', 'middle_names', 'last_name'], ' ', 'None')
        ];

        return view('stories.modules.news.edit')->with('info', $info);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $story_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $story_id, $id)
    {
        $story = Story::find($story_id);

        if(!Permission::CheckOwnership(auth()->user()->id, $story->user_id))
            return redirect('/stories')->with('error', 'Access denied');

        $this->ValidateRequest($request);

        // If there are images to delete, then do it!
        if($request->input('remove_images') !== null && is_array($request->input('remove_images'))) {
            foreach($request->input('remove_images') as $imageName) {
                
                // Delete images
                HandleFiles::DeleteFile('public/stories/'.$story_id.'/news/'.$imageName);

            }
        }

        // Find news item
        $newsItem = $story->news->find($id);

        // Generate JSON article
        $JSONArticle = $this->JSONifyArticle($story->id, $request);

        // Delete old and upload new image
        $imageName = HandleFiles::DeleteThenUpload(
            $request,
            $newsItem,
            'image',
            'image',
            'public/stories/'.$story_id.'/news/'
        );

        $this->SaveRequest($newsItem, $story_id, $imageName, $request, $JSONArticle);

        return redirect('/stories/'.$story_id.'/modules/news')->with('success', 'News item created');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $story_id
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($story_id, $id)
    {
        $newsItem = NewsItem::find($id);
        
        // Check for access
        if(
            !Permission::CheckOwnership(auth()->user()->id, $newsItem->story->user->id)
        )
        {
            return redirect('/stories/'.$newsItem->story->id.'/modules/news')->with('error', 'Access denied');
        }

        // Delete the image first if there is any
        HandleFiles::DeleteFile(
            'public/stories/'.$newsItem->story->id.'/news/'.$newsItem->image,
            $newsItem,
            'image'
        );

        // Now loop through the article sections (JSON) and delete all images we find
        $article = json_decode($newsItem->article_json);

        if(is_array($article) && count($article) > 0) {
            
            // Loop through sections and delete any image we might find
            foreach($article as $section) {
                
                if($section->type == 'image') {

                    // Delete the image
                    HandleFiles::DeleteFile(
                        'public/stories/'.$newsItem->story->id.'/news/'.$section->content,
                        $newsItem,
                        'image'
                    );

                }

            }

        }

        // End section

        $newsItem->delete();

        return redirect('/stories/'.$newsItem->story->id.'/modules/news')->with('success', 'News item; '.$newsItem->headline .', deleted');
        
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

    public function MakeNewsSection($type, $story_id = 0, $content = '')
    {
        $uniqueID = uniqid();
        switch($type) {
            case 'headline' :
                $return = '
                    <input type="hidden" name="article['.$uniqueID.'][type]" value="'.$type.'" />
                    <label for="article['.$uniqueID.'][content]">Headline</label>
                    <input placeholder="Headline" name="article['.$uniqueID.'][content]" type="text" value="'.$content.'" id="article['.$uniqueID.'][content]" class="form-control">
                ';
                break;
            case 'sub_headline' :
                $return = '
                    <input type="hidden" name="article['.$uniqueID.'][type]" value="'.$type.'" />
                    <label for="article['.$uniqueID.'][content]">Sub headline</label>
                    <input placeholder="Sub headline" name="article['.$uniqueID.'][content]" type="text" value="'.$content.'" id="article['.$uniqueID.'][content]" class="form-control">
                ';
                break;
            case 'image' :

                $image = '';
                if(isset($content)) {
                    $image = '
                        <a href="/storage/stories/'.$story_id.'/news/'.$content.'" class="image-anchor" target="_blank" data-image-name="'.$content.'">'.$content.'</a>
                        <input type="hidden" name="article['.$uniqueID.'][saved]" value="'.$content.'" />
                    ';
                }

                $return = '
                    <input type="hidden" name="article['.$uniqueID.'][type]" value="'.$type.'" />
                    <label for="article['.$uniqueID.'][content]">Image</label>
                    <input name="article['.$uniqueID.'][content]" type="file" id="article['.$uniqueID.'][content]">
                    '.$image.'
                ';
                break;
            default :
                $return = '
                    <input type="hidden" name="article['.$uniqueID.'][type]" value="paragraph" />
                    <label for="article['.$uniqueID.'][content]">Paragraph</label>
                    <textarea placeholder="Paragraph" name="article['.$uniqueID.'][content]" id="article['.$uniqueID.'][content]" class="form-control">'.$content.'</textarea>
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
        if(isset($article) && !empty($article) && is_array($article['article']) && count($article['article']) > 0) {

            foreach($article['article'] as $uniqueID => $section) {
                
                // Ignore if content is empty, not need to do anything.
                if(empty($section['content']) && (!isset($section['saved'])))
                    continue;

                // What type of section is this?
                switch($section['type']) {
                    case 'image' :
                        
                        // Upload image and save filname
                        $imageName = HandleFiles::UploadFile(
                            $request,
                            'article.'.$uniqueID.'.content',
                            'public/stories/'.$story_id.'/news/'
                        );

                        // TODO: Check filetype, pretty important for when other ppl get access to this.

                        // If no file was saved check if we can just use saved data - otherwise; just mozy on!
                        if(empty($imageName)) {
                            if(!empty($section['saved'])) {
                                $imageName = $section['saved'];
                            } else {
                                continue;
                            }
                        }

                        // unset entry "saved" which might or might not be there
                        unset($section['saved']);

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
