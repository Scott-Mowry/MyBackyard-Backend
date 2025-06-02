<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockImage;
use Illuminate\Support\Facades\Storage;
class StockImageController extends Controller
{
    public function getStockImages()
    {
       $images=StockImage::all();
       return view('Admin.Pages.StockImage.Detail',compact('images'));
    }

    public function stockImagesUpload(Request $request)
    {
        try{
        foreach($request->images as $img){
            $path = $img->store('public/user/stock_images');
            $file_path= Storage::url($path);
            $add=new StockImage();
            $add->image=$file_path;
            $add->save();
        }
            return redirect()->back()->with('success', 'Stock Image uploaded successfully.');
    }catch(\Exception $e)
    {
        return redirect()->back()->with('error',$e);
    }
    }

    public function stockImageDelete($id)
    {
        $image = StockImage::findOrFail($id);
        $image->delete();
        return redirect()->back()->with('success', 'Stock Image deleted successfully.');
    }
}
