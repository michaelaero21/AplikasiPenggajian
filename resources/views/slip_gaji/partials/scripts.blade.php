<script>
/* ====== LIVE SEARCH DENGAN DEBOUNCE ====== */
document.getElementById('search-input')
        .addEventListener('input', debounce(searchTable, 300));

function searchTable() {
    const keyword = document
                    .getElementById('search-input')
                    .value
                    .toLowerCase();

    const rows = document.querySelectorAll('#karyawan-table tbody tr');

    rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('td'));
        const match = cells.some(td =>
            td.innerText.toLowerCase().includes(keyword)
        );
        row.style.display = match ? '' : 'none';
    });
}

/* util: debounce */
function debounce(fn, delay) {
    let timer;
    return function () {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, arguments), delay);
    };
}

</script>
