<?php

namespace App\Http\Controllers;

use App\Models\ProjectDetail;
use App\Models\AccessRight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectDetailController extends Controller
{
    private function checkAccess($permission){
        $access = AccessRight::where('user_id', Auth::id())
            ->where('module_name', 'projectdetails')
            ->first();
    
        if (!$access || $access->{$permission . '_access'} == 0) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    }
        
    public function index()
{
    $this->checkAccess('read');  // read permission check

    try {
        $projects = ProjectDetail::all();
        return response()->json($projects, 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to fetch projects'], 500);
    }
}

public function store(Request $request)
{
    $this->checkAccess('write');  // write permission check

    $request->validate([
        'project_name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|in:active,inactive',
    ]);

    try {
        $project = ProjectDetail::create([
            'project_name' => $request->project_name,
            'description' => $request->description,
            'status' => $request->status,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Project created', 'project' => $project], 201);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to create project'], 500);
    }
}

public function show($id)
{
    $this->checkAccess('read');  // read permission check

    try {
        $project = ProjectDetail::findOrFail($id);
        return response()->json($project, 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Project not found'], 404);
    }
}

public function update(Request $request, $id)
{
    $this->checkAccess('update');  // update permission check

    $request->validate([
        'project_name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|in:active,inactive',
    ]);

    try {
        $project = ProjectDetail::findOrFail($id);
        $project->project_name = $request->project_name;
        $project->description = $request->description;
        $project->status = $request->status;
        $project->updated_by = Auth::id();
        $project->save();

        return response()->json(['message' => 'Project updated', 'project' => $project], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to update project'], 500);
    }
}

public function destroy($id)
{
    $this->checkAccess('delete');  // delete permission check

    try {
        $project = ProjectDetail::findOrFail($id);
        $project->delete();

        return response()->json(['message' => 'Project deleted'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to delete project'], 500);
    }
}


}
