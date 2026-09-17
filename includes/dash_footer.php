        </div><!-- /.dash-content -->
    </div><!-- /.dash-main -->
</div><!-- /.dash-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    if (btn && sidebar) {
        btn.addEventListener('click', function () { sidebar.classList.toggle('show'); });
        document.addEventListener('click', function (e) {
            if (window.innerWidth < 768 && sidebar.classList.contains('show') &&
                !sidebar.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
});
</script>
</body>
</html>
