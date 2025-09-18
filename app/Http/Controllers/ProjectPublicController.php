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

            // ✅ extra check for normal users
            if (Auth::guard('web')->check() && !Auth::guard('web')->user()->can_upload) {
                return redirect()->route('project.list', $project_slug)
                    ->with('error', 'You are not allowed to upload.');
            }

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

        // ✅ block non-upload users
        if (Auth::guard('web')->check() && !Auth::guard('web')->user()->can_upload) {
            return redirect()->route('project.list', $project_slug)
                ->with('error', 'You are not allowed to upload.');
        }

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



    // ================== Web: Login + Upload ==================
    public function loginAndUpload(Request $request, $project_slug)
    {
        $project = Project::where('slug', $project_slug)->firstOrFail();

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        // Try user login
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::guard('web')->user();

            if (!$user->projects->contains($project)) {
                Auth::guard('web')->logout();
                return redirect()->back()->with('showLogin', true)
                    ->with('error', 'You do not have access to this project.');
            }

            // ✅ check upload permission
            if (!$user->can_upload) {
                Auth::guard('web')->logout();
                return redirect()->back()->with('showLogin', true)
                    ->with('error', 'You are not allowed to upload.');
            }

            return redirect()->route('project.uploadForm', $project_slug);
        }

        // Try admin login (admins always allowed)
        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('project.uploadForm', $project_slug);
        }

        return redirect()->back()->with('showLogin', true)
            ->with('error', 'Invalid credentials. Please try again.');
}


    // ================== API: Download APK ==================
    // Modified apiDownload - Don't track anything yet
public function apiDownload(ProjectApk $apk, Request $request)
{
    try {
        // Validate file exists
        if (!Storage::disk('public')->exists($apk->filepath)) {
            return response()->json(['status'=>'error','message'=>'File not found'], 404);
        }

        $fullPath = Storage::disk('public')->path($apk->filepath);
        
        // Just serve the file - NO tracking here
        return response()->download($fullPath, $apk->filename, [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Content-Disposition' => 'attachment; filename="' . $apk->filename . '"'
        ]);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Download failed'], 500);
    }
}

// NEW method - Only track when download actually completes
public function apiDownloadComplete(ProjectApk $apk, Request $request)
{
    try {
        DB::transaction(function () use ($apk, $request) {
            // NOW increment count and save history
            $apk->increment('download_count');
            
            ApkDownload::create([
                'apk_id'        => $apk->id,
                'user_id'       => auth()->id(),
                'device_name'   => $request->input('device_name', 'Unknown'),
                'os_version'    => $request->input('os_version', 'Unknown'),
                'location'      => $request->input('location', 'Unknown'),
                'download_time' => now(),
            ]);
        });

        return response()->json([
            'status' => 'success',
            'new_count' => $apk->fresh()->download_count
        ]);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error'], 500);
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