<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CaseProjectResource;
use App\Models\CaseProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CaseProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $caseProjects = CaseProject::with(['staff.positionReference', 'staff.departmentReference', 'client'])
            ->paginate(15);

        return CaseProjectResource::collection($caseProjects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'case_date' => 'required|date',
            'status' => 'required|string',
            'staff_id' => 'required|exists:staff,id',
            'client_id' => 'required|exists:clients,id',
            'link_dokumen' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $caseProject = CaseProject::create($validator->validated());
        $caseProject->load(['staff.positionReference', 'staff.departmentReference', 'client']);

        return response()->json([
            'success' => true,
            'message' => 'Case project created successfully',
            'data' => new CaseProjectResource($caseProject)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $caseProject = CaseProject::with(['staff.positionReference', 'staff.departmentReference', 'client'])
            ->find($id);

        if (!$caseProject) {
            return response()->json([
                'success' => false,
                'message' => 'Case project not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CaseProjectResource($caseProject)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $caseProject = CaseProject::find($id);

        if (!$caseProject) {
            return response()->json([
                'success' => false,
                'message' => 'Case project not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|required|string',
            'case_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|string',
            'staff_id' => 'sometimes|required|exists:staff,id',
            'client_id' => 'sometimes|required|exists:clients,id',
            'link_dokumen' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $caseProject->update($validator->validated());
        $caseProject->load(['staff.positionReference', 'staff.departmentReference', 'client']);

        return response()->json([
            'success' => true,
            'message' => 'Case project updated successfully',
            'data' => new CaseProjectResource($caseProject)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $caseProject = CaseProject::find($id);

        if (!$caseProject) {
            return response()->json([
                'success' => false,
                'message' => 'Case project not found'
            ], 404);
        }

        $caseProject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Case project deleted successfully'
        ]);
    }
}
