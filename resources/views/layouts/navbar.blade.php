<nav class="navbar navbar-expand-lg border-bottom" style="background-color:#A5BEE2;">
    <div class="container-fluid px-4 d-flex justify-content-between align-items-center">

        <!-- LEFT -->
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-list fs-4"
               style="color:#070367; cursor:pointer;"
               id="toggleSidebar"></i>

            <span class="fw-bold" style="color:#070367;">
                DASHBOARD
            </span>
        </div>

        <!-- RIGHT -->
        <div class="d-flex align-items-center gap-4">

            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill" style="color:#070367;"></i>
                <span style="color:#070367; font-weight:500;">
                    {{ auth()->user()->role }}
                </span>
            </div>

            <div class="dropdown">
                <button class="btn d-flex align-items-center gap-2 border-0"
                        type="button"
                        data-bs-toggle="dropdown"
                        style="color:#070367;">

                    <i class="bi bi-person-workspace"></i>
                    <span style="font-weight:500;">
                        {{ auth()->user()->name }}
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li class="dropdown-header fw-bold">
                        Selamat datang!
                    </li>

                    <li> 
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#"> 
                            <i class="bi bi-arrow-clockwise">
                    </i> 
                        Muat Ulang
                        </a>
                    </li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="dropdown-item d-flex align-items-center gap-2">
                                <i class="bi bi-box-arrow-right"></i>
                                Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</nav>