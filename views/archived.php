<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Archived Records - Macro Access</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .archive-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .archive-header h2 {
            color: white;
            margin: 0;
            font-size: 22px;
        }
        .date-filter {
            display: flex;
            gap: 15px;
            align-items: center;
            background: rgba(255,255,255,0.1);
            padding: 12px 20px;
            border-radius: 12px;
            backdrop-filter: blur(5px);
        }
        .date-filter label {
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        .date-filter input {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            background: white;
            font-size: 12px;
        }
        .btn-restore-all {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 13px;
        }
        .btn-restore-all:hover {
            background: #219a52;
            transform: scale(1.02);
        }
        .empty-archive {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 12px;
            color: #888;
        }
        .empty-archive span {
            font-size: 50px;
            display: block;
            margin-bottom: 15px;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .btn-restore {
            background: #27ae60;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 11px;
            transition: background 0.3s;
        }
        .btn-restore:hover {
            background: #219a52;
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
        <li><a href="index.php?action=records">RECORDS</a></li>
        <li><a href="index.php?action=logout">LOG OUT</a></li>
    </ul>
</nav>

<div class="main-content">

    <div class="archive-header">
        <h2>ARCHIVED RECORDS</h2>
        
        <div class="date-filter">
            <label>📅 FROM</label>
            <input type="date" name="from" id="dateFrom" value="<?= $_GET['from'] ?? '' ?>">
            <label>📅 TO</label>
            <input type="date" name="to" id="dateTo" value="<?= $_GET['to'] ?? '' ?>">
        </div>
        
        <?php if (!empty($records)): ?>
        <button class="btn-restore-all" onclick="restoreAll()">
            RESTORE ALL
        </button>
        <?php endif; ?>
    </div>

    <div class="white-card table-card">
        
        <?php if (!empty($records)): ?>
        <table class="records-table">
            <thead>
                <tr>
                    <th>CLIENT NAME</th>
                    <th>DATE TESTED</th>
                    <th>METH RESULT</th>
                    <th>THC RESULT</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['client_name']) ?></div>
                    <td><?= date('Y-m-d', strtotime($row['date_tested'])) ?></div>
                    <td class="<?= $row['meth_result'] === 'POSITIVE' ? 'status-pos' : 'status-neg' ?>">
                        <?= $row['meth_result'] ?>
                    </div>
                    <td class="<?= $row['thc_result'] === 'POSITIVE' ? 'status-pos' : 'status-neg' ?>">
                        <?= $row['thc_result'] ?>
                    </div>
                    <td class="action-buttons">
                        <button class="btn-restore" onclick="restoreRecord(<?= $row['id'] ?>)">
                            RESTORE
                        </button>
                    </div>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-archive">
            <span>📭</span>
            <p>No archived records found.</p>
            <p style="font-size: 12px; margin-top: 10px;">Archived records will appear here when you archive them from the Records page.</p>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<script>
// Auto-refresh when date changes
document.getElementById('dateFrom').addEventListener('change', function() {
    applyDateFilter();
});
document.getElementById('dateTo').addEventListener('change', function() {
    applyDateFilter();
});

function applyDateFilter() {
    const from = document.getElementById('dateFrom').value;
    const to = document.getElementById('dateTo').value;
    let url = 'index.php?action=archived_records';
    if (from) url += '&from=' + from;
    if (to) url += '&to=' + to;
    window.location.href = url;
}

function restoreRecord(id) {
    if (confirm('Are you sure you want to restore this record?')) {
        window.location.href = 'index.php?action=restore_record&id=' + id;
    }
}

function restoreAll() {
    if (confirm('⚠️ Are you sure you want to restore ALL archived records? This action cannot be undone.')) {
        window.location.href = 'index.php?action=restore_all_records';
    }
}
</script>

<?php include 'views/footer.php'; ?>
</body>
</html>