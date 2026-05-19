<?php include 'views/header.php'; ?>

<?php

if (!isAdmin()) {
    header("Location: index.php?action=dashboard&error=unauthorized");
    exit();
}

// Get records from database
$stmt = $db->query("SELECT * FROM test_records WHERE is_archived = 0 ORDER BY date_tested DESC");
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Records - Macro Access</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .btn-edit-record {
            background: #f39c12;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 3px;
            font-size: 11px;
        }
        .btn-edit-record:hover {
            background: #e67e22;
        }
        .action-cell {
            white-space: nowrap;
        }
    </style>
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <div class="nav-logo">
            <a href="index.php?action=dashboard" style="text-decoration: none; color: inherit;">
                🔬 MACRO ACCESS
            </a>
        </div>

        <ul class="nav-links">
            <li><a href="index.php?action=account">ACCOUNT</a></li>
            <li><a href="index.php?action=reports">REPORTS</a></li>
            <li><a href="index.php?action=analytics">ANALYTICS</a></li>
            <li><a href="index.php?action=account" class="active" style="text-decoration: underline !important;">RECORDS</a></li>
            <li><a href="index.php?action=logout">LOG OUT</a></li>
        </ul>
    </nav>

    <div class="main-content">
       <h2 style="color: white;">RECORDS</h2>

        <!-- CENTERED BUT WIDER CONTAINER -->
        <div style="max-width: 1400px; margin: 0 auto; padding: 0 20px;">
            
            <!-- ADD RECORD FORM -->
            <form action="index.php?action=add_record" method="POST" enctype="multipart/form-data">
                <div class="entry-grid">
                    <!-- INFORMATION CARD -->
                    <div class="white-card entry-card">
                        <div class="card-header">INFORMATION</div>
                        <div class="card-body">
                            <input type="text" name="client_name" placeholder="CLIENT NAME" class="styled-input full-width" required>
                            <div class="info-row">
                                <label for="client_photo" class="image-upload-label">
                                    <div class="image-box" id="imagePreview">
                                        <i class="fa-regular fa-image" id="placeholderIcon"></i>
                                        <img src="" id="previewImg" style="display:none; width:100%; height:100%; object-fit:cover; border-radius:4px;">
                                    </div>
                                    <input type="file" name="client_photo" id="client_photo" accept="image/*" style="display:none;" onchange="previewFile()">
                                </label>
                                <div class="sub-inputs">
                                    <div class="mini-row">
                                        <input type="number" name="age" id="age" placeholder="AGE" class="styled-input" readonly>
                                        <select name="sex" class="styled-input" required>
                                            <option value="M">MALE</option>
                                            <option value="F">FEMALE</option>
                                        </select>
                                    </div>
                                    <input type="date" name="birth_date" id="birth_date" class="styled-input full-width" required onchange="calculateAge()">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RESULT CARD -->
                    <div class="white-card entry-card">
                        <div class="card-header">RESULT</div>
                        <div class="card-body">
                            <input type="text" name="company_name" placeholder="COMPANY NAME" class="styled-input full-width">
                            <select name="meth_result" class="styled-input full-width">
                                <option value="NEGATIVE">METH NEGATIVE</option>
                                <option value="POSITIVE">METH POSITIVE</option>
                                <option value="INVALID">INVALID</option>
                            </select>
                            <select name="thc_result" class="styled-input full-width">
                                <option value="NEGATIVE">THC NEGATIVE</option>
                                <option value="POSITIVE">THC POSITIVE</option>
                                <option value="INVALID">INVALID</option>
                            </select>
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div class="white-card entry-card actions-card">
                        <div class="card-header">ACTIONS</div>
                        <div class="card-body action-btns">
                            <button type="submit" class="btn-blue-action">➕ ADD</button>
                            <button type="button" class="btn-blue-action btn-disabled" id="printSelectedBtn" onclick="printSelectedRecord()">🖨️ PRINT</button>
                            <a href="index.php?action=archived_records" style="width:100%;">
                                <button type="button" class="btn-blue-action">📦 ARCHIVE LIST</button>
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- TABLE WITH PAGINATION AND LIVE SEARCH -->
            <div class="white-card table-card">
                <div class="search-container" style="justify-content: flex-start;">
                    <div class="search-left">
                        <input type="text" id="searchInput" placeholder="🔍 Search by client name..." class="search-input" onkeyup="performSearch()">
                    </div>
                </div>

                <div style="overflow-x: auto; width: 100%;">
                    <table class="records-table" style="width: 100%; min-width: 800px;">
                        <thead>
                            <tr>
                                <th class="sortable-header" data-sort="client_name">CLIENT NAME</th>
                                <th class="sortable-header" data-sort="date_tested">DATE TESTED</th>
                                <th class="sortable-header" data-sort="meth_result">METH (METHAMPHETAMINE)</th>
                                <th class="sortable-header" data-sort="thc_result">THC (TETRAHYDROCANNABINOL)</th>
                                <th>ACTION</th>
                            <tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>

                <div class="pagination-container">
                    <div class="records-info" id="recordsInfo">Showing 0 to 0 of 0 entries</div>
                    <div class="pagination" id="pagination"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Record Modal -->
    <div id="editRecordModal" class="modal-overlay" style="display: none;">
        <div class="modal-content edit-blue-card" style="width: 500px; max-width: 90%;">
            <div class="modal-header">
                <h3>✏️ EDIT RECORD</h3>
                <hr>
            </div>
            <form action="index.php?action=update_record" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="record_id" id="edit_record_id">
                <div class="modal-body">
                    <div class="input-group">
                        <label>CLIENT NAME</label>
                        <input type="text" name="client_name" id="edit_client_name" class="modal-input" required>
                    </div>
                    <div class="input-group">
                        <label>COMPANY NAME</label>
                        <input type="text" name="company_name" id="edit_company_name" class="modal-input">
                    </div>
                    <div class="input-group">
                        <label>AGE</label>
                        <input type="number" name="age" id="edit_age" class="modal-input" readonly>
                    </div>
                    <div class="input-group">
                        <label>BIRTH DATE</label>
                        <input type="date" name="birth_date" id="edit_birth_date" class="modal-input" onchange="updateEditAge()">
                    </div>
                    <div class="input-group">
                        <label>SEX</label>
                        <select name="sex" id="edit_sex" class="modal-input">
                            <option value="M">MALE</option>
                            <option value="F">FEMALE</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>METH RESULT</label>
                        <select name="meth_result" id="edit_meth_result" class="modal-input">
                            <option value="NEGATIVE">NEGATIVE</option>
                            <option value="POSITIVE">POSITIVE</option>
                            <option value="INVALID">INVALID</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>THC RESULT</label>
                        <select name="thc_result" id="edit_thc_result" class="modal-input">
                            <option value="NEGATIVE">NEGATIVE</option>
                            <option value="POSITIVE">POSITIVE</option>
                            <option value="INVALID">INVALID</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>PHOTO</label>
                        <input type="file" name="client_photo" id="edit_client_photo" accept="image/*" class="modal-input">
                        <small style="color: #ccc; display: block; margin-top: 5px;">Leave empty to keep current photo</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal('editRecordModal')">CANCEL</button>
                    <button type="submit" class="btn-modal-save">SAVE CHANGES</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'views/modals/view_record.php'; ?>

    <script>
    // ============================================
    // DATA FROM PHP
    // ============================================
    let allRecords = <?php echo json_encode($records ?? []); ?>;
    let filteredRecords = [...allRecords];
    let currentPage = 1;
    let entriesPerPage = 5;
    let currentSort = { column: 'date_tested', direction: 'desc' };
    let selectedRecord = null;
    let selectedRow = null;

    // ============================================
    // INITIALIZATION
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        sortData();
        renderTable();
        
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

    function performSearch() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        
        if (searchTerm === '') {
            filteredRecords = [...allRecords];
        } else {
            filteredRecords = allRecords.filter(record => {
                return record.client_name && record.client_name.toLowerCase().includes(searchTerm);
            });
        }
        
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
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:30px;">No records found.';
        } else {
            pageRecords.forEach(record => {
                const row = tbody.insertRow();
                row.className = 'record-row';
                row.onclick = () => selectRecord(record, row);
                
                row.insertCell(0).innerHTML = escapeHtml(record.client_name || '');
                row.insertCell(1).innerHTML = record.date_tested ? new Date(record.date_tested).toLocaleDateString() : '';
                row.insertCell(2).innerHTML = `<span class="${record.meth_result === 'POSITIVE' ? 'status-pos' : 'status-neg'}">${record.meth_result || ''}</span>`;
                row.insertCell(3).innerHTML = `<span class="${record.thc_result === 'POSITIVE' ? 'status-pos' : 'status-neg'}">${record.thc_result || ''}</span>`;
                row.insertCell(4).innerHTML = `
                    <div class="action-cell">
    <button type="button" class="btn-action btn-view" onclick='event.stopPropagation(); openViewModal(${JSON.stringify(record).replace(/'/g, "&#39;")})'>👁️ VIEW</button>
    <button type="button" class="btn-action btn-edit" onclick='event.stopPropagation(); openEditModal(${JSON.stringify(record).replace(/'/g, "&#39;")})'>✏️ EDIT</button>
    <button type="button" class="btn-action btn-archive" onclick='event.stopPropagation(); archiveRecord(${record.id})'>📦 ARCHIVE</button>
</div>
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

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    function selectRecord(data, rowElement) {
        selectedRecord = data;
        if (selectedRow) selectedRow.classList.remove('selected-row');
        selectedRow = rowElement;
        selectedRow.classList.add('selected-row');
        const printBtn = document.getElementById('printSelectedBtn');
        if (printBtn) {
            printBtn.disabled = false;
            printBtn.classList.remove('btn-disabled');
        }
    }

    function calculateAge() {
        const birthDate = document.getElementById('birth_date').value;
        if (!birthDate) return;
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        document.getElementById('age').value = age;
    }

    function updateEditAge() {
        const birthDate = document.getElementById('edit_birth_date').value;
        if (!birthDate) return;
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        document.getElementById('edit_age').value = age;
    }

    function previewFile() {
        const preview = document.getElementById('previewImg');
        const icon = document.getElementById('placeholderIcon');
        const fileInput = document.getElementById('client_photo');
        const file = fileInput.files[0];
        const reader = new FileReader();
        reader.onloadend = function() {
            preview.src = reader.result;
            preview.style.display = "block";
            icon.style.display = "none";
        }
        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = "";
            preview.style.display = "none";
            icon.style.display = "block";
        }
    }

    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function openViewModal(data) {
        selectedRecord = data;
        const printBtn = document.getElementById('printSelectedBtn');
        if (printBtn) {
            printBtn.disabled = false;
            printBtn.classList.remove('btn-disabled');
        }
        
        document.getElementById('view-client-name').innerText = data.client_name;
        document.getElementById('view-age').innerText = data.age || 'N/A';
        document.getElementById('view-dob').innerText = data.birth_date || 'N/A';
        
        let sexText = 'N/A';
        if (data.sex === 'M') sexText = 'MALE';
        else if (data.sex === 'F') sexText = 'FEMALE';
        document.getElementById('view-sex').innerText = sexText;
        
        document.getElementById('view-company').innerText = data.company_name || 'N/A';
        document.getElementById('view-date-meth').innerText = data.date_tested;
        document.getElementById('view-result-meth').innerText = data.meth_result;
        document.getElementById('view-date-thc').innerText = data.date_tested;
        document.getElementById('view-result-thc').innerText = data.thc_result;
        
        document.getElementById('view-result-meth').className = data.meth_result === 'POSITIVE' ? 'status-pos' : 'status-neg';
        document.getElementById('view-result-thc').className = data.thc_result === 'POSITIVE' ? 'status-pos' : 'status-neg';
        
        const profileImg = document.getElementById('view-profile-img');
        if (data.photo_path && data.photo_path !== null && data.photo_path !== '') {
            profileImg.src = "uploads/" + data.photo_path.split('uploads/').pop();
        } else {
            profileImg.src = "assets/img/placeholder-user.png";
        }
        
        openModal('viewRecordModal');
    }

    function openEditModal(data) {
        document.getElementById('edit_record_id').value = data.id;
        document.getElementById('edit_client_name').value = data.client_name || '';
        document.getElementById('edit_company_name').value = data.company_name || '';
        document.getElementById('edit_age').value = data.age || '';
        document.getElementById('edit_birth_date').value = data.birth_date || '';
        document.getElementById('edit_sex').value = data.sex || 'M';
        document.getElementById('edit_meth_result').value = data.meth_result || 'NEGATIVE';
        document.getElementById('edit_thc_result').value = data.thc_result || 'NEGATIVE';
        openModal('editRecordModal');
    }

    function printSelectedRecord() {
    if (!selectedRecord) {
        alert("Please select a record first.");
        return;
    }

    const record = selectedRecord;
    
    let photoUrl = '';
    if (record.photo_path && record.photo_path !== '') {
        let filename = record.photo_path;
        if (filename.includes('/')) {
            filename = filename.split('/').pop();
        }
        photoUrl = `uploads/${filename}`;
    }
    
    const w = window.open('', '', 'width=1000,height=800');
    w.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Drug Test Report - Macro Access</title>
            <style>
                @page {
                    margin: 1.5cm;
                }
                body {
                    font-family: Arial, sans-serif;
                    padding: 0;
                    background: #fff;
                    margin: 0;
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
                .client-section {
                    display: flex;
                    gap: 25px;
                    margin-bottom: 30px;
                    padding: 20px;
                    background: #f8f9fa;
                    border-radius: 12px;
                }
                .photo-box {
                    width: 120px;
                    height: 120px;
                    border: 2px solid #ddd;
                    border-radius: 12px;
                    overflow: hidden;
                    background: #fff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .photo-box img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
                .photo-placeholder {
                    font-size: 48px;
                    color: #ccc;
                }
                .client-details h2 {
                    margin: 0 0 10px 0;
                    color: #1e3a8a;
                    font-size: 20px;
                }
                .client-details p {
                    margin: 5px 0;
                    font-size: 13px;
                }
                .company-section {
                    text-align: center;
                    margin-bottom: 25px;
                    padding: 12px;
                    background: #f0f7ff;
                    border-radius: 8px;
                }
                .company-section label {
                    font-size: 10px;
                    color: #666;
                    display: block;
                }
                .company-section h3 {
                    margin: 5px 0;
                    font-size: 16px;
                    color: #1e3a8a;
                }
                .results-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 30px;
                }
                .results-table th {
                    background: #2c3e50;
                    color: white;
                    padding: 10px;
                    border: 1px solid #000;
                    text-align: left;
                    font-size: 12px;
                }
                .results-table td {
                    padding: 8px;
                    border: 1px solid #ddd;
                    font-size: 12px;
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
                /* Second page content styles */
                .acknowledgement-content {
                    padding: 10px;
                    line-height: 1.2;
                }
                .acknowledgement-content h3 {
                    color: #1e3a8a;
                    margin-bottom: 10px;
                    font-size: 16px;
                }
                .acknowledgement-content h4 {
                    color: #333;
                    margin: 12px 0 5px 0;
                    font-size: 13px;
                }
                .acknowledgement-content p {
                    margin: 5px 0;
                    font-size: 11px;
                    color: #333;
                }
                .acknowledgement-content ul {
                    margin: 5px 0 5px 20px;
                }
                .acknowledgement-content li {
                    margin: 3px 0;
                    font-size: 11px;
                    color: #333;
                }
                .signature-section {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                }
                .signature-line {
                    display: flex;
                    justify-content: space-between;
                    margin-top: 10px;
                }
                .signature-item {
                    text-align: center;
                    width: 45%;
                }
                .signature-item hr {
                    margin-top: 30px;
                    border: none;
                    border-top: 1px solid #000;
                }
                .signature-item p {
                    margin: 10px 0;
                    font-size: 11px;
                }
                /* Force page break between page 1 and page 2 */
                .page-break {
                    page-break-before: always;
                }
            </style>
        </head>
        <body>
            <!-- PAGE 1 - TEST RESULTS -->
            <div>
                <div class="print-header">
                    <div class="print-logo">🔬</div>
                    <h1>MACRO ACCESS DRUG TESTING CENTER</h1>
                    <p>Official Drug Test Report</p>
                </div>
                <div class="print-date">Report Generated: ${new Date().toLocaleString()}</div>
                <div class="client-section">
                    <div class="photo-box">${photoUrl ? `<img src="${photoUrl}" onerror="this.parentElement.innerHTML='<div class=\\'photo-placeholder\\'>🔬</div>'">` : `<div class="photo-placeholder">🔬</div>`}</div>
                    <div class="client-details">
                        <h2>${escapeHtml(record.client_name || '')}</h2>
                        <p><strong>Age:</strong> ${record.age || 'N/A'}</p>
                        <p><strong>Birth Date:</strong> ${record.birth_date || 'N/A'}</p>
                        <p><strong>Sex:</strong> ${record.sex === 'M' ? 'Male' : (record.sex === 'F' ? 'Female' : 'N/A')}</p>
                    </div>
                </div>
                <div class="company-section">
                    <label>COMPANY</label>
                    <h3>${escapeHtml(record.company_name || 'N/A')}</h3>
                </div>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>TYPE OF DRUG</th>
                            <th>DATE TESTED</th>
                            <th>RESULT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Methamphetamine (METH)</strong>
                            <td>${record.date_tested || 'N/A'}
                            <td class="${record.meth_result === 'POSITIVE' ? 'status-pos' : 'status-neg'}">${record.meth_result || 'N/A'}
                        </tr>
                        <tr>
                            <td><strong>Tetrahydrocannabinol (THC)</strong>
                            <td>${record.date_tested || 'N/A'}
                            <td class="${record.thc_result === 'POSITIVE' ? 'status-pos' : 'status-neg'}">${record.thc_result || 'N/A'}
                        </tr>
                    </tbody>
                </table>
                <div class="print-footer">This is a computer-generated report. No signature is required.<br>Macro Access Drug Testing Center | Since 2004</div>
            </div>
            
            <!-- PAGE BREAK FORCE -->
            <div style="page-break-before: always;"></div>
            
            <!-- PAGE 2 - ACKNOWLEDGEMENT AND SECURITY LETTER -->
            <div>
                <div class="print-header">
                    <div class="print-logo">🔬</div>
                    <h1>MACRO ACCESS DRUG TESTING CENTER</h1>
                    <p>Official Drug Test Report - Page 2 of 2</p>
                </div>
                <div class="print-date">Report Generated: ${new Date().toLocaleString()}</div>
                
                <div class="acknowledgement-content">
                    <h3>Dear Sir/Madam,</h3>
                    
                    <p>This letter serves to confirm and assure you that your drug test results conducted at Macro Access Drug Testing Center will be released to you in a secure and confidential manner. The center strictly adheres to a policy that your results will NOT be processed, altered, modified, or changed without your explicit knowledge and consent.</p>
                    
                    <p>Please read and acknowledge the following security measures regarding the release of your results:</p>
                    
                    <h4>1. Direct and Unmodified Release</h4>
                    <p>- The drug test result you will receive is the exact, unaltered outcome of the laboratory analysis.</p>
                    <p>- No staff member or administrator of the Web-Based Drug Testing Management System has the authority to modify, delete, or change your result after it has been finalized.</p>
                    <p>- Any request to change or dispute a result will require a formal process, including possible retesting, and will never be done through simple digital editing.</p>
                    
                    <h4>2. Release of Results to You Only</h4>
                    <p>- Your official drug test result will be released only to you or to a person you have authorized in writing.</p>
                    <p>- Results will not be emailed, shared, or processed for any third party without your signed consent.</p>
                    
                    <h4>3. No Automated Processing Without Your Consent</h4>
                    <p>- The system does not automatically send, share, or process your results for analytics, external reporting, or any other purpose unless you have given separate written permission.</p>
                    <p>- If you request a reprint of a lost or damaged result, the system will regenerate the exact same unmodified result from the secure database.</p>
                    
                    <h4>4. Your Rights</h4>
                    <p>- You have the right to receive your result directly from the center.</p>
                    <p>- You have the right to request a physical or digital copy of your result at any time.</p>
                    <p>- You have the right to refuse any processing, sharing, or modification of your result.</p>
                    
                    <div class="signature-section">
                        <div class="signature-line">
                            <div class="signature-item">
                                <hr>
                                <p>Client's Signature</p>
                                <p style="font-size: 10px; color: #666;">Date: _________________</p>
                            </div>
                            <div class="signature-item">
                                <hr>
                                <p>Authorized Representative</p>
                                <p style="font-size: 10px; color: #666;">Macro Access Drug Testing Center</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="print-footer">This is a computer-generated report. No signature is required.<br>Macro Access Drug Testing Center | Since 2004</div>
            </div>
        </body>
        </html>
    `);
    w.document.close();
    w.focus();
    setTimeout(() => { w.print(); w.close(); }, 500);
}

    function archiveRecord(id) {
        const confirmArchive = confirm("Archive this record?");
        if (confirmArchive) {
            window.location.href = "index.php?action=archive_record&id=" + id;
        }
    }
    </script>

    <?php include 'views/footer.php'; ?>
    <!-- Toast Notification Script -->
<style>
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    min-width: 300px;
    padding: 15px 20px;
    border-radius: 8px;
    color: white;
    font-size: 14px;
    font-weight: 500;
    z-index: 9999;
    animation: slideInRight 0.3s ease-out;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}
.toast-success {
    background: #27ae60;
    border-left: 5px solid #1e8449;
}
.toast-error {
    background: #e74c3c;
    border-left: 5px solid #c0392b;
}
.toast-close {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    opacity: 0.8;
}
.toast-close:hover {
    opacity: 1;
}
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; visibility: hidden; }
}
</style>

<script>
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `<span>${message}</span><button class="toast-close" onclick="this.parentElement.remove()">✕</button>`;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => { if (toast && toast.remove) toast.remove(); }, 300);
    }, 3000);
}

<?php if(isset($_SESSION['toast_success'])): ?>
    showToast("<?= addslashes($_SESSION['toast_success']) ?>", 'success');
    <?php unset($_SESSION['toast_success']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['toast_error'])): ?>
    showToast("<?= addslashes($_SESSION['toast_error']) ?>", 'error');
    <?php unset($_SESSION['toast_error']); ?>
<?php endif; ?>
</script>

</body>
</html>