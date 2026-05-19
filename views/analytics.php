<?php include 'views/header.php'; ?>
<?php

// Get date range from POST or default to last 30 days
$end_date = isset($_POST['end_date']) && $_POST['end_date'] ? $_POST['end_date'] : date('Y-m-d');
$start_date = isset($_POST['start_date']) && $_POST['start_date'] ? $_POST['start_date'] : date('Y-m-d', strtotime('-30 days'));

// Prepare date filter condition
$dateCondition = "DATE(date_tested) BETWEEN :start_date AND :end_date";

// Get selected period type
$period_type = isset($_POST['period_type']) ? $_POST['period_type'] : 'month';

// ============================================
// 1. TOTAL TESTS BY DAY/MONTH/YEAR
// ============================================

$periodGroup = "";
if ($period_type === 'day') {
    $periodGroup = "DATE(date_tested) as period";
} elseif ($period_type === 'month') {
    $periodGroup = "DATE_FORMAT(date_tested, '%Y-%m') as period";
} else {
    $periodGroup = "YEAR(date_tested) as period";
}

$periodQuery = "SELECT $periodGroup, COUNT(*) as total_tests 
                FROM test_records 
                WHERE $dateCondition
                GROUP BY period 
                ORDER BY period ASC";
$periodStmt = $db->prepare($periodQuery);
$periodStmt->bindParam(':start_date', $start_date);
$periodStmt->bindParam(':end_date', $end_date);
$periodStmt->execute();
$periodData = $periodStmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// 2. METH RESULTS (Positive/Negative)
// ============================================
$methQuery = "SELECT 
                SUM(CASE WHEN meth_result = 'POSITIVE' THEN 1 ELSE 0 END) as meth_positive,
                SUM(CASE WHEN meth_result = 'NEGATIVE' THEN 1 ELSE 0 END) as meth_negative,
                SUM(CASE WHEN meth_result = 'INVALID' THEN 1 ELSE 0 END) as meth_invalid,
                COUNT(*) as total_meth
              FROM test_records 
              WHERE $dateCondition";
$methStmt = $db->prepare($methQuery);
$methStmt->bindParam(':start_date', $start_date);
$methStmt->bindParam(':end_date', $end_date);
$methStmt->execute();
$methResults = $methStmt->fetch(PDO::FETCH_ASSOC);

// ============================================
// 3. THC RESULTS (Positive/Negative)
// ============================================
$thcQuery = "SELECT 
                SUM(CASE WHEN thc_result = 'POSITIVE' THEN 1 ELSE 0 END) as thc_positive,
                SUM(CASE WHEN thc_result = 'NEGATIVE' THEN 1 ELSE 0 END) as thc_negative,
                SUM(CASE WHEN thc_result = 'INVALID' THEN 1 ELSE 0 END) as thc_invalid,
                COUNT(*) as total_thc
              FROM test_records 
              WHERE $dateCondition";
$thcStmt = $db->prepare($thcQuery);
$thcStmt->bindParam(':start_date', $start_date);
$thcStmt->bindParam(':end_date', $end_date);
$thcStmt->execute();
$thcResults = $thcStmt->fetch(PDO::FETCH_ASSOC);

// ============================================
// 4. GENDER STATISTICS (Male/Female)
// ============================================
$genderQuery = "SELECT 
                  SUM(CASE WHEN sex = 'M' THEN 1 ELSE 0 END) as male_count,
                  SUM(CASE WHEN sex = 'F' THEN 1 ELSE 0 END) as female_count,
                  SUM(CASE WHEN sex NOT IN ('M', 'F') OR sex IS NULL THEN 1 ELSE 0 END) as other_count,
                  COUNT(*) as total_gender
                FROM test_records 
                WHERE $dateCondition";
$genderStmt = $db->prepare($genderQuery);
$genderStmt->bindParam(':start_date', $start_date);
$genderStmt->bindParam(':end_date', $end_date);
$genderStmt->execute();
$genderResults = $genderStmt->fetch(PDO::FETCH_ASSOC);

// ============================================
// 5. TOTAL OVERALL COUNT
// ============================================
$totalQuery = "SELECT COUNT(*) as total_tests FROM test_records WHERE $dateCondition";
$totalStmt = $db->prepare($totalQuery);
$totalStmt->bindParam(':start_date', $start_date);
$totalStmt->bindParam(':end_date', $end_date);
$totalStmt->execute();
$totalResults = $totalStmt->fetch(PDO::FETCH_ASSOC);
$totalTests = $totalResults['total_tests'] ?? 0;

// Prepare data for charts
$periodLabels = [];
$periodCounts = [];
foreach ($periodData as $row) {
    $periodLabels[] = $row['period'];
    $periodCounts[] = $row['total_tests'];
}

$methPositive = $methResults['meth_positive'] ?? 0;
$methNegative = $methResults['meth_negative'] ?? 0;
$thcPositive = $thcResults['thc_positive'] ?? 0;
$thcNegative = $thcResults['thc_negative'] ?? 0;

$maleCount = $genderResults['male_count'] ?? 0;
$femaleCount = $genderResults['female_count'] ?? 0;

// Combined positive rate for any drug
$positiveQuery = "SELECT COUNT(*) as both_positive FROM test_records 
                  WHERE $dateCondition
                  AND (meth_result = 'POSITIVE' OR thc_result = 'POSITIVE')";
$positiveStmt = $db->prepare($positiveQuery);
$positiveStmt->bindParam(':start_date', $start_date);
$positiveStmt->bindParam(':end_date', $end_date);
$positiveStmt->execute();
$positiveResults = $positiveStmt->fetch(PDO::FETCH_ASSOC);
$anyPositive = $positiveResults['both_positive'] ?? 0;
$negativeTests = $totalTests - $anyPositive;

// Prepare JSON data for print function
$periodLabelsJson = json_encode($periodLabels);
$periodCountsJson = json_encode($periodCounts);
$startDateFormatted = date('M d, Y', strtotime($start_date));
$endDateFormatted = date('M d, Y', strtotime($end_date));
$periodTypeFormatted = ucfirst($period_type);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Analytics - Macro Access Drug Testing Center</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }
        .stat-card h3 {
            font-size: 28px;
            margin: 10px 0;
            color: #1e3a8a;
        }
        .stat-card p {
            color: #666;
            font-size: 12px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-card.positive h3 { color: #e74c3c; }
        .stat-card.negative h3 { color: #27ae60; }
        .chart-row {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 40px;
        }
        .chart-box {
            flex: 1;
            min-width: 280px;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .chart-box h4 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-size: 16px;
        }
        .chart-box canvas {
            max-height: 350px;
        }
        .legend-color {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 2px;
            margin-right: 5px;
        }
        
        /* Animation for numbers */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stat-card {
            animation: fadeInUp 0.5s ease-out forwards;
        }
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        
        .chart-box {
            animation: fadeInUp 0.5s ease-out forwards;
        }
        .chart-row:first-child .chart-box:first-child { animation-delay: 0.5s; }
        .chart-row:first-child .chart-box:last-child { animation-delay: 0.6s; }
        .chart-row:last-child .chart-box:first-child { animation-delay: 0.7s; }
        .chart-row:last-child .chart-box:last-child { animation-delay: 0.8s; }
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
        <li><a href="index.php?action=reports">REPORTS</a></li>
        <li><a href="index.php?action=account" class="active" style="text-decoration: underline !important;">ANALYTICS</a></li>
        <?php if (isAdmin()): ?>
            <li><a href="index.php?action=records">RECORDS</a></li>
        <?php endif; ?>
        <li><a href="index.php?action=logout">LOG OUT</a></li>
    </ul>
</nav>

<div class="main-content">
    <div class="analytics-header">
        <h2 style="color: white;">DRUG TESTING ANALYTICS</h2>
        <button onclick="printAnalyticsReport()" class="btn-blue-action" style="width: auto; padding: 5px 20px;">🖨️ PRINT REPORT</button>
    </div>
    
    <!-- FORM WITH AUTO-SUBMIT -->
    <form action="index.php?action=analytics" method="POST" class="date-range" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        FROM 
        <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="date-picker" required onchange="this.form.submit()">
        TO 
        <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="date-picker" required onchange="this.form.submit()">
        
        <select name="period_type" onchange="this.form.submit()" style="background: #ffffff; color: #333333; padding: 6px 12px; border-radius: 4px; border: 1px solid #aaaaaa; cursor: pointer; font-family: inherit; font-size: 12px;">
            <option value="day" <?= $period_type === 'day' ? 'selected' : '' ?>>📅 By Day</option>
            <option value="month" <?= $period_type === 'month' ? 'selected' : '' ?>>📆 By Month</option>
            <option value="year" <?= $period_type === 'year' ? 'selected' : '' ?>>📊 By Year</option>
        </select>
    </form>
</div>

<div class="white-card analytics-main-card">
    <!-- STATISTICS CARDS -->
    <div class="analytics-stats-grid">
        <div class="stat-card">
            <p>Total Tests</p>
            <h3><?= number_format($totalTests) ?></h3>
        </div>
        <div class="stat-card positive">
            <p>Positive Results (Any Drug)</p>
            <h3><?= number_format($anyPositive) ?></h3>
        </div>
        <div class="stat-card negative">
            <p>Negative Results</p>
            <h3><?= number_format($negativeTests) ?></h3>
        </div>
        <div class="stat-card">
            <p>Positive Rate</p>
            <h3><?= $totalTests > 0 ? round(($anyPositive / $totalTests) * 100, 1) : 0 ?>%</h3>
        </div>
    </div>

    <!-- METH BAR CHART -->
    <div class="chart-row">
        <div class="chart-box">
            <h4>METHAMPHETAMINE (METH) RESULTS</h4>
            <canvas id="methChart"></canvas>
            <div style="text-align: center; margin-top: 15px; font-size: 12px;">
                <span><span class="legend-color" style="background: #e74c3c;"></span> Positive: <?= $methPositive ?></span>
                <span style="margin-left: 15px;"><span class="legend-color" style="background: #27ae60;"></span> Negative: <?= $methNegative ?></span>
            </div>
        </div>
        
        <!-- THC BAR CHART -->
        <div class="chart-box">
            <h4>TETRAHYDROCANNABINOL (THC) RESULTS</h4>
            <canvas id="thcChart"></canvas>
            <div style="text-align: center; margin-top: 15px; font-size: 12px;">
                <span><span class="legend-color" style="background: #e74c3c;"></span> Positive: <?= $thcPositive ?></span>
                <span style="margin-left: 15px;"><span class="legend-color" style="background: #27ae60;"></span> Negative: <?= $thcNegative ?></span>
            </div>
        </div>
    </div>

    <!-- GENDER BAR CHART & TREND LINE CHART -->
    <div class="chart-row">
        <div class="chart-box">
            <h4>GENDER DISTRIBUTION</h4>
            <canvas id="genderChart"></canvas>
            <div style="text-align: center; margin-top: 15px; font-size: 12px;">
                <span><span class="legend-color" style="background: #3498db;"></span> Male: <?= $maleCount ?></span>
                <span style="margin-left: 15px;"><span class="legend-color" style="background: #e91e63;"></span> Female: <?= $femaleCount ?></span>
            </div>
        </div>
        <div class="chart-box">
            <h4>TESTS OVER TIME (<?= ucfirst($period_type) ?>)</h4>
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- SUMMARY TABLE -->
    <!-- SUMMARY TABLE -->
<?php if ($totalTests > 0): ?>
<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
    <h4 style="margin-bottom: 15px; color: #333;">SUMMARY REPORT (<?= date('M d, Y', strtotime($start_date)) ?> - <?= date('M d, Y', strtotime($end_date)) ?>)</h4>
    <table style="width: 100%; border-collapse: collapse;">
        <tr style="background: #f5f5f5;">
            <th style="padding: 10px; text-align: left;">Metric</th>
            <th style="padding: 10px; text-align: center;">Count</th>
            <th style="padding: 10px; text-align: center;">Percentage</th>
         </tr>
         <tr>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">METH Positive</td>
            <td style="padding: 8px; text-align: center; border-bottom: 1px solid #eee;"><?= $methPositive ?></td>
            <td style="padding: 8px; text-align: center; border-bottom: 1px solid #eee;"><?= $totalTests > 0 ? round(($methPositive / $totalTests) * 100, 1) : 0 ?>%</td>
         </tr>
         <tr>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">METH Negative</td>
            <td style="padding: 8px; text-align: center; border-bottom: 1px solid #eee;"><?= $methNegative ?></td>
            <td style="padding: 8px; text-align: center; border-bottom: 1px solid #eee;"><?= $totalTests > 0 ? round(($methNegative / $totalTests) * 100, 1) : 0 ?>%</td>
         </tr>
         <tr>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">THC Positive</td>
            <td style="padding: 8px; text-align: center; border-bottom: 1px solid #eee;"><?= $thcPositive ?></td>
            <td style="padding: 8px; text-align: center; border-bottom: 1px solid #eee;"><?= $totalTests > 0 ? round(($thcPositive / $totalTests) * 100, 1) : 0 ?>%</td>
         </tr>
         <tr>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">THC Negative</td>
            <td style="padding: 8px; text-align: center; border-bottom: 1px solid #eee;"><?= $thcNegative ?></td>
            <td style="padding: 8px; text-align: center; border-bottom: 1px solid #eee;"><?= $totalTests > 0 ? round(($thcNegative / $totalTests) * 100, 1) : 0 ?>%</td>
         </tr>
         <tr>
            <td style="padding: 8px; border-bottom: 1px solid #eee;">Male Tested</td>
            <td style="padding: 8px; text-align: center; border-bottom: 1px solid #eee;"><?= $maleCount ?></td>
            <td style="padding: 8px; text-align: center; border-bottom: 1px solid #eee;"><?= $totalTests > 0 ? round(($maleCount / $totalTests) * 100, 1) : 0 ?>%</td>
         </tr>
         <tr>
            <td style="padding: 8px;">Female Tested</td>
            <td style="padding: 8px; text-align: center;"><?= $femaleCount ?></td>
            <td style="padding: 8px; text-align: center;"><?= $totalTests > 0 ? round(($femaleCount / $totalTests) * 100, 1) : 0 ?>%</td>
         </tr>
    </table>
</div>
<?php endif; ?>

        <?php if ($totalTests == 0): ?>
            <div class="empty-state-container" style="text-align: center; padding: 100px 0;">
                <span style="font-size: 50px;">📊</span>
                <h3 style="color: #888; margin-top: 20px;">NO DATA AVAILABLE FOR THIS RANGE</h3>
                <p style="color: #bbb;">Try adjusting your date filters or adding new records.</p>
                <?php if (isAdmin()): ?>
                    <p style="margin-top: 20px;">
                        <a href="index.php?action=records" class="btn-blue-pill" style="text-decoration: none;">ADD RECORDS NOW</a>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Store chart instances globally for updating
    let methChart, thcChart, genderChart, trendChart;
    
    // Function to update charts with animation when filters change
    function updateCharts() {
        const methPositive = <?= $methPositive ?>;
        const methNegative = <?= $methNegative ?>;
        const thcPositive = <?= $thcPositive ?>;
        const thcNegative = <?= $thcNegative ?>;
        const maleCount = <?= $maleCount ?>;
        const femaleCount = <?= $femaleCount ?>;
        const periodLabels = <?= $periodLabelsJson ?>;
        const periodCounts = <?= $periodCountsJson ?>;
        
        // Update METH Chart
        if (methChart) {
            methChart.data.datasets[0].data = [methPositive];
            methChart.data.datasets[1].data = [methNegative];
            methChart.update({ duration: 800, easing: 'easeOutQuart' });
        }
        
        // Update THC Chart
        if (thcChart) {
            thcChart.data.datasets[0].data = [thcPositive];
            thcChart.data.datasets[1].data = [thcNegative];
            thcChart.update({ duration: 800, easing: 'easeOutQuart' });
        }
        
        // Update Gender Chart
        if (genderChart) {
            genderChart.data.datasets[0].data = [maleCount];
            genderChart.data.datasets[1].data = [femaleCount];
            genderChart.update({ duration: 800, easing: 'easeOutQuart' });
        }
        
        // Update Trend Chart
        if (trendChart) {
            trendChart.data.labels = periodLabels;
            trendChart.data.datasets[0].data = periodCounts;
            trendChart.update({ duration: 1000, easing: 'easeOutQuart' });
        }
    }
    
    // Print function
    function printAnalyticsReport() {
    const totalTests = <?= json_encode(number_format($totalTests)) ?>;
    const anyPositive = <?= json_encode(number_format($anyPositive)) ?>;
    const positiveRate = <?= json_encode($totalTests > 0 ? round(($anyPositive / $totalTests) * 100, 1) : 0) ?>;
    const negativeTests = <?= json_encode(number_format($negativeTests)) ?>;
    const methPositive = <?= json_encode($methPositive) ?>;
    const methNegative = <?= json_encode($methNegative) ?>;
    const thcPositive = <?= json_encode($thcPositive) ?>;
    const thcNegative = <?= json_encode($thcNegative) ?>;
    const maleCount = <?= json_encode($maleCount) ?>;
    const femaleCount = <?= json_encode($femaleCount) ?>;
    const periodLabels = <?= $periodLabelsJson ?>;
    const periodCounts = <?= $periodCountsJson ?>;
    const periodType = "<?= $periodTypeFormatted ?>";
    const now = new Date();
    
    const printWindow = window.open('', '_blank', 'width=900,height=700');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Analytics Report - Macro Access</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                    background: white;
                }
                .print-container {
                    max-width: 100%;
                    margin: 0 auto;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 15px;
                    border-bottom: 2px solid #1e3a8a;
                }
                .logo {
                    font-size: 40px;
                }
                .header h1 {
                    color: #1e3a8a;
                    margin: 5px 0;
                    font-size: 18px;
                }
                .header p {
                    color: #666;
                    font-size: 10px;
                }
                .date {
                    text-align: right;
                    font-size: 9px;
                    color: #666;
                    margin-bottom: 15px;
                    padding-bottom: 8px;
                    border-bottom: 1px solid #ddd;
                }
                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: 10px;
                    margin-bottom: 20px;
                }
                .stat-card {
                    background: #f8f9fa;
                    border-radius: 8px;
                    padding: 10px;
                    text-align: center;
                    border: 1px solid #eee;
                }
                .stat-card h3 {
                    font-size: 18px;
                    margin: 5px 0;
                    color: #1e3a8a;
                }
                .stat-card p {
                    color: #666;
                    font-size: 9px;
                }
                .chart-row {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 15px;
                    margin-bottom: 20px;
                }
                .chart-box {
                    flex: 1;
                    min-width: 250px;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 10px;
                    background: white;
                }
                .chart-box h4 {
                    text-align: center;
                    margin-bottom: 10px;
                    color: #333;
                    font-size: 12px;
                }
                canvas {
                    max-height: 180px !important;
                    width: 100% !important;
                }
                .chart-labels {
                    text-align: center;
                    margin-top: 8px;
                    font-size: 9px;
                }
                .summary-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                    font-size: 9px;
                }
                .summary-table th {
                    background: #2c3e50;
                    color: white;
                    padding: 6px;
                    border: 1px solid #000;
                    text-align: left;
                }
                .summary-table td {
                    padding: 5px;
                    border: 1px solid #ddd;
                }
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    padding-top: 10px;
                    border-top: 1px solid #ddd;
                    font-size: 8px;
                    color: #999;
                }
                @media print {
                    body {
                        padding: 0;
                    }
                    .chart-row {
                        page-break-inside: avoid;
                    }
                }
            </style>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"><\/script>
        </head>
        <body>
            <div class="print-container">
                <div class="header">
                    <div class="logo">🔬</div>
                    <h1>MACRO ACCESS DRUG TESTING CENTER</h1>
                    <p>Analytics Summary Report</p>
                </div>
                
                <div class="date">
                    Report Generated: ${now.toLocaleDateString()} ${now.toLocaleTimeString()}
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card"><p>Total Tests</p><h3>${totalTests}</h3></div>
                    <div class="stat-card"><p>Positive Results</p><h3>${anyPositive}</h3></div>
                    <div class="stat-card"><p>Negative Results</p><h3>${negativeTests}</h3></div>
                    <div class="stat-card"><p>Positive Rate</p><h3>${positiveRate}%</h3></div>
                </div>
                
                <div class="chart-row">
                    <div class="chart-box">
                        <h4>METH RESULT</h4>
                        <canvas id="methChart"></canvas>
                        <div class="chart-labels">Positive: ${methPositive} | Negative: ${methNegative}</div>
                    </div>
                    <div class="chart-box">
                        <h4>THC RESULT</h4>
                        <canvas id="thcChart"></canvas>
                        <div class="chart-labels">Positive: ${thcPositive} | Negative: ${thcNegative}</div>
                    </div>
                </div>
                
                <div class="chart-row">
                    <div class="chart-box">
                        <h4>GENDER DISTRIBUTION</h4>
                        <canvas id="genderChart"></canvas>
                        <div class="chart-labels">Male: ${maleCount} | Female: ${femaleCount}</div>
                    </div>
                    <div class="chart-box">
                        <h4>TESTS OVER TIME (${periodType})</h4>
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
                
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>METH Positive</td>
                            <td style="text-align:center">${methPositive}</td>
                            <td style="text-align:center">${totalTests > 0 ? Math.round(methPositive / totalTests * 100) : 0}%</td>
                        </tr>
                        <tr>
                            <td>METH Negative</td>
                            <td style="text-align:center">${methNegative}</td>
                            <td style="text-align:center">${totalTests > 0 ? Math.round(methNegative / totalTests * 100) : 0}%</td>
                        </tr>
                        <tr>
                            <td>THC Positive</td>
                            <td style="text-align:center">${thcPositive}</td>
                            <td style="text-align:center">${totalTests > 0 ? Math.round(thcPositive / totalTests * 100) : 0}%</td>
                        </tr>
                        <tr>
                            <td>THC Negative</td>
                            <td style="text-align:center">${thcNegative}</td>
                            <td style="text-align:center">${totalTests > 0 ? Math.round(thcNegative / totalTests * 100) : 0}%</td>
                        </tr>
                        <tr>
                            <td>Male Tested</td>
                            <td style="text-align:center">${maleCount}</td>
                            <td style="text-align:center">${totalTests > 0 ? Math.round(maleCount / totalTests * 100) : 0}%</td>
                        </tr>
                        <tr>
                            <td>Female Tested</td>
                            <td style="text-align:center">${femaleCount}</td>
                            <td style="text-align:center">${totalTests > 0 ? Math.round(femaleCount / totalTests * 100) : 0}%</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="footer">
                    This is a computer-generated report. No signature is required.<br>
                    Macro Access Drug Testing Center | Since 2004
                </div>
            </div>
            
            <script>
                new Chart(document.getElementById('methChart'), {
                    type: 'bar',
                    data: { labels: ['METH'], datasets: [
                        { label: 'Positive', data: [${methPositive}], backgroundColor: '#e74c3c' },
                        { label: 'Negative', data: [${methNegative}], backgroundColor: '#27ae60' }
                    ]},
                    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: { size: 9 } } } } }
                });
                
                new Chart(document.getElementById('thcChart'), {
                    type: 'bar',
                    data: { labels: ['THC'], datasets: [
                        { label: 'Positive', data: [${thcPositive}], backgroundColor: '#e74c3c' },
                        { label: 'Negative', data: [${thcNegative}], backgroundColor: '#27ae60' }
                    ]},
                    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: { size: 9 } } } } }
                });
                
                new Chart(document.getElementById('genderChart'), {
                    type: 'bar',
                    data: { labels: ['Gender'], datasets: [
                        { label: 'Male', data: [${maleCount}], backgroundColor: '#3498db' },
                        { label: 'Female', data: [${femaleCount}], backgroundColor: '#e91e63' }
                    ]},
                    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: { size: 9 } } } } }
                });
                
                new Chart(document.getElementById('trendChart'), {
                    type: 'line',
                    data: { labels: ${JSON.stringify(periodLabels)}, datasets: [{
                        label: 'Tests', data: ${JSON.stringify(periodCounts)},
                        borderColor: '#1e3a8a', backgroundColor: 'rgba(30,58,138,0.1)', fill: true
                    }]},
                    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'top', labels: { font: { size: 9 } } } } }
                });
                
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                        window.close();
                    }, 500);
                };
            <\/script>
        </body>
        </html>
    `);
    
    printWindow.document.close();
}

    <?php if ($totalTests > 0): ?>
    // Initialize METH Chart with animation
    methChart = new Chart(document.getElementById('methChart'), {
        type: 'bar',
        data: {
            labels: ['METH Results'],
            datasets: [
                { label: 'Positive', data: [0], backgroundColor: '#e74c3c', borderRadius: 8 },
                { label: 'Negative', data: [0], backgroundColor: '#27ae60', borderRadius: 8 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: { y: { beginAtZero: true, title: { display: true, text: 'Number of Tests' }, grid: { color: '#e0e0e0' } } },
            animation: { duration: 800, easing: 'easeOutQuart' }
        }
    });

    // Initialize THC Chart with animation
    thcChart = new Chart(document.getElementById('thcChart'), {
        type: 'bar',
        data: {
            labels: ['THC Results'],
            datasets: [
                { label: 'Positive', data: [0], backgroundColor: '#e74c3c', borderRadius: 8 },
                { label: 'Negative', data: [0], backgroundColor: '#27ae60', borderRadius: 8 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: { y: { beginAtZero: true, title: { display: true, text: 'Number of Tests' }, grid: { color: '#e0e0e0' } } },
            animation: { duration: 800, easing: 'easeOutQuart' }
        }
    });

    // Initialize Gender Chart with animation
    genderChart = new Chart(document.getElementById('genderChart'), {
        type: 'bar',
        data: {
            labels: ['Gender Distribution'],
            datasets: [
                { label: 'Male', data: [0], backgroundColor: '#3498db', borderRadius: 8 },
                { label: 'Female', data: [0], backgroundColor: '#e91e63', borderRadius: 8 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: { y: { beginAtZero: true, title: { display: true, text: 'Number of People' }, grid: { color: '#e0e0e0' } } },
            animation: { duration: 800, easing: 'easeOutQuart' }
        }
    });

    // Initialize Trend Chart with animation
    trendChart = new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($periodLabels) ?>,
            datasets: [{
                label: 'Number of Tests',
                data: new Array(<?= count($periodLabels) ?>).fill(0),
                borderColor: '#1e3a8a',
                backgroundColor: 'rgba(30, 58, 138, 0.1)',
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#1e3a8a',
                pointBorderColor: '#fff',
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Number of Tests' }, grid: { color: '#e0e0e0' } },
                x: { title: { display: true, text: '<?= ucfirst($period_type) ?>' }, ticks: { maxRotation: 45, minRotation: 45 } }
            },
            animation: { duration: 1000, easing: 'easeOutQuart' }
        }
    });
    
    // Animate to actual values after initialization
    setTimeout(() => {
        updateCharts();
    }, 200);
    <?php endif; ?>
    </script>

    <?php include 'views/footer.php'; ?>
    </body>
    </html>