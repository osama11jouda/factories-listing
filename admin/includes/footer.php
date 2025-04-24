            </main>
        </div>
    </div>

    <footer class="bg-light text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Syrian Factories Admin Panel</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
            
            // Fix for the active state of sidebar links
            var currentLocation = window.location.href;
            $('.list-group-item').each(function() {
                var linkHref = $(this).attr('href');
                if (currentLocation.indexOf(linkHref) > -1) {
                    $(this).addClass('active');
                }
            });
        });
    </script>
</body>
</html>