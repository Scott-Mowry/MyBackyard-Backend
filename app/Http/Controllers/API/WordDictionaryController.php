<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WordDictionary;
use App\Models\UploadFileModel;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\UserFavoriteWords;
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\BaseController;


class WordDictionaryController extends BaseController
{
    public function getFileSearch(Request $request)
    {
        if(!$request->keyword){
            
            return response()->json([
                'status' => 0,
                'message' => 'Word(s) not found...!'
            ]);
            
        }   
        
        $keyword = $request->keyword;
        $type = $request->type;
        
        // [['name', 'LIKE', '%' . $keyword . '%'],['type','=',$type]]
        
        $words = UploadFileModel::when($request->keyword, function($q) use ($keyword, $type){
                $q->where([['name', 'LIKE', $keyword . '%'],['type','=',$type]]);
        })->take(10)->get();
        
        $total= UploadFileModel::count();

        return response()->json([
                'status' => 1,
                'message' => 'Word(s) found.',
                'data' => $words
            ]);
    }
    
    public function getFile(Request $request)
    {
        if(!$request->id){
            
            return response()->json([
                'status' => 0,
                'message' => 'Word not found...!'
            ]);
            
        }   
        
        $id = $request->id;
        
        $word = UploadFileModel::where('id', '=', $id)->first();
        
        if($word==null){
            
            return response()->json([
                'status' => 0,
                'message' => 'Word not found...!'
            ]);
            
        } 
        
        $total= UploadFileModel::count();
        $next=UploadFileModel::where([['type', '=', $word['type']],['name', 'LIKE', mb_substr($word['name'], 0, 1) . '%'],['id','>',$id]])->get();
        $prev=UploadFileModel::where([['type', '=', $word['type']],['name', 'LIKE', mb_substr($word['name'], 0, 1) . '%'],['id','<',$id]])->get();
        if($next!=null)
        {
            $last=$next->last();
            $word['next']=$last!=null?$last->id:null;
        } else {
            $word['next']=null;
        }
        
        if($prev!=null)
        {
            $last=$prev->last();
            $word['previous']=$last!=null?$last->id:null;
        } else {
            $word['previous']=null;
        }

        return response()->json([
                'status' => 1,
                'message' => 'Word found.',
                'data' => $word ]);
    }
    
    public function requestWord(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'word' => 'required|unique:words_dictionary',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError("$validator->errors()->first()");
        }

        if (($request->language == null) || ($request->language == '')) {
            $language = 'en';
        } else {
            $language = $request->language;
        }

        $requestedBy = auth()->user()->id;
        $newWord = new WordDictionary();
        $newWord->category_id = $request->category_id;
        $newWord->language = $language;
        $newWord->word = $request->word;
        $newWord->description = $request->word;
        $newWord->description = $request->description;
        $newWord->requested_by = $requestedBy;

        if ($newWord->save()) {
            return $this->sendResponse(array(
                "word" => $newWord
            ), 'Your word request has been sent to admin for approval successfully.');
        } else {
            return $this->sendError("Unable to process your request at the moment.");
        }
    }


    public function searchWord(Request $request)
    {
        $word = $request->word;
        $category_id = $request->category_id;

        $wordDictionary = new WordDictionary();
        $words = $wordDictionary->findByWordAndCategory($word, $category_id);

        if (count($words) >= 1) {
            return response()->json([
                'status' => 1,
                'message' => 'Word(s) found.',
                'data' => $words,
            ]);
        } else {

            return response()->json([
                'status' => 0,
                'message' => 'Word(s) not found...!'
            ]);
        }
    }

    public function wordData(Request $request)
    {
        $wordID = $request->word_id;

        $word = WordDictionary::with('wordCategory')->find($wordID);

        if (!$word) {
            return response()->json([
                'status' => 0,
                'message' => 'Word not found.',
            ]);
        }

        $userID = auth()->user()->id;
        $userFavCheck = UserFavoriteWords::where('word_dictionary_id', '=', $wordID)
            ->where('user_id', '=', $userID)
            ->where('is_favorite', 1)
            ->first();

        $word->is_favorite = $userFavCheck ? 1 : 0;

        $wordData = [];
        $wordDataTypes = ['noun', 'pronoun', 'adjective', 'verb']; // Add other types if needed

        foreach ($wordDataTypes as $type) {
            $definitions = $word->word_Data->where('word_data_type', $type)->pluck('word_data_text')->toArray();

            if (!empty($definitions)) {
                $wordData[] = [
                    'word_data_type' => $type,
                    'definitions' => $definitions,
                ];
            }
        }

        return response()->json([
            'status' => 1,
            'message' => 'Word found.',
            'data' => [
                'id' => $word->id,
                'category_id' => $word->category_id,
                'language' => $word->language,
                'word' => $word->word,
                'pronunciation' => $word->pronunciation,
                'description' => $word->description,
                'is_approved' => $word->is_approved,
                'requested_by' => $word->requested_by,
                'approved_by' => $word->approved_by,
                'created_at' => $word->created_at,
                'updated_at' => $word->updated_at,
                'is_favorite' => $word->is_favorite,
                'word_category' => $word->wordCategory,
                'word__data' => $wordData,
            ],
        ]);
    }
}
