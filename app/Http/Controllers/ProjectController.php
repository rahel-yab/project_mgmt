<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest; 
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    protected $projectService;

    // Inject the service through the constructor
    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(): JsonResponse
    {
        $projects = $this->projectService->getAllProjects();
        return response()->json($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        // The Request class handles validation automatically
        $project = $this->projectService->createProject($request->validated());

        return response()->json([
            'message' => 'Project created successfully',
            'data' => $project
        ], 201);
    }

    public function destroy(\App\Models\Project $project): JsonResponse
{
    // 1. Authorization Check (Only the creator or Admin can delete)
    // We will define this 'delete' rule in the ProjectPolicy
    Gate::authorize('delete', $project);

    // 2. Call the Service to handle the "Cascade Soft Delete"
    $this->projectService->deleteProject($project);

    return response()->json([
        'message' => 'Project and its tasks have been moved to trash.'
    ], 200);
}
}

