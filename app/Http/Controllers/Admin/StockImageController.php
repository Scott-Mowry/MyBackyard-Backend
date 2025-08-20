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
        $images = StockImage::all();
        return view('Admin.Pages.StockImage.Detail', compact('images'));
    }

    public function stockImagesUpload(Request $request)
    {
        try {
            foreach ($request->images as $img) {
                $path = $img->store('public/user/stock_images');
                $file_path = Storage::url($path);
                $add = new StockImage();
                $add->image = $file_path;
                $add->save();
            }
            return redirect()->back()->with('success', 'Stock Image uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e);
        }
    }

    public function stockImageDelete($id)
    {
        $image = StockImage::findOrFail($id);
        $image->delete();
        return redirect()->back()->with('success', 'Stock Image deleted successfully.');
    }

    // API v2 Functions
    public function getStockImagesv2()
    {
        $images = StockImage::all();

        $stockImages = $images->map(function ($image) {
            return [
                'id' => $image->id,
                'image' => $image->image,
                'created_at' => $image->created_at,
                'updated_at' => $image->updated_at
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Stock images retrieved successfully',
            'data' => $stockImages
        ], 200);
    }

    public function stockImagesUploadv2(Request $request)
    {
        try {
            $uploadedImages = [];

            foreach ($request->images as $img) {
                $path = $img->store('public/user/stock_images');
                $file_path = Storage::url($path);
                $add = new StockImage();
                $add->image = $file_path;
                $add->save();

                $uploadedImages[] = [
                    'id' => $add->id,
                    'image' => $add->image,
                    'created_at' => $add->created_at
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'Stock images uploaded successfully',
                'data' => $uploadedImages
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error uploading stock images: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stockImageDeletev2($id)
    {
        $image = StockImage::find($id);

        if (!$image) {
            return response()->json([
                'status' => false,
                'message' => 'Stock image not found'
            ], 404);
        }

        try {
            $image->delete();
            return response()->json([
                'status' => true,
                'message' => 'Stock image deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting stock image'
            ], 500);
        }
    }
}
