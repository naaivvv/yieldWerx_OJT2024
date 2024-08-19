// Probe dropdown
document.getElementById('select-all-abbrev').addEventListener('change', function(e) {
    document.querySelectorAll('.filter-checkbox-abbrev').forEach(checkbox => checkbox.checked = e.target.checked);
});

