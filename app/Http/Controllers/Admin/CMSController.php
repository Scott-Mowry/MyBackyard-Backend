<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CMS;
class CMSController extends Controller
{
    public function getCMS()
    {
        $data=CMS::whereType('tc')->latest()->first();
        $data2=CMS::whereType('pp')->latest()->first();
        $data3=CMS::whereType('au')->latest()->first();
        return view('Admin.Pages.CMS.Add',compact('data','data2','data3'));
    }

    public function addCMS(Request $request)
    {
        $checkCms1=CMS::whereType('tc')->latest()->first();
        $checkCms11=CMS::whereType('pp')->latest()->first();
        $checkCms111=CMS::whereType('au')->latest()->first();
        if(!$checkCms1)
        {
            $add_cms=new CMS();
            $add_cms->type='tc';
            $add_cms->detail=$request->terms;
            $add_cms->save();
        }
        else{
            $update=$checkCms1->update(['detail'=>$request->terms]);
        }
        if(!$checkCms11)
        {
            $add_cms11=new CMS();
            $add_cms11->type='pp';
            $add_cms11->detail=$request->policy;
            $add_cms11->save();
        }
        else{
            $update=$checkCms11->update(['detail'=>$request->policy]);
        }

        if(!$checkCms111)
        {
            $add_cms11=new CMS();
            $add_cms11->type='au';
            $add_cms11->detail=$request->policy;
            $add_cms11->save();
        }
        else{
            $update=$checkCms111->update(['detail'=>$request->policy]);
        }
        return redirect()->back()->with('success','CMS has been saved Successfully.');
    }
}
