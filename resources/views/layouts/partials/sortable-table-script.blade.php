<script type="text/javascript">
    (() => {
        const tables = document.querySelectorAll('[data-sortable-table]');

        const cellValue = (row, index) => {
            const cell = row.cells[index];
            return cell ? cell.textContent.trim() : '';
        };

        const normalize = (value) => {
            const numeric = Number(value.replace(/[$,%\s,]/g, ''));
            return Number.isNaN(numeric) || value === '' ? value.toLowerCase() : numeric;
        };

        const compareValues = (left, right) => {
            if (typeof left === 'number' && typeof right === 'number') {
                return left - right;
            }

            return String(left).localeCompare(String(right), undefined, {
                numeric: true,
                sensitivity: 'base'
            });
        };

        const sortTable = (table, columnIndex, direction) => {
            const body = table.tBodies[0];
            const rows = Array.from(body ? body.rows : []);
            const multiplier = direction === 'desc' ? -1 : 1;

            rows.sort((leftRow, rightRow) => {
                const left = normalize(cellValue(leftRow, columnIndex));
                const right = normalize(cellValue(rightRow, columnIndex));

                return compareValues(left, right) * multiplier;
            });

            rows.forEach((row) => body.appendChild(row));
            table.querySelectorAll('th').forEach((header) => {
                header.removeAttribute('aria-sort');
            });

            if (table.tHead && table.tHead.rows[0] && table.tHead.rows[0].cells[columnIndex]) {
                table.tHead.rows[0].cells[columnIndex].setAttribute('aria-sort', direction === 'desc' ? 'descending' : 'ascending');
            }
        };

        tables.forEach((table) => {
            const headers = Array.from(table.tHead ? table.tHead.rows[0].cells : []);
            const defaultSort = (table.dataset.sortDefault || '').split(':');

            headers.forEach((header, index) => {
                header.tabIndex = 0;
                header.style.cursor = 'pointer';
                header.title = 'Sort column';
                header.dataset.sortDirection = 'asc';
                header.addEventListener('click', () => {
                    const direction = header.dataset.sortDirection === 'asc' ? 'desc' : 'asc';
                    header.dataset.sortDirection = direction;
                    sortTable(table, index, direction);
                });
                header.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        header.click();
                    }
                });
            });

            if (defaultSort.length === 2) {
                sortTable(table, Number(defaultSort[0]), defaultSort[1] === 'desc' ? 'desc' : 'asc');
            }
        });
    })();
</script>
