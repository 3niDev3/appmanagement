<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

class ProjectController extends Controller
{

    public function index()
    {
        $projects = Project::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.form');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:projects',
            'slug' => 'required|string|max:255|unique:projects,slug',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Auto-generate slug if it's empty or just whitespace
        if (empty(trim($data['slug']))) {
            $data['slug'] = $this->generateSlug($data['name']);
        } else {
            // Clean the provided slug
            $data['slug'] = $this->generateSlug($data['slug']);
        }

        // Ensure slug is unique
        $data['slug'] = $this->ensureUniqueSlug($data['slug']);

        Project::create($data);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project created successfully!');
    }

    public function edit(string $id)
    {
        $project = Project::findOrFail($id);
        return view('admin.projects.form', compact('project'));
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:projects,name,' . $id,
            'slug' => 'required|string|max:255|unique:projects,slug,' . $id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Auto-generate slug if it's empty or just whitespace
        if (empty(trim($data['slug']))) {
            $data['slug'] = $this->generateSlug($data['name']);
        } else {
            // Clean the provided slug
            $data['slug'] = $this->generateSlug($data['slug']);
        }

        // Ensure slug is unique (excluding current project)
        $data['slug'] = $this->ensureUniqueSlug($data['slug'], $id);

        $project = Project::findOrFail($id);
        $project->update($data);

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully!');
    }

    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project deleted successfully!');
    }

    /**
     * Generate a URL-friendly slug from text
     * Examples: "Test User" -> "test-user", "My Project Name" -> "my-project-name"
     */
    private function generateSlug($text)
    {
        // Convert to lowercase and create slug
        $slug = Str::slug($text, '-');
        
        // Remove any extra characters and ensure it's clean
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug); // Replace multiple dashes with single dash
        $slug = trim($slug, '-'); // Remove leading/trailing dashes
        
        return $slug;
    }

    /**
     * Ensure the slug is unique by appending numbers if necessary
     */
    private function ensureUniqueSlug($slug, $excludeId = null)
    {
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = Project::where('slug', $slug);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            if (!$query->exists()) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * AJAX endpoint to generate slug from name
     */
    public function generateSlugAjax(Request $request)
    {
        $name = $request->input('name', '');
        $excludeId = $request->input('exclude_id');
        
        if (empty($name)) {
            return response()->json(['slug' => '']);
        }

        $slug = $this->generateSlug($name);
        $uniqueSlug = $this->ensureUniqueSlug($slug, $excludeId);

        return response()->json(['slug' => $uniqueSlug]);
    }
}