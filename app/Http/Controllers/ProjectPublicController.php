<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectApk;
use App\Models\ApkDownload; // new table for download history
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectPublicController extends Controller
{
    // ================== Helper ==================
    private function checkProjectAccess($project_slug)
    {
        $project = Project::where('slug', $project_slug)->firstOrFail();

        if (Auth::guard('admin')->check()) return $project;

        if (Auth::guard('web')->check() && Auth::guard('web')->user()->projects->contains($project)) {
            return $project;
        }

        if (Auth::guard('web')->check()) Auth::guard('web')->logout();
        if (Auth::guard('admin')->check()) Auth::guard('admin')->logout();

        throw \Illuminate\Validation\ValidationException::withMessages([
            'error' => 'You do not have access to this project.'
        ]);
    }

    // ================== Web: List APKs ==================
    public function list($project_slug)
    {
        $project = Project::where('slug', $project_slug)->firstOrFail();

        if (!Auth::guard('web')->check() && !Auth::guard('admin')->check()) {
            return view('projects.list', compact('project'))->with('showLogin', true);
        }

        try {
            $this->checkProjectAccess($project_slug);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return view('projects.list', compact('project'))
                ->with('showLogin', true)
                ->with('error', $e->errors()['error'][0]);
        }

        $apks = $project->apks()->with('uploadedBy')->latest()->get();
        return view('projects.list', compact('project', 'apks'));
    }

    // ================== Web: Login + List ==================
    public function loginAndList(Request $request, $project_slug)
    {
        $project = Project::where('slug', $project_slug)->firstOrFail();

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();

            if (!Auth::guard('web')->user()->projects->contains($project)) {
                Auth::guard('web')->logout();
                return redirect()->back()->with('showLogin', true)
                    ->with('error', 'You do not have access to this project.');
            }

            return redirect()->route('project.list', $project_slug);
        }

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('project.list', $project_slug);
        }

        return redirect()->back()->with('showLogin', true)
            ->with('error', 'Invalid credentials. Please try again.');
    }

    // ================== Web: Upload Form ==================
    public function uploadForm($project_slug)
    {
        $project = Project::where('slug', $project_slug)->firstOrFail();

        if (!Auth::guard('web')->check() && !Auth::guard('admin')->check()) {
            return view('projects.upload', compact('project'))->with('showLogin', true);
        }

        try {
            $this->checkProjectAccess($project_slug);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return view('projects.upload', compact('project'))
                ->with('showLogin', true)
                ->with('error', $e->errors()['error'][0]);
        }

        return view('projects.upload', compact('project'));
    }

    // ================== Web: Upload Store ==================
    public function uploadStore(Request $request, $project_slug)
    {
        $project = $this->checkProjectAccess($project_slug);

        $request->validate([
            'apk_file' => 'required|file|mimes:apk,zip|max:512000',
            'description' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('apk_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('apks/' . $project->slug, $filename, 'public');

        ProjectApk::create([
            'project_id' => $project->id,
            'filename' => $filename,
            'filepath' => $path,
            'description' => $request->description,
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('project.uploadForm', $project_slug)
            ->with('success', 'APK uploaded successfully.');
    }

    // ================== API: Download APK ==================
    public function apiDownload(ProjectApk $apk, Request $request)
    {
        try {
            Log::info('Download request for APK ID: ' . $apk->id);
            
            // Validate that the file exists
            if (!Storage::disk('public')->exists($apk->filepath)) {
                Log::error('File not found: ' . $apk->filepath);
                return response()->json(['status'=>'error','message'=>'File not found'], 404);
            }

            // Get the full file path
            $fullPath = Storage::disk('public')->path($apk->filepath);
            
            if (!file_exists($fullPath)) {
                Log::error('Physical file not found: ' . $fullPath);
                return response()->json(['status'=>'error','message'=>'Physical file not found'], 404);
            }

            // Increment download count
            $apk->increment('download_count');

            // Track download details
            try {
                ApkDownload::create([
                    'apk_id'     => $apk->id,
                    'user_id'    => auth()->id(), // can be null if guest
                    'device_name'=> $request->input('device_name', 'Unknown'),
                    'os_version' => $request->input('os_version', 'Unknown'),
                    'location'   => $request->input('location', 'Unknown'),
                    'download_time' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to save download history: ' . $e->getMessage());
                // Continue with download even if history saving fails
            }

            Log::info('Serving file download: ' . $apk->filename);

            // Return the file download response
            return response()->download($fullPath, $apk->filename, [
                'Content-Type' => 'application/vnd.android.package-archive',
                'Content-Disposition' => 'attachment; filename="' . $apk->filename . '"'
            ]);

        } catch (\Exception $e) {
            Log::error('Download error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Download failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // ================== API: Download History ==================
    public function apiDownloadHistory(ProjectApk $apk)
    {
        try {
            $history = ApkDownload::where('apk_id', $apk->id)
                ->latest('download_time')
                ->limit(50) // Limit to recent 50 downloads
                ->get(['user_id', 'device_name', 'os_version', 'location', 'download_time', 'created_at']);

            return response()->json($history);
            
        } catch (\Exception $e) {
            Log::error('History fetch error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch history'
            ], 500);
        }
    }

    // ================== API: APK Stats ==================
    public function apiStats()
    {
        try {
            $totalApps = ProjectApk::count();
            $totalDownloads = ProjectApk::sum('download_count');
            $lastUpdated = ProjectApk::latest()->first();

            return response()->json([
                'total_apps' => $totalApps,
                'total_downloads' => $totalDownloads,
                'last_updated' => $lastUpdated ? $lastUpdated->updated_at->format('d F Y H:i A') : null
            ]);
            
        } catch (\Exception $e) {
            Log::error('Stats fetch error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }
}