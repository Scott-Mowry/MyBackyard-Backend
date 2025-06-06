<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WordAdjective;
use Illuminate\Http\Request;
use DataTables;
use App\Models\WordDictionary;
use App\Models\WordNoun;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Notifications\Send;
use App\Helpers\Helper;
use App\Models\User;
use App\Models\WordData;
use App\Models\Category;
class ManageWordsController extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $data = WordDictionary::select('*')->with('word_data')->with('requestedBy')->with('approvedBy')->orderBy('id', 'DESC');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    // $delete = route('admin.delete.manage_words', ['id' => $row->id]);
                    $view = route('admin.manage_words.details', ['id' => $row->id]);


                    $actionBtn = '<div class="dropdown" >
                    <button class="btn btn-secondary dropdown-toggle mt-3" type="button" data-toggle="dropdown">
                        Actions
                    </button>
                    <div class="dropdown-menu">
                        <a href="' . $view . '" class="dropdown-item">Edit</a>
                    </div>
                </div>';

                    return $actionBtn;
                })
                // ->addColumn('post_by', function($row){
                //     if($row->user_id==null){
                //         return 'Admin';
                //     }else{
                //        return $row->users->name;
                //     }

                // })
                ->addColumn('word', function ($row) {
                    return $row->word ?? '---';
                })->filterColumn('word', function ($query, $keyword) {
                    $query->where('word', 'like', '%' . $keyword . '%');
                })
                ->addColumn('pronunciation ', function ($row) {
                    if ($row->pronunciation !== null) {
                        return  $row->pronunciation;
                    } else {
                        return '---';
                    }
                })
                ->addColumn('description ', function ($row) {
                    if ($row->description !== null) {
                        return  $row->description;
                    } else {
                        return '---';
                    }
                })
                ->addColumn('is_approved', function ($row) {
                    if ($row->is_approved == 1) {
                        return  "Approved";
                    } else {
                        return "Not Approved";
                    }
                })
                ->rawColumns(['action', 'pronunciation'])
                ->make(true);
        }


        return view('Admin.Pages.ManageWords.Listing');
    }



    public function deleteManageWord($id)
    {
        $word_dictionary = WordDictionary::find($id);
        if ($word_dictionary) {
            $word_dictionary->delete();
            return redirect()->back()->with('success', 'Dictionary Word has been deleted Successfully.');
        } else {
            return redirect()->back()->with('error', 'Error in deleting dictionary word.');
        }
    }

    public function manageWordAddForm()
    {
         $categories = Category::select('id', 'category_name','category_icon')->get();
        return view('Admin.Pages.ManageWords.Add', compact('categories'));
    }


    public function addManageWord(Request $request)
    {
        // dd($request->all());
        try {
            
             $requestedBy = auth()->user()->id;
            $Word = new WordDictionary();
            $Word->category_id = $request->category_id;
            $Word->language = 'en';
            $Word->pronunciation = $request->pronunciation;
             $Word->word = $request->word;
            $Word->description = $request->word;
            $Word->description = $request->description;
            $Word->requested_by = $requestedBy;
            $Word->is_approved = 1;
            $Word->save();
            
            if (($request->adjective_text != '') || ($request->adjective_text != null)) {
            
                $wordData = new WordData();
                $wordData->word_dictionary_id = $Word->id;
                $wordData->word_data_type = 'adjective';
                $wordData->word_data_text = $request->adjective_text;
                $wordData->save();
                
            }
            if (($request->pronoun_text != '') || ($request->pronoun_text != null)) {
              
                $wordData = new WordData();
                $wordData->word_dictionary_id = $Word->id;
                $wordData->word_data_type = 'pronoun';
                $wordData->word_data_text = $request->pronoun_text;
                $wordData->save();
            }
            if (($request->verb_text != '') || ($request->verb_text != null)) {
              
                $wordData = new WordData();
                $wordData->word_dictionary_id = $Word->id;
                $wordData->word_data_type = 'verb';
                $wordData->word_data_text = $request->verb_text;
                $wordData->save();
            }
            // $add = new WordDictionary();
            // $add->detail = $request->detail;
            // $add->user_id = null;

            // // if ($request->hasFile('image')) {
            // //     $img = $request->image;
            // //     $path = $img->store('public/admin/word');
            // //     $file_path = Storage::url($path);
            // //     $add->image = $file_path;
            // // }
            // $add->save();
            return redirect()->back()->with('success', 'Dictionary Word has been added Successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getManageWordById($id)
    {
        $word = WordDictionary::where('id','=',$id)->with('wordCategory')->with('word_data')->first();

        // dd($word);
        return view('Admin.Pages.ManageWords.Detail', compact('word'));
    }


    public function updateManageWord(Request $request)
    {
        $userID = Auth::user()->id;
        $word = WordDictionary::where('id','=',$request->id)->first();

        if ($word) {
            try {
                $word->update([
                    'word' => $request->word,
                    'pronunciation' => $request->pronunciation,
                    'description' => $request->description,
                    'is_approved' => $request->is_approved,
                    'approved_by' => $userID,
                ]);


                if (($request->noun_text != '') || ($request->noun_text != null)) {
                    $wordData = WordData::where('word_dictionary_id', $request->id)->where('word_data_type', '=', 'noun')->first();
                    if ($wordData) {
                        $wordData->update(['word_data_text' => $request->noun_text]);
                    } else {
                        $wordData = new WordData();
                        $wordData->word_dictionary_id = $request->id;
                        $wordData->word_data_type = 'noun';
                        $wordData->word_data_text = $request->noun_text;
                        $wordData->save();
                    }
                }
                if (($request->adjective_text != '') || ($request->adjective_text != null)) {
                    $wordData = WordData::where('word_dictionary_id', $request->id)->where('word_data_type', '=', 'adjective')->first();
                    if ($wordData) {
                        $wordData->update(['word_data_text' => $request->adjective_text]);
                    } else {
                        $wordData = new WordData();
                        $wordData->word_dictionary_id = $request->id;
                        $wordData->word_data_type = 'adjective';
                        $wordData->word_data_text = $request->adjective_text;
                        $wordData->save();
                    }
                }
                if (($request->pronoun_text != '') || ($request->pronoun_text != null)) {
                    $wordData = WordData::where('word_dictionary_id', $request->id)->where('word_data_type', '=', 'pronoun')->first();
                    if ($wordData) {
                        $wordData->update(['word_data_text' => $request->pronoun_text]);
                    } else {
                        $wordData = new WordData();
                        $wordData->word_dictionary_id = $request->id;
                        $wordData->word_data_type = 'pronoun';
                        $wordData->word_data_text = $request->pronoun_text;
                        $wordData->save();
                    }
                }
                if (($request->verb_text != '') || ($request->verb_text != null)) {
                    $wordData = WordData::where('word_dictionary_id', $request->id)->where('word_data_type', '=', 'verb')->first();
                    if ($wordData) {
                        $wordData->update(['word_data_text' => $request->verb_text]);
                    } else {
                        $wordData = new WordData();
                        $wordData->word_dictionary_id = $request->id;
                        $wordData->word_data_type = 'verb';
                        $wordData->word_data_text = $request->verb_text;
                        $wordData->save();
                    }
                }

                $noticeTitle = "Word Approved";
                $message = 'Your word "'.$word->word.'" has been approved successfully.';
                /***********************   Notification start  *********************/
                // try {
                    $user = User::where("id", $word->requested_by)->first(['id', 'device_token', 'is_push_notify']);
                    // dd($users);
                    // foreach ($users as $user) {
                        $notification = [
                            'device_token' => $user->device_token,
                            'sender_id' => $userID,
                            'receiver_id' => $user->id,
                            'title'  => $noticeTitle,
                            'description' => $message,
                            'record_id' => $word->id,
                            'word_id' => $word->id,
                            'type' => "admin_to_user",
                        ];

                        // dd($notification);


                        if ($user->is_push_notify == '1') {
                            Helper::push_notification($notification);
                        }
                        Helper::in_app_notification($notification);
                    // }
                    // return response()->json([
                    //     'status' => 1,
                    //     'message' => 'Notifications Send',
                    //     'data' => $users,
                    // ]);
                // } catch (\Exception $e) {
                //     return response()->json([
                //         'status' => 0,
                //         'message' => $e.message,
                //     ]);
                // }
                /***********************  Notification end  *********************/

                return redirect()->back()->with('success', 'Word has been updated successfully.');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error in updating word.');
            }
        } else {
            return redirect()->back()->with('error', 'Error in updating word.');
        }
    }
}
