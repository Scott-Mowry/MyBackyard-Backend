<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CMS;
use App\Models\ContentWeb;
use Validator;
use App\Http\Controllers\BaseController as BaseController;
class CMSController extends BaseController
{
    public function getPolicyData()
    {
       $data=CMS::whereType('pp')->latest()->first();

       $title=$data->type;
       $description=htmlspecialchars($data->detail);

       $about = [
        'title' => 'Privacy Policy',
        'description' => $description
       ];

       return view('CMS.policy', $about);
    }



    public function getTermsData()
    {
        $data=CMS::whereType('tc')->latest()->first();
        $title=$data->type;
        $description=htmlspecialchars($data->detail);

        $about = [
         'title' => 'Terms And Conditions',
         'description' => $description
       ];
       return view('CMS.terms', $about);
    }


    public function getAboutData()
    {
        $data=CMS::whereType('au')->latest()->first();
        $title=$data->type;
        $description=htmlspecialchars($data->detail);

        $about = [
         'title' => 'About Us',
         'description' => $description
       ];
       return view('CMS.about', $about);
    }



    public function getWebView(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'type' => 'required|in:pp,tc,au',
        ]);

        if($valid->fails())
        {
            return $this->sendError($valid->errors()->first());
        }

        $url=ContentWeb::whereType($request->type)->latest()->first();
        if($url){
            return $this->sendResponse($url->url,'content found.');
        }
        else{
            return $this->sendError('Content not found.');
        }


    }

}
