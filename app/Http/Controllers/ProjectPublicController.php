<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectApk;
use App\Models\ApkDownload;
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

    // ================== Helper: Clean Old APKs (Keep Only 10) ==================
    private function cleanOldApks($project_id)
    {
        try {
            // Get all APKs for this project, ordered by creation date (newest first)
            $allApks = ProjectApk::where('project_id', $project_id)
                ->orderBy('created_at', 'desc')
                ->get();

            // If more than 10 APKs exist, delete the oldest ones
            if ($allApks->count() > 10) {
                $apksToDelete = $allApks->slice(10); // Get APKs beyond the first 10

                foreach ($apksToDelete as $apk) {
                    // Delete physical file
                    if (Storage::disk('public')->exists($apk->filepath)) {
                        Storage::disk('public')->delete($apk->filepath);
                        Log::info('Deleted file: ' . $apk->filepath);
                    }

                    // Delete download history records
                    ApkDownload::where('apk_id', $apk->id)->delete();

                    // Delete the APK record (hard delete)
                    $apk->delete();
                    
                    Log::info('Hard deleted APK: ' . $apk->filename . ' (ID: ' . $apk->id . ')');
                }

                Log::info('Cleaned ' . $apksToDelete->count() . ' old APKs for project ID: ' . $project_id);
            }
        } catch (\Exception $e) {
            Log::error('Failed to clean old APKs: ' . $e->getMessage());
        }
    }

    // ================== Helper: Get User's Projects ==================
    private function getUserProjects()
    {
        if (Auth::guard('admin')->check()) {
            // Admin can see all projects
            return Project::orderBy('name')->get();
        } elseif (Auth::guard('web')->check()) {
            // Regular user can only see assigned projects
            return Auth::guard('web')->user()->projects()->orderBy('name')->get();
        }
        
        return collect(); // Empty collection for guests
    }

    // ================== Web: Logout ==================
    public function logout(Request $request, $project_slug = null)
    {
        $redirectRoute = null;
        $redirectParams = [];

        // Store redirect info before logout
        if ($project_slug) {
            $redirectRoute = 'project.list';
            $redirectParams = [$project_slug];
        }

        // Logout depending on which guard is authenticated
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        } elseif (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        // Invalidate session and regenerate token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect appropriately
        if ($redirectRoute && $redirectParams) {
            return redirect()->route($redirectRoute, $redirectParams)
                ->with('success', 'You have been logged out successfully.');
        }

        // Default fallback to homepage
        return redirect('/')
            ->with('success', 'You have been logged out successfully.');
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

        // Get only the latest 10 APKs
        $apks = $project->apks()->with('uploadedBy')->latest()->limit(10)->get();
        
        // Get user's projects for dropdown
        $userProjects = $this->getUserProjects();

        return view('projects.list', compact('project', 'apks', 'userProjects'));
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

        // Get user's projects for dropdown
        $userProjects = $this->getUserProjects();

        return view('projects.upload', compact('project', 'userProjects'));
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

        // Create new APK record
        ProjectApk::create([
            'project_id' => $project->id,
            'filename' => $filename,
            'filepath' => $path,
            'description' => $request->description,
            'uploaded_by' => Auth::id(),
        ]);

        // Clean old APKs (keep only 10 latest)
        $this->cleanOldApks($project->id);

        return redirect()->route('project.list', $project_slug)
            ->with('success', 'APK uploaded successfully. Old APKs automatically cleaned.');
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

            // Track download attempt (before actual download)
            try {
                ApkDownload::create([
                    'apk_id'     => $apk->id,
                    'user_id'    => auth()->id(),
                    'device_name'=> $request->input('device_name', 'Unknown'),
                    'os_version' => $request->input('os_version', 'Unknown'),
                    'location'   => $request->input('location', 'Unknown'),
                    'download_time' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to save download history: ' . $e->getMessage());
            }

            // Increment download count only after successful file serving
            $apk->increment('download_count');

            Log::info('Serving file download: ' . $apk->filename);

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