<?php
/**
 * API endpoint для экспорта данных в PDF и Word форматы
 */

require_once 'db.php';
require_once 'includes/ExportHelper.php';

$action = $_GET['action'] ?? '';
$type = $_GET['type'] ?? '';

// Получаем те же фильтры, что и на странице
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');
$inst_filter = filter_input(INPUT_GET, 'institute_id', FILTER_VALIDATE_INT) ?: '';
$fac_filter = filter_input(INPUT_GET, 'faculty_id', FILTER_VALIDATE_INT) ?: '';

if (!empty($date_from) && !validateDate($date_from)) $date_from = '';
if (!empty($date_to) && !validateDate($date_to)) $date_to = '';

switch ($action) {
    case 'registry':
        $events = $eventModel->getAllWithFilters($date_from, $date_to, $inst_filter, $fac_filter);
        
        if ($type === 'pdf') {
            ExportHelper::exportRegistryPDF($events);
        } elseif ($type === 'word') {
            ExportHelper::exportRegistryWord($events);
        }
        break;
        
    case 'report':
        $db_date_from = date('Y-m-d', strtotime($date_from));
        $db_date_to = date('Y-m-d', strtotime($date_to));
        
        $instituteId = $inst_filter ?: null;
        $facultyId = $fac_filter ?: null;
        
        $metrics = $eventModel->getMetrics($db_date_from, $db_date_to, $instituteId, $facultyId);
        $records = $eventModel->getReportData($db_date_from, $db_date_to, $instituteId, $facultyId);
        
        if ($type === 'pdf') {
            ExportHelper::exportReportPDF($records, $date_from, $date_to, $metrics);
        } elseif ($type === 'word') {
            ExportHelper::exportReportWord($records, $date_from, $date_to, $metrics);
        }
        break;
        
    default:
        echo "Неверный запрос";
        exit;
}
