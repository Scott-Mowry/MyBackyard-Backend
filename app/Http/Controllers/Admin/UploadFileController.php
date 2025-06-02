<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\UploadFileModel;
use DataTables;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;

class UploadFileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = UploadFileModel::select('*')->orderBy('id', 'DESC');
            return Datatables::of($data)
                ->addIndexColumn(null, null, false)

                ->addColumn('action', function ($row) {
                    $delete = route('admin.upload-file.destroy', ['id' => $row->id]);
                    // $edit = route('admin.upload-file.edit', ['id' => $row->hash]);
                    $view = route('admin.upload-file.show', ['id' => $row->id]);

                    $actionBtn = '<div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                        Actions
                    </button>

                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a href="' . $delete . '" class="dropdown-item">Delete</a>
                        <a href="' . $view . '" class="dropdown-item">View</a>
                     </div>

            
                </div>';

                    return $actionBtn;
                })
                ->addColumn('hash', function ($row) {
                    return $row->hash;
                })
                ->addColumn('name', function ($row) {
                    return $row->name;
                })
                ->filterColumn('name', function ($query, $keyword) {
                    $query->where('name', 'like', '%' . $keyword . '%');
                })
                ->addColumn('attachment', function ($row) {
                    return $row->attachment;
                })
                ->addColumn('size', function ($row) {
                    return $row->size;
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at;
                })
                ->filterColumn('size', function ($query, $keyword) {
                    $query->where('size', 'like', '%' . $keyword . '%');
                })
                ->addColumn('type', function ($row) {
                    return $row->type;
                })
                ->filterColumn('type', function ($query, $keyword) {
                    $query->where('type', 'like', '%' . $keyword . '%');
                })
                ->addColumn('updated_at', function ($row) {
                    return $row->updated_at;
                })

                ->rawColumns(['action'])
                ->make(true);
        }


        return view('Admin.Pages.FileUpload.Listing');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $type = "create";
        return view('Admin.Pages.FileUpload.Add', compact('type'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|array',
        ]);

        $files = $request->file('file');
        $hash = Helper::generateUniqueRandomString();

        $data = [];

        foreach ($files as $file) {

            $path = $file->store('public/files');
            $file_path = Storage::url($path);

            $data[] = [
                'hash' => $hash,
                'user_id' => Auth::user()->id,
                'name' => $file->getClientOriginalName(),
                'attachment' => $file_path,
                'size' => round(($file->getSize() / (1024 * 1024)), 2) . ' mb',
                // 'type' => $file->extension(),
                'type' => preg_match('/[А-Яа-яЁё]/u', $file->getClientOriginalName()) == 1 ? 'tr' : 'en',
                'created_at' => date("Y-m-d H:i:s")
            ];

        }

        UploadFileModel::insert($data);

        return redirect()
            ->route('admin.upload-file')
            ->with('success', 'Uploaded successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $file = UploadFileModel::where('id', '=', $id)->first();
        return view('Admin.Pages.FileUpload.Detail', compact('file'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($hash)
    {
        $files = UploadFileModel::where('hash', '=', $hash)->get();
        $type = "edit";
        return view('Admin.Pages.FileUpload.Add', compact('type', 'files'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $hash)
    {
        $this->validate($request, [
            'name' => 'required|array',
            'file' => 'required|array',
        ]);

        //dd($request->all());

        $names = $request->input('name');
        $files = $request->file('file');
        $prev_file = $request->input('prev_file');
        $sizes = $request->input('size');
        $types = $request->input('type');

        $data = [];

        foreach ($names as $key => $name) {

            $file_path = null;
            $size = null;
            $type = null;

            if ($prev_file[$key]) {
                $file_path = $prev_file[$key];
                $size = $sizes[$key];
                $type = $types[$key];
            } else {
                $path = $files[$key]->store('public/files');
                $file_path = Storage::url($path);
                $size = round(($files[$key]->getSize() / (1024 * 1024)), 2) . ' mb';
                $type = $files[$key]->extension();
            }

            $data[] = [
                'hash' => $hash,
                'user_id' => Auth::user()->id,
                'name' => $name,
                'attachment' => $file_path,
                'size' => $size,
                'type' => $type
            ];

        }

        //dd($data);

        UploadFileModel::where('hash', '=', $hash)->delete();
        UploadFileModel::insert($data);

        return redirect()
            ->route('admin.upload-file')
            ->with('success', 'Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        UploadFileModel::where('id', '=', $id)->delete();
        return redirect()
            ->route('admin.upload-file')
            ->with('success', 'File deleted successfully');
    }
}
