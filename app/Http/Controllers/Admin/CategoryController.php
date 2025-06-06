<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function getCategories(Request $request)
    {

        if ($request->ajax()) {
            $data = Category::select('*')->orderBy('id', 'DESC');
            return Datatables::of($data)
                ->addIndexColumn(null, null, false)

                ->addColumn('action', function ($row) {
                    $active = route('admin.categories.action', ['id' => $row->id, 'status' => 'Active']);
                    $inactive = route('admin.categories.action', ['id' => $row->id, 'status' => 'Inactive']);
                    $view = route('admin.categories.details', ['id' => $row->id]);

                    if ($row->status == 'Inactive') {
                        $actionBtn = '<div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                        Actions
                    </button>
                    <div class="dropdown-menu">
                        <!-- <a href="' . $active . '" class="dropdown-item">Active</a> --!>
                        <a href="' . $view . '" class="dropdown-item">Edit</a>
                    </div>
                </div>';
                    } else {
                        $actionBtn = '<div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" " type="button" data-toggle="dropdown">
                        Actions
                    </button>
                    <div class="dropdown-menu">
                    <!-- <a href="' . $inactive . '" class="dropdown-item">Inactive</a> --!>
                        <a href="' . $view . '" class="dropdown-item">Edit</a>
                    </div>
                </div>';
                    }
                    return $actionBtn;
                })
                ->addColumn('category_name', function ($row) {
                    return $row->category_name ?? '---';
                })->filterColumn('category_name', function ($query, $keyword) {
                    $query->where('category_name', 'like', '%' . $keyword . '%');
                })
                ->addColumn('category_icon', function ($row) {
                    return $row->category_icon ?? '---';
                })->filterColumn('category_icon', function ($query, $keyword) {
                    $query->where('category_icon', 'like', '%' . $keyword . '%');
                })
                ->addColumn('status', function ($row) {
                    return $row->status ?? '---';
                })->filterColumn('status', function ($query, $keyword) {
                    $query->where('status', 'like', '%' . $keyword . '%');
                })

                ->rawColumns(['action', 'status'])
                ->make(true);
        }


        return view('Admin.Pages.Category.Listing');
    }


    public function actionCategoryById($id, $status)
    {
        $category = Category::find($id);
        $update = $category->update(['status' => $status]);
        if ($update) {
            if ($status == 'Active') {
                return redirect()->back()->with('success', 'Category has been active Successfully.');
            } else {
                return redirect()->back()->with('success', 'Category has been inactive Successfully.');
            }
        } else {
            return redirect()->back()->with('error', 'Server Error.');
        }
    }



    public function getCategoryById($id)
    {
        $category = Category::find($id);
        return view('Admin.Pages.Category.Detail', compact('category'));
    }

    public function updateCategory(Request $request)
    {
        $category = Category::find($request->category_id);
        if ($category) {
            $data = [];
            if ($request->hasFile('category_icon')) {
                $img = $request->category_icon;
                $path = $img->store('public/icons');
                $file_path = Storage::url($path);
                // $data['category_icon'] = $file_path;
            }
            $data = ['category_name' => $request->category_name, 'category_icon' => $file_path];
            try {
                $category->update($data);
                return redirect()->back()->with('success', 'Category has been updated Successfully.');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error in updating category.');
            }
        } else {
            return redirect()->back()->with('error', 'No Category found with this detail.');
        }
    }

    public function addCategoryForm()
    {
        return view('Admin.Pages.Category.Add');
    }

    public function addCategory(Request $request)
    {

        try {
            if ($request->hasFile('category_icon')) {
                $img = $request->category_icon;
                $path = $img->store('public/icons');
                $file_path = Storage::url($path);
                // $data['category_icon'] = $file_path;
            }
            $add = new Category();
            $add->category_name = $request->category_name;
            $add->category_icon = $file_path;
            $add->save();
            return redirect()->back()->with('success', 'Category has been added Successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error in adding category.');
        }
    }
}
