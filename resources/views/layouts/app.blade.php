<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* SIDEBAR */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: #A5BEE2;
            transition: width .3s ease;
        }

        #sidebar.collapsed {
            width: 80px;
        }

        #sidebar.collapsed .menu-text {
            display: none;
        }

        /* MAIN CONTENT */
        #main-content {
            margin-left: 250px;
            transition: margin-left .3s ease;
        }

        #main-content.expanded {
            margin-left: 80px;
        }
    </style>
</head>

<body>

@include('layouts.sidebar')

<div id="main-content">
    @include('layouts.navbar')

    <main class="container py-4">
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const toggle = document.getElementById("toggleSidebar");
    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("main-content");

    toggle.addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("expanded");
    });

});
</script>

</body>
</html>