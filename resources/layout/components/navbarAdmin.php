<!-- navbar -->
<nav class="navbar bg-base-100 shadow-sm">
    <section class="navbar-start">
        <div class="drawer">
            <input type="checkbox" class="drawer-toggle" id="my-drawer" />
            <div class="drawer-content">
                <div class="flex-none">
                    <label for="my-drawer" class="btn btn-square btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-5 w-5 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </label>
                </div>
            </div>
            <div class="drawer-side">
                <label for="my-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
                <ul class="menu bg-base-200 text-base-content min-h-full w-80 p-4">
                    <span class="font-bold text-xl mb-5">ADMIN SIDEBAR</span>
                    <!-- Sidebar content here -->
                    <li><a href="../admin/adminDashboard.php">Products</a></li>
                    <li><a>Reports</a></li>
                    <li><a>Settings</a></li>
                    <li class="mt-auto">
                        <a href="../auth/logout.php" class="text-error hover:bg-error hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </section>
    <div class="flex justify-center item-center">
        <span class="font-bold text-xl">Admin Dashboard</span>
    </div>
</nav>