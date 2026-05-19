<?php include 'views/header.php'; ?>

<?php

// ============================================
// 1. GET WEEKLY DATA (Last 7 days)
// ============================================
$weeklyQuery = "SELECT 
                    DATE(date_tested) as test_date,
                    DAYNAME(date_tested) as day_name,
                    COUNT(*) as total_tests
                FROM test_records 
                WHERE DATE(date_tested) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(date_tested)
                ORDER BY test_date ASC";

$weeklyStmt = $db->prepare($weeklyQuery);
$weeklyStmt->execute();
$weeklyData = $weeklyStmt->fetchAll(PDO::FETCH_ASSOC);

// Create arrays for the last 7 days (fill missing days with 0)
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$weeklyCounts = array_fill(0, 7, 0);

foreach ($weeklyData as $row) {
    $dayIndex = array_search($row['day_name'], $daysOfWeek);
    if ($dayIndex !== false) {
        $weeklyCounts[$dayIndex] = $row['total_tests'];
    }
}

// ============================================
// 2. GET MONTHLY DATA (Last 12 months)
// ============================================
$monthlyQuery = "SELECT 
                    DATE_FORMAT(date_tested, '%Y-%m') as month,
                    DATE_FORMAT(date_tested, '%b') as month_name,
                    COUNT(*) as total_tests
                FROM test_records 
                WHERE DATE(date_tested) >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                GROUP BY DATE_FORMAT(date_tested, '%Y-%m')
                ORDER BY month ASC";

$monthlyStmt = $db->prepare($monthlyQuery);
$monthlyStmt->execute();
$monthlyData = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// 3. GET TOTAL TESTS (just to check if data exists)
// ============================================
$totalQuery = "SELECT COUNT(*) as total FROM test_records";
$totalStmt = $db->prepare($totalQuery);
$totalStmt->execute();
$totalTests = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get month names for display
$monthNames = [];
$monthCounts = [];
foreach ($monthlyData as $row) {
    $monthNames[] = $row['month_name'];
    $monthCounts[] = $row['total_tests'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Macro Access - Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .chart-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            background: #e0e0e0;
            color: #333;
            transition: all 0.3s;
            font-size: 14px;
        }
        .chart-btn.active {
            background: #1e3a8a;
            color: white;
        }
        .chart-btn:hover {
            background: #3498db;
            color: white;
        }
        
        /* Fix chart container overflow */
        .hero-chart-box {
            overflow: hidden;
            position: relative;
            min-height: 350px;
            width: 100%;
        }
        
        .chart-wrapper {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        #landingChart {
            width: 100% !important;
            height: auto !important;
            max-height: 280px;
        }
        
        /* Slideshow Styles */
        .slideshow-container {
            position: relative;
            max-width: 100%;
            margin: 40px auto 20px;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .slideshow-wrapper {
            position: relative;
            width: 100%;
            background: white;
            border-radius: 12px;
        }
        
        .slide {
            display: none;
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
        }
        
        .slide.active {
            display: block;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0.4; }
            to { opacity: 1; }
        }
        
        .slideshow-caption {
            text-align: center;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 10px;
            position: absolute;
            bottom: 0;
            width: 100%;
            border-radius: 0 0 12px 12px;
            font-size: 14px;
        }
        
        .prev, .next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            padding: 16px;
            color: white;
            font-weight: bold;
            font-size: 18px;
            transition: 0.3s;
            border-radius: 0 3px 3px 0;
            user-select: none;
            background: rgba(0,0,0,0.5);
            z-index: 10;
            text-decoration: none;
        }
        
        .next {
            right: 0;
            border-radius: 3px 0 0 3px;
        }
        
        .prev:hover, .next:hover {
            background: rgba(0,0,0,0.8);
        }
        
        .dots-container {
            text-align: center;
            padding: 15px 0;
        }
        
        .dot {
            cursor: pointer;
            height: 12px;
            width: 12px;
            margin: 0 5px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        
        .dot.active, .dot:hover {
            background-color: #1e3a8a;
        }
        
        /* Animation for fade in */
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
        
        .hero-content {
            animation: fadeInUp 0.5s ease-out forwards;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .hero-chart-box {
            animation: fadeInUp 0.5s ease-out forwards;
            margin-bottom: 40px;
        }
        
        .slideshow-title {
            text-align: center;
            color: white;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        @media (max-width: 768px) {
            .landing-hero {
                flex-direction: column;
                padding: 20px 15px;
            }
            .hero-content, .hero-chart-box {
                max-width: 100%;
            }
            #landingChart {
                max-height: 220px;
            }
            .slide {
                height: 250px;
            }
            .hero-content h1 {
                font-size: 1.8rem;
            }
            .slideshow-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body class="dashboard-body">

    <nav class="navbar">
        <div class="nav-logo">
            <a href="index.php?action=dashboard" style="text-decoration: none; color: inherit;">🔬 MACRO ACCESS</a>
        </div>
        <ul class="nav-links">
            <li><a href="index.php?action=account">ACCOUNT</a></li>
            <li><a href="index.php?action=reports">REPORTS</a></li>
            <li><a href="index.php?action=analytics">ANALYTICS</a></li>
            <?php if (isAdmin()): ?>
                <li><a href="index.php?action=records">RECORDS</a></li>
            <?php endif; ?>
            <li><a href="index.php?action=logout">LOG OUT</a></li>
        </ul>
    </nav>

    <main class="landing-hero">
        <div class="hero-content">
            <h1>Accurate Results,<br>Trusted Service.</h1>
            <p>Simple, professional, and reassuring. Emphasizing accuracy which is the most important thing in drug testing.</p>
        </div>

        <div class="hero-chart-box">
            <?php if ($totalTests > 0): ?>
                <div class="chart-wrapper">
                    <div class="chart-controls">
                        <button class="chart-btn active" onclick="switchChart('weekly')">Last 7 Days</button>
                        <button class="chart-btn" onclick="switchChart('monthly')">Last 12 Months</button>
                    </div>
                    <canvas id="landingChart" style="max-height: 280px; width: 100%;"></canvas>
                </div>
            <?php else: ?>
                <div class="no-data-placeholder">
                    <p>No analytics data available yet.</p>
                    <?php if (isAdmin()): ?>
                        <p style="margin-top: 10px;">
                            <a href="index.php?action=records" class="btn-blue-pill" style="text-decoration: none; padding: 10px 20px;">ADD TEST RECORDS</a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Photo Slideshow Section -->
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px 40px;">
        <h2 class="slideshow-title">📸 Our Drug Testing Center</h2>
        
        <div class="slideshow-wrapper">
            <div class="slideshow-container">
                <a class="prev" onclick="changeSlide(-1)">❮</a>
                <a class="next" onclick="changeSlide(1)">❯</a>
                
                <!-- Slide 1 -->
                <div class="slide active">
                    <img src="dbphoto/dilaw.jpg" alt="Our Laboratory">
                    <div class="slideshow-caption">State-of-the-art Laboratory Facilities</div>
                </div>
                
                <!-- Slide 2 -->
                <div class="slide">
                    <img src="dbphoto/urine.jpg" alt="Our Laboratory">
                    <div class="slideshow-caption">Advanced Drug Testing Equipment</div>
                </div>
                
                <!-- Slide 3 -->
                <div class="slide">
                    <img src="dbphoto/tube.webp" alt="Our Laboratory">
                    <div class="slideshow-caption">Professional Medical Team</div>
                </div>
                
                <!-- Slide 4 -->
                <div class="slide">
                    <img src="dbphoto/haha.webp" alt="Our Laboratory">
                    <div class="slideshow-caption">Comfortable Waiting Area</div>
                </div>
                
                <!-- Slide 5 -->
                <div class="slide">
                    <img src="dbphoto/bote.webp" alt="Our Laboratory">
                    <div class="slideshow-caption">Friendly Reception Area</div>
                </div>
            </div>
            
            <div class="dots-container" id="dotsContainer"></div>
        </div>
    </div>

    <?php if ($totalTests > 0): ?>
    <script>
        let currentChart = null;
        
        const weeklyLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        const weeklyData = <?= json_encode($weeklyCounts) ?>;
        const monthlyLabels = <?= json_encode($monthNames) ?>;
        const monthlyData = <?= json_encode($monthCounts) ?>;
        
        function initWeeklyChart() {
            if (currentChart) {
                currentChart.destroy();
            }
            
            const ctx = document.getElementById('landingChart').getContext('2d');
            currentChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: weeklyLabels,
                    datasets: [{
                        label: 'Tests',
                        data: new Array(weeklyLabels.length).fill(0),
                        backgroundColor: 'rgba(30, 58, 138, 0.8)',
                        borderColor: '#1e3a8a',
                        borderWidth: 1,
                        borderRadius: 8
                    }]
                },
                options: { 
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { 
                        legend: { display: false },
                        tooltip: { 
                            callbacks: { 
                                label: function(context) { 
                                    return context.raw + ' tests'; 
                                } 
                            } 
                        }
                    },
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            title: { display: true, text: 'Tests' },
                            grid: { color: '#e0e0e0' }
                        }
                    },
                    animation: {
                        duration: 800,
                        easing: 'easeOutQuart'
                    }
                }
            });
            
            setTimeout(() => {
                currentChart.data.datasets[0].data = weeklyData;
                currentChart.update({ duration: 800, easing: 'easeOutQuart' });
            }, 100);
        }   
        
        function initMonthlyChart() {
            if (currentChart) {
                currentChart.destroy();
            }
            
            const ctx = document.getElementById('landingChart').getContext('2d');
            currentChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Tests',
                        data: new Array(monthlyLabels.length).fill(0),
                        backgroundColor: 'rgba(74, 222, 128, 0.8)',
                        borderColor: '#27ae60',
                        borderWidth: 1,
                        borderRadius: 8
                    }]
                },
                options: { 
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { 
                        legend: { display: false },
                        tooltip: { 
                            callbacks: { 
                                label: function(context) { 
                                    return context.raw + ' tests'; 
                                } 
                            } 
                        }
                    },
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            title: { display: true, text: 'Tests' },
                            grid: { color: '#e0e0e0' }
                        }
                    },
                    animation: {
                        duration: 800,
                        easing: 'easeOutQuart'
                    }
                }
            });
            
            setTimeout(() => {
                currentChart.data.datasets[0].data = monthlyData;
                currentChart.update({ duration: 800, easing: 'easeOutQuart' });
            }, 100);
        }
        
        function switchChart(type) {
            const buttons = document.querySelectorAll('.chart-btn');
            buttons.forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            if (type === 'weekly') {
                initWeeklyChart();
            } else if (type === 'monthly') {
                initMonthlyChart();
            }
        }
        
        <?php if (!empty($weeklyData) || array_sum($weeklyCounts) > 0): ?>
            initWeeklyChart();
        <?php else: ?>
            initMonthlyChart();
        <?php endif; ?>
        
        // ============================================
        // SLIDESHOW FUNCTIONALITY
        // ============================================
        let slideIndex = 1;
        let slideInterval;
        
        function showSlides(n) {
            let slides = document.getElementsByClassName("slide");
            let dots = document.getElementsByClassName("dot");
            
            if (n > slides.length) slideIndex = 1;
            if (n < 1) slideIndex = slides.length;
            
            for (let i = 0; i < slides.length; i++) {
                slides[i].classList.remove("active");
            }
            
            for (let i = 0; i < dots.length; i++) {
                dots[i].classList.remove("active");
            }
            
            slides[slideIndex - 1].classList.add("active");
            if (dots[slideIndex - 1]) dots[slideIndex - 1].classList.add("active");
        }
        
        function changeSlide(n) {
            clearInterval(slideInterval);
            slideIndex += n;
            showSlides(slideIndex);
            startAutoSlide();
        }
        
        function currentSlide(n) {
            clearInterval(slideInterval);
            slideIndex = n;
            showSlides(slideIndex);
            startAutoSlide();
        }
        
        function startAutoSlide() {
            slideInterval = setInterval(() => {
                slideIndex++;
                showSlides(slideIndex);
                if (slideIndex > document.getElementsByClassName("slide").length) {
                    slideIndex = 1;
                    showSlides(slideIndex);
                }
            }, 5000);
        }
        
        // Create dots
        function createDots() {
            let slides = document.getElementsByClassName("slide");
            let dotsContainer = document.getElementById("dotsContainer");
            dotsContainer.innerHTML = "";
            
            for (let i = 0; i < slides.length; i++) {
                let dot = document.createElement("span");
                dot.className = "dot";
                dot.onclick = (function(index) {
                    return function() { currentSlide(index + 1); };
                })(i);
                dotsContainer.appendChild(dot);
            }
            
            showSlides(slideIndex);
            startAutoSlide();
        }
        
        // Initialize slideshow when page loads
        document.addEventListener('DOMContentLoaded', function() {
            createDots();
        });
    </script>
    <?php endif; ?>
    
    <?php include 'views/footer.php'; ?>
</body>
</html>