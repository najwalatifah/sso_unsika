<!-- SIDEBAR -->
<div id="sidebar">

    <div class="logo text-center py-4">
        <img src="{{ asset('img/logo_unsika.png') }}" width="60">
    </div>

    <ul class="nav flex-column px-2">
        <li class="nav-item">
            <a href="#" class="nav-link d-flex align-items-center gap-3">
                <i class="bi bi-bar-chart-line-fill fs-5"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>
    </ul>

</div>

<style>
/* Default link */
#sidebar .nav-link {
    color: #070367;
    font-weight: 500;
    transition: all 0.3s ease;
    border-radius: 16px;
    padding: 12px 16px;
}

/* Icon ikut transition */
#sidebar .nav-link i {
    transition: all 0.3s ease;
}

/* Hover effect */
#sidebar .nav-link:hover {
    background-color: #070367;
    color: #ffffff !important;
}

/* Hover icon */
#sidebar .nav-link:hover i {
    color: #ffffff;
}
</style>