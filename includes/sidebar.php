<nav id="sidebar">
    <div class="sidebar-header d-flex align-items-center justify-content-center">
        <i class="bi bi-mortarboard-fill fs-3 me-2 text-primary"></i>
        <h4 class="mb-0">SMS Panel</h4>
    </div>

    <ul class="list-unstyled components mt-3">
        <li class="active">
            <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        </li>
        <li>
            <a href="#studentSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="bi bi-people-fill me-2"></i> Students
            </a>
            <ul class="collapse list-unstyled bg-dark" id="studentSubmenu">
                <li>
                    <a href="modules/admission/index.php" class="ps-5"><i class="bi bi-person-plus me-2"></i> New Admission</a>
                </li>
                <li>
                    <a href="modules/students/index.php" class="ps-5"><i class="bi bi-list-ul me-2"></i> Student List</a>
                </li>
            </ul>
        </li>
        <li>
            <a href="modules/classes/index.php"><i class="bi bi-building me-2"></i> Classes</a>
        </li>
        <li>
            <a href="modules/marks/index.php"><i class="bi bi-journal-text me-2"></i> Exams & Marks</a>
        </li>
        <li>
            <a href="modules/tc/index.php"><i class="bi bi-file-earmark-text me-2"></i> Transfer Certificates</a>
        </li>
        <li>
            <a href="modules/reports/index.php"><i class="bi bi-bar-chart-fill me-2"></i> Reports</a>
        </li>
        
        <?php if ($_SESSION['role'] === 'Admin'): ?>
        <li class="mt-4">
            <a href="#adminSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle text-warning">
                <i class="bi bi-gear-fill me-2"></i> Settings
            </a>
            <ul class="collapse list-unstyled bg-dark" id="adminSubmenu">
                <li>
                    <a href="modules/users/index.php" class="ps-5 text-warning"><i class="bi bi-person-badge me-2"></i> Manage Users</a>
                </li>
            </ul>
        </li>
        <?php endif; ?>
    </ul>
</nav>
