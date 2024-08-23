<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Jobs\ImportProjectExcelFileJob;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Project\ImportStoreRequest;
use App\Http\Resources\Project\ProjectResource;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::paginate(10);
        $projects = ProjectResource::collection($projects);

        return inertia('Project/Index', compact('projects'));
    }

    public function import()
    {
        return inertia('Project/Import');
    }

    public function importStore(ImportStoreRequest $request)
    {
        $data = $request->validated();

        $file = File::putAndCreate($data['file']); 
        $task = Task::create([
            'file_id' => $file->id,
            'user_id' => auth()->id(),
            'type' => $data['type'],
        ]);

        ImportProjectExcelFileJob::dispatch($file->path, $task)->onConnection('sync');
    }
}
