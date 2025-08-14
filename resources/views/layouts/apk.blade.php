<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'APK Manager')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            color: #333;
            font-family: 'Segoe UI', sans-serif;
        }
        /* Navbar */
        .navbar {
            background-color: #0d6efd;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            color: #fff;
            font-weight: 600;
        }
        .navbar-brand:hover {
            color: #e2e6ea;
        }
        /* Footer */
        footer {
            background-color: #fff;
            border-top: 1px solid #dee2e6;
        }
        /* Cards */
        .apk-card, .upload-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .apk-card:hover, .upload-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        /* Buttons */
        .btn-primary, .btn-upload {
            border-radius: 50px;
            font-weight: 600;
        }
        /* Dropzone */
        .dropzone {
            background: #f1f3f5;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s, border-color 0.3s;
        }
        .dropzone:hover {
            background: #e9ecef;
            border-color: #0d6efd;
        }
        /* Badge */
        .badge-new {
            background-color: #e9ecef;
            color: #333;
            font-weight: 500;
            font-size: 0.8rem;
        }
        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">APK Manager</a>
        </div>
    </nav>

    <main class="py-5">
        @yield('content')
    </main>

    <footer class="py-3 text-center">
        &copy; {{ date('Y') }} APK Manager. All rights reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
