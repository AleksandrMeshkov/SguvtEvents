<?php
require 'db.php';

if (isset($_GET['reset'])) {
    unset($_SESSION['report_date_from'], $_SESSION['report_date_to'], $_SESSION['report_institute_id'], $_SESSION['report_faculty_id']);
    header("Location: report.php");
    exit();
}

if (isset($_GET['date_from']) || isset($_GET['date_to'])) {
    $raw_from = trim($_GET['date_from'] ?? '');
    $raw_to = trim($_GET['date_to'] ?? '');
    
    $_SESSION['report_date_from'] = (!empty($raw_from) && validateDate($raw_from)) ? $raw_from : '';
    $_SESSION['report_date_to'] = (!empty($raw_to) && validateDate($raw_to)) ? $raw_to : '';
}

if (array_key_exists('institute_id', $_GET)) {
    $_SESSION['report_institute_id'] = filter_input(INPUT_GET, 'institute_id', FILTER_VALIDATE_INT) ?: '';
}

if (array_key_exists('faculty_id', $_GET)) {
    $_SESSION['report_faculty_id'] = filter_input(INPUT_GET, 'faculty_id', FILTER_VALIDATE_INT) ?: '';
}

$date_from = !empty($_SESSION['report_date_from']) ? $_SESSION['report_date_from'] : date('d.m.Y', strtotime(date('Y-m-01')));
$date_to = !empty($_SESSION['report_date_to']) ? $_SESSION['report_date_to'] : date('d.m.Y', strtotime(date('Y-m-t')));
$inst_filter = $_SESSION['report_institute_id'] ?? '';
$fac_filter = $_SESSION['report_faculty_id'] ?? '';

$db_date_from = date('Y-m-d', strtotime($date_from));
$db_date_to = date('Y-m-d', strtotime($date_to));

$instituteId = $inst_filter ?: null;
$facultyId = $fac_filter ?: null;

$metrics = $eventModel->getMetrics($db_date_from, $db_date_to, $instituteId, $facultyId);
$records = $eventModel->getReportData($db_date_from, $db_date_to, $instituteId, $facultyId);
$institutes = $instituteModel->getAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отчеты - СГУВТ</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="lib/flatpickr/flatpickr.min.css">
    <script src="lib/flatpickr/flatpickr.min.js"></script>
    <script src="lib/flatpickr/l10n/ru.js"></script>
    <script src="assets/js/common.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="page-header-row">
            <h2 class="page-title">Отчетность по мероприятиям СГУВТ</h2>
            <div class="action-buttons-row">
                <button type="button" onclick="window.print()" class="btn btn-gray">🖨 Печать</button>
                <button type="button" onclick="generatePDF('Otchet_SGUVT.pdf')" class="btn btn-red">СОХРАНИТЬ В PDF</button>
                <button type="button" onclick="generateWord('Otchet_SGUVT.docx')" class="btn btn-blue">СОХРАНИТЬ В WORD</button>
            </div>
        </div>
        <form method="GET" id="report_filter_form">
            <div class="filter-panel">
                <div class="filter-row">
                    <div class="form-group"><label>Период С:</label><input type="text" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" class="form-control datepicker" placeholder="ДД.ММ.ГГГГ"></div>
                    <div class="form-group"><label>ПО:</label><input type="text" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" class="form-control datepicker" placeholder="ДД.ММ.ГГГГ"></div>
                    <div class="form-group">
                        <label>Институт:</label>
                        <select name="institute_id" id="report_institute" class="form-control" onchange="loadFaculties(this.value, 'report_faculty', <?php echo $fac_filter ?: 'null'; ?>)">
                            <option value="">-- Все --</option>
                            <?php foreach($institutes as $inst): ?>
                                <option value="<?php echo $inst['id']; ?>" <?php if($inst_filter == $inst['id']) echo 'selected'; ?>><?php echo htmlspecialchars($inst['short_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Факультет:</label>
                        <select name="faculty_id" id="report_faculty" class="form-control">
                            <option value="">-- Все --</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Показать</button>
                    <a href="report.php?reset=1" class="btn btn-gray btn-link">Сбросить</a>
                </div>
            </div>
        </form>
        
        <div class="metrics-grid">
            <div class="metric-card"><h2><?php echo $metrics['ev_count'] ?: 0; ?></h2><p>Мероприятий</p></div>
            <div class="metric-card"><h2><?php echo $metrics['winners_count'] ?: 0; ?></h2><p>Призовых мест</p></div>
            <div class="metric-card"><h2><?php echo $metrics['awards_count'] ?: 0; ?></h2><p>Выдано наград</p></div>
        </div>
        
        <div id="pdf_content" class="pdf-content">
            <h2 class="report-title">Отчетность по мероприятиям СГУВТ</h2>
            <p class="report-period">Период: с <?php echo htmlspecialchars($date_from); ?> по <?php echo htmlspecialchars($date_to); ?></p>
            <table class="main-table">
                <thead><tr><th>Дата</th><th>Институт</th><th>Факультет</th><th>Название</th><th>ФИО / Группа</th><th>Награды</th></tr></thead>
                <tbody>
                    <?php if(empty($records)): ?>
                        <tr><td colspan="6" class="text-center">Данных нет.</td></tr>
                    <?php else: foreach($records as $r): ?>
                        <tr>
                            <td><?php echo date('d.m.Y', strtotime($r['date_start'])); ?></td>
                            <td><b><?php echo htmlspecialchars($r['short_name']); ?></b></td>
                            <td><?php echo htmlspecialchars($r['faculty_name'] ?? '—'); ?></td>
                            <td>
                                <span class="category-label"><?php echo htmlspecialchars($r['cat_name']); ?></span><br>
                                <?php echo htmlspecialchars($r['title']); ?>
                            </td>
                            <td>
                                <b class="participant-name"><?php echo htmlspecialchars($r['participant_info'] ?: '—'); ?></b><br>
                                <span class="role-text"><?php echo htmlspecialchars($r['role_in_event']); ?></span>
                            </td>
                            <td>
                                <?php 
                                $award = trim($r['award_details'] ?? '');
                                if (!empty($award) && $award !== 'ПУСТО' && $award !== '—') {
                                    echo "<strong class='award-text'>" . htmlspecialchars($award) . "</strong>";
                                } else {
                                    echo "<span class='no-award'>Награды отсутствуют</span>";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        flatpickr(".datepicker", { locale: "ru", dateFormat: "d.m.Y", allowInput: true });
        
        // Загрузить факультеты при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            const instSelect = document.getElementById('report_institute');
            loadFaculties(instSelect ? instSelect.value : '', 'report_faculty', <?php echo $fac_filter ?: 'null'; ?>);
        });
    </script>
</body>
</html>
