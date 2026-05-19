<?php include 'views/header.php'; ?>
<?php
// Get all records (non-archived)
$stmt = $db->query("SELECT * FROM test_records ORDER BY date_tested DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Reports - Macro Access</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        @media print {
            .navbar, .search-container, .footer, .pagination-container, 
            .btn-blue-action, .white-card, .main-content > div > div:first-child,
            .sortable-header::after, .print-hide {
                display: none !important;
            }
            .print-container {
                display: block !important;
            }
            body, .dashboard-body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            @page {
                margin: 1.5cm;
            }
            .print-header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 3px solid #1e3a8a;
            }
            .print-logo {
                font-size: 50px;
                margin-bottom: 10px;
            }
            .print-header h1 {
                color: #1e3a8a;
                margin: 0;
                font-size: 22px;
            }
            .print-header p {
                color: #666;
                margin: 5px 0 0;
                font-size: 12px;
            }
            .print-date {
                text-align: right;
                font-size: 11px;
                color: #666;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 1px solid #ddd;
            }
            .print-summary {
                margin-bottom: 20px;
                padding: 10px;
                background: #f5f5f5;
                font-size: 12px;
            }
            .print-table {
                width: 100%;
                border-collapse: collapse;
            }
            .print-table th {
                background: #2c3e50 !important;
                color: white !important;
                padding: 8px !important;
                border: 1px solid #000 !important;
                text-align: left;
            }
            .print-table td {
                padding: 3px !important;
                border: 1px solid #ddd !important;
            }
            .status-pos {
                color: red !important;
                font-weight: bold;
            }
            .status-neg {
                color: green !important;
                font-weight: bold;
            }
            .print-footer {
                text-align: center;
                margin-top: 30px;
                padding-top: 15px;
                border-top: 1px solid #ddd;
                font-size: 9px;
                color: #999;
            }
        }
        .print-container {
            display: none;
        }
    </style>
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <div class="nav-logo">
            <a href="index.php?action=dashboard" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;">
                <span>🔬</span> MACRO ACCESS
            </a>
        </div>
        <ul class="nav-links">
            <li><a href="index.php?action=account">ACCOUNT</a></li>
            <li><a href="index.php?action=account" class="active" style="text-decoration: underline !important;">REPORTS</a></li>
            <li><a href="index.php?action=analytics">ANALYTICS</a></li>
            <?php if (isAdmin()): ?>
                <li><a href="index.php?action=records">RECORDS</a></li>
            <?php endif; ?>
            <li><a href="index.php?action=logout">LOG OUT</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div style="max-width: 1400px; margin: 0 auto; padding: 0 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: white; margin: 0; font-size: 20px;">GENERATED REPORTS</h2>
                <button onclick="printCurrentView()" class="btn-blue-action" style="width: auto; padding: 5px 20px;">🖨️ PRINT REPORT</button>
            </div>

            <!-- SCREEN VIEW -->
            <div class="white-card" style="padding: 20px; overflow-x: auto;">
                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 20px; flex-wrap: wrap;">
                    <input type="text" id="searchInput" placeholder="🔍 Search by client or company..." class="search-input" style="width: 280px;">
                    <select id="methFilter" class="entries-select" onchange="applyFilters()">
                        <option value="all">METH: ALL</option>
                        <option value="POSITIVE">METH: POSITIVE</option>
                        <option value="NEGATIVE">METH: NEGATIVE</option>
                    </select>
                    <select id="thcFilter" class="entries-select" onchange="applyFilters()">
                        <option value="all">THC: ALL</option>
                        <option value="POSITIVE">THC: POSITIVE</option>
                        <option value="NEGATIVE">THC: NEGATIVE</option>
                    </select>
                </div>
                
                <div style="overflow-x: auto; width: 100%;">
                    <table class="records-table" id="recordsTable" style="width: 100%; min-width: 800px;">
                        <thead>
                            <tr>
                                <th class="sortable-header" data-sort="client_name">CLIENT NAME</th>
                                <th class="sortable-header" data-sort="company_name">COMPANY</th>
                                <th class="sortable-header" data-sort="date_tested">DATE TESTED</th>
                                <th class="sortable-header" data-sort="meth_result">METH RESULT</th>
                                <th class="sortable-header" data-sort="thc_result">THC RESULT</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>
                
                <div class="pagination-container">
                    <div class="records-info" id="recordsInfo">Showing 0 to 0 of 0 entries</div>
                    <div class="pagination" id="pagination"></div>
                </div>
            </div>

            <!-- PRINT TEMPLATE - Only visible when printing -->
            <div class="print-container">
                <div class="print-header">
                    <div class="print-logo">🔬</div>
                    <h1>MACRO ACCESS DRUG TESTING CENTER</h1>
                    <p>Official Drug Test Report</p>
                </div>
                
                <div class="print-date" id="printDate"></div>
                
                <div class="print-summary" id="printSummary"></div>
                
                <table class="print-table" id="printTable">
                    <thead>
                        <tr>
                            <th>CLIENT NAME</th>
                            <th>COMPANY</th>
                            <th>DATE TESTED</th>
                            <th>METH RESULT</th>
                            <th>THC RESULT</th>
                        </tr>
                    </thead>
                    <tbody id="printTableBody"></tbody>
                </table>
                
                <div class="print-footer">
                    This is a computer-generated report. No signature is required.<br>
                    Macro Access Drug Testing Center | Since 2004
                </div>
            </div>
        </div>
    </div>

    <script>    
    let allRecords = <?php echo json_encode($records ?? []); ?>;
    let filteredRecords = [...allRecords];
    let currentPage = 1;
    let entriesPerPage = 10;
    let currentSort = { column: 'date_tested', direction: 'desc' };
    let methFilterValue = 'all';
    let thcFilterValue = 'all';
    let searchTerm = '';
    
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('searchInput').addEventListener('input', function() {
            searchTerm = this.value.toLowerCase();
            applyFilters();
        });
        document.getElementById('methFilter').addEventListener('change', function() {
            methFilterValue = this.value;
            applyFilters();
        });
        document.getElementById('thcFilter').addEventListener('change', function() {
            thcFilterValue = this.value;
            applyFilters();
        });
        applyFilters();
        
        document.querySelectorAll('.sortable-header').forEach(header => {
            header.addEventListener('click', function() {
                const sortColumn = this.getAttribute('data-sort');
                if (currentSort.column === sortColumn) {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.column = sortColumn;
                    currentSort.direction = 'asc';
                }
                sortData();
                currentPage = 1;
                renderTable();
            });
        });
    });
    
    function applyFilters() {
        filteredRecords = allRecords.filter(record => {
            const matchesSearch = searchTerm === '' || 
                (record.client_name && record.client_name.toLowerCase().includes(searchTerm)) ||
                (record.company_name && record.company_name.toLowerCase().includes(searchTerm));
            const matchesMeth = methFilterValue === 'all' || (record.meth_result === methFilterValue);
            const matchesThc = thcFilterValue === 'all' || (record.thc_result === thcFilterValue);
            return matchesSearch && matchesMeth && matchesThc;
        });
        sortData();
        currentPage = 1;
        renderTable();
    }
    
    function sortData() {
        filteredRecords.sort((a, b) => {
            let valA = a[currentSort.column] || '';
            let valB = b[currentSort.column] || '';
            if (currentSort.column === 'date_tested') {
                valA = new Date(valA);
                valB = new Date(valB);
            }
            if (typeof valA === 'string') valA = valA.toLowerCase();
            if (typeof valB === 'string') valB = valB.toLowerCase();
            if (valA < valB) return currentSort.direction === 'asc' ? -1 : 1;
            if (valA > valB) return currentSort.direction === 'asc' ? 1 : -1;
            return 0;
        });
    }
    
    function renderTable() {
        const start = (currentPage - 1) * entriesPerPage;
        const pageRecords = filteredRecords.slice(start, start + entriesPerPage);
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        
        if (pageRecords.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:30px;">No records available.‹‹';
        } else {
            pageRecords.forEach(record => {
                tbody.innerHTML += `
                    <tr>
                        <td>${escapeHtml(record.client_name || '')}
                        <td>${escapeHtml(record.company_name || 'N/A')}
                        <td>${record.date_tested ? new Date(record.date_tested).toLocaleDateString() : ''}
                        <td class="${record.meth_result === 'POSITIVE' ? 'status-pos' : 'status-neg'}">${record.meth_result || ''}
                        <td class="${record.thc_result === 'POSITIVE' ? 'status-pos' : 'status-neg'}">${record.thc_result || ''}
                    </tr>
                `;
            });
        }
        
        const total = filteredRecords.length;
        const showingFrom = start + 1;
        const showingTo = Math.min(start + entriesPerPage, total);
        document.getElementById('recordsInfo').innerHTML = `Showing ${showingFrom} to ${showingTo} of ${total} entries`;
        renderPagination(Math.ceil(total / entriesPerPage));
        updateSortIndicators();
    }
    
    function renderPagination(totalPages) {
        const paginationDiv = document.getElementById('pagination');
        paginationDiv.innerHTML = '';
        if (totalPages <= 1) return;
        
        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '‹ Previous';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; renderTable(); } };
        paginationDiv.appendChild(prevBtn);
        
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                const pageBtn = document.createElement('button');
                pageBtn.innerHTML = i;
                pageBtn.className = i === currentPage ? 'active-page' : '';
                pageBtn.onclick = () => { currentPage = i; renderTable(); };
                paginationDiv.appendChild(pageBtn);
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                const dots = document.createElement('span');
                dots.innerHTML = '...';
                dots.style.padding = '0 5px';
                paginationDiv.appendChild(dots);
            }
        }
        
        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = 'Next ›';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; renderTable(); } };
        paginationDiv.appendChild(nextBtn);
    }
    
    function updateSortIndicators() {
        document.querySelectorAll('.sortable-header').forEach(header => {
            const sortColumn = header.getAttribute('data-sort');
            header.classList.remove('sort-asc', 'sort-desc');
            if (sortColumn === currentSort.column) {
                header.classList.add(currentSort.direction === 'asc' ? 'sort-asc' : 'sort-desc');
            }
        });
    }
    
    function printCurrentView() {
    // Get ONLY the records on the current page
    const start = (currentPage - 1) * entriesPerPage;
    let recordsToPrint = filteredRecords.slice(start, start + entriesPerPage);
    
    // Limit to 15 records max to ensure it fits on one page
    const MAX_RECORDS_PER_PAGE = 12;
    if (recordsToPrint.length > MAX_RECORDS_PER_PAGE) {
        recordsToPrint = recordsToPrint.slice(0, MAX_RECORDS_PER_PAGE);
    }
    
    const totalRecords = recordsToPrint.length;
    const methPositive = recordsToPrint.filter(r => r.meth_result === 'POSITIVE').length;
    const methNegative = recordsToPrint.filter(r => r.meth_result === 'NEGATIVE').length;
    const thcPositive = recordsToPrint.filter(r => r.thc_result === 'POSITIVE').length;
    const thcNegative = recordsToPrint.filter(r => r.thc_result === 'NEGATIVE').length;
    
    // Set print date
    const now = new Date();
    document.getElementById('printDate').innerHTML = `Generated: ${now.toLocaleDateString()} | Page ${currentPage} of ${Math.ceil(filteredRecords.length / entriesPerPage)}`;
    
    // Set summary
    document.getElementById('printSummary').innerHTML = `Records: ${start + 1}-${Math.min(start + entriesPerPage, filteredRecords.length)} of ${filteredRecords.length} | METH +:${methPositive} -:${methNegative} | THC +:${thcPositive} -:${thcNegative}`;
    
    // Build table
    let printHtml = '';
    recordsToPrint.forEach(record => {
        printHtml += `
            <tr>
                <td>${escapeHtml(record.client_name || '').substring(0, 20)}</div>
                <td>${escapeHtml(record.company_name || 'N/A').substring(0, 20)}</div>
                <td>${record.date_tested ? new Date(record.date_tested).toLocaleDateString() : ''}</div>
                <td class="${record.meth_result === 'POSITIVE' ? 'status-pos' : 'status-neg'}">${record.meth_result || ''}</div>
                <td class="${record.thc_result === 'POSITIVE' ? 'status-pos' : 'status-neg'}">${record.thc_result || ''}</div>
            </tr>
        `;
    });
    
    document.getElementById('printTableBody').innerHTML = printHtml;
    
    // Trigger print
    setTimeout(() => {
        window.print();
    }, 100);
}
    
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    </script>

    <?php include 'views/footer.php'; ?>
</body>
</html>