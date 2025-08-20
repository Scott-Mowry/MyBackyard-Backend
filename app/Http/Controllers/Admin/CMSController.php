<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CMS;
class CMSController extends Controller
{
    public function getCMS()
    {
        $data = CMS::whereType('tc')->latest()->first();
        $data2 = CMS::whereType('pp')->latest()->first();
        $data3 = CMS::whereType('au')->latest()->first();
        return view('Admin.Pages.CMS.Add', compact('data', 'data2', 'data3'));
    }

    public function addCMS(Request $request)
    {
        $checkCms1 = CMS::whereType('tc')->latest()->first();
        $checkCms11 = CMS::whereType('pp')->latest()->first();
        $checkCms111 = CMS::whereType('au')->latest()->first();
        if (!$checkCms1) {
            $add_cms = new CMS();
            $add_cms->type = 'tc';
            $add_cms->detail = $request->terms;
            $add_cms->save();
        } else {
            $update = $checkCms1->update(['detail' => $request->terms]);
        }
        if (!$checkCms11) {
            $add_cms11 = new CMS();
            $add_cms11->type = 'pp';
            $add_cms11->detail = $request->policy;
            $add_cms11->save();
        } else {
            $update = $checkCms11->update(['detail' => $request->policy]);
        }

        if (!$checkCms111) {
            $add_cms11 = new CMS();
            $add_cms11->type = 'au';
            $add_cms11->detail = $request->policy;
            $add_cms11->save();
        } else {
            $update = $checkCms111->update(['detail' => $request->policy]);
        }
        return redirect()->back()->with('success', 'CMS has been saved Successfully.');
    }

    // API v2 Functions
    public function getCMSv2()
    {
        $cmsPages = CMS::select('*')->orderBy('id', 'desc')->get();

        $cmsData = $cmsPages->map(function ($row) {
            $typeLabel = '';
            switch ($row->type) {
                case 'tc':
                    $typeLabel = 'Terms & Conditions';
                    break;
                case 'pp':
                    $typeLabel = 'Privacy Policy';
                    break;
                case 'au':
                    $typeLabel = 'About Us';
                    break;
                default:
                    $typeLabel = ucfirst($row->type);
            }

            return [
                'id' => $row->id,
                'type' => $row->type,
                'type_label' => $typeLabel,
                'detail' => $row->detail ?? '---',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'CMS data retrieved successfully',
            'data' => $cmsData
        ], 200);
    }

    public function addCMSv2(Request $request)
    {
        try {
            $type = $request->type;
            $detail = $request->detail;

            if (!$type || !$detail) {
                return response()->json([
                    'status' => false,
                    'message' => 'Type and detail are required'
                ], 400);
            }

            // Check if CMS page of this type already exists
            $existingCms = CMS::whereType($type)->latest()->first();

            if (!$existingCms) {
                // Create new CMS page
                $newCms = new CMS();
                $newCms->type = $type;
                $newCms->detail = $detail;
                $newCms->save();
            } else {
                // Update existing CMS page
                $existingCms->update(['detail' => $detail]);
            }

            return response()->json([
                'status' => true,
                'message' => 'CMS page has been saved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error saving CMS data'
            ], 500);
        }
    }
}
