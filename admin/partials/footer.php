</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('[data-password-target]').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var input = document.getElementById(cb.getAttribute('data-password-target'));
        if (!input) return;
        input.type = cb.checked ? 'text' : 'password';
    });
});
</script>

</body>
</html>