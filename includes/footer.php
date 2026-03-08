        </div> <!-- End Main Content area from header.php -->
    </div> <!-- End Wrapper -->

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebarBtn = document.getElementById('sidebarCollapse');
            if (sidebarBtn) {
                sidebarBtn.addEventListener('click', function () {
                    document.getElementById('sidebar').classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>
