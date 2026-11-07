<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event_id'])) {
    $id = filter_input(INPUT_POST, 'delete_event_id', FILTER_VALIDATE_INT);
    if ($id) {
        try {
            $eventModel->delete($id);
            $_SESSION['success'] = "Мероприятие успешно удалено.";
        } catch(PDOException $e) {
            $_SESSION['error'] = "Ошибка удаления: системный сбой.";
        }
    }
    header("Location: index.php");
    exit();
}

$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');
$inst_filter = filter_input(INPUT_GET, 'institute_id', FILTER_VALIDATE_INT) ?: '';
$fac_filter = filter_input(INPUT_GET, 'faculty_id', FILTER_VALIDATE_INT) ?: '';

if (!empty($date_from) && !validateDate($date_from)) $date_from = '';
if (!empty($date_to) && !validateDate($date_to)) $date_to = '';

$events = $eventModel->getAllWithFilters($date_from, $date_to, $inst_filter, $fac_filter);
$institutes = $instituteModel->getAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Учет мероприятий - СГУВТ</title>
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
            <h2 class="page-title">Реестр мероприятий</h2>
            <div class="action-buttons-row">
                <button type="button" onclick="window.print()" class="btn btn-gray">🖨 Печать</button>
                <button type="button" onclick="generatePDF('Reestr_meropriyatiy.pdf')" class="btn btn-red">СОХРАНИТЬ В PDF</button>
                <button type="button" onclick="generateWord('Reestr_meropriyatiy.docx')" class="btn btn-blue">СОХРАНИТЬ В WORD</button>
            </div>
        </div>
        
        <?php displayAlerts(); ?>

        <form method="GET" id="filter_form">
            <div class="filter-panel">
                <div class="filter-row">
                    <div class="form-group"><label>С даты:</label><input type="text" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" class="form-control datepicker" placeholder="ДД.ММ.ГГГГ"></div>
                    <div class="form-group"><label>По дату:</label><input type="text" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" class="form-control datepicker" placeholder="ДД.ММ.ГГГГ"></div>
                    <div class="form-group">
                        <label>Институт:</label>
                        <select name="institute_id" id="filter_institute" class="form-control" onchange="loadFaculties(this.value, 'filter_faculty', <?php echo $fac_filter ?: 'null'; ?>)">
                            <option value="">-- Все --</option>
                            <?php foreach($institutes as $inst): ?>
                                <option value="<?php echo $inst['id']; ?>" <?php if($inst_filter == $inst['id']) echo 'selected'; ?>><?php echo htmlspecialchars($inst['short_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Факультет:</label>
                        <select name="faculty_id" id="filter_faculty" class="form-control">
                            <option value="">-- Все --</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Отфильтровать</button>
                    <a href="index.php" class="btn btn-gray btn-link">Сбросить</a>
                </div>
            </div>
        </form>
        
        <div id="pdf_content">
            <h2 id="pdf_title" class="pdf-title">Реестр мероприятий СГУВТ</h2>
            <table class="main-table">
                <thead>
                    <tr>
                        <th class="col-date">Дата</th>
                        <th class="col-institute">Институт</th>
                        <th class="col-faculty">Факультет</th>
                        <th class="col-title">Тип и Название</th>
                        <th class="col-organizer">Организатор (Институт / ФИО)</th>
                        <th class="col-description">Описание мероприятия</th>
                        <th>Записи (Студенты/Группы)</th>
                        <th class="col-actions action-col">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($events)): ?>
                        <tr><td colspan="8" class="text-center">Нет данных за этот период.</td></tr>
                    <?php else: ?>
                        <?php foreach($events as $e): ?>
                            <tr>
                                <td><?php echo date('d.m.Y', strtotime($e['date_start'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($e['short_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($e['faculty_name'] ?? '—'); ?></td>
                                <td>
                                    <span class="category-label"><?php echo htmlspecialchars($e['category_name']); ?></span><br>
                                    <?php echo htmlspecialchars($e['title']); ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($e['organizer']); ?></strong><br>
                                    <em class="location-text"><?php echo htmlspecialchars($e['location']); ?></em>
                                </td>
                                <td class="description-cell">
                                    <?php echo nl2br(htmlspecialchars($e['description'] ?: 'ПУСТО')); ?>
                                </td>
                                <td>
                                    <strong>Всего записей: <?php echo $e['parts_count']; ?></strong><br>
                                    <span class="participants-text"><?php echo htmlspecialchars($e['parts_list'] ?: 'Нет данных'); ?></span>
                                </td>
                                <td class="action-col">
                                    <div class="action-buttons">
                                        <a href="edit.php?id=<?php echo $e['id']; ?>" class="btn btn-small btn-center">Редактировать</a>
                                        <form method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить мероприятие?');" class="form-inline">
                                            <input type="hidden" name="delete_event_id" value="<?php echo $e['id']; ?>">
                                            <button type="submit" class="btn btn-red btn-small btn-full">Удалить</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        flatpickr(".datepicker", { locale: "ru", dateFormat: "d.m.Y", allowInput: true });
        
        // Загрузить факультеты при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            const instSelect = document.getElementById('filter_institute');
            loadFaculties(instSelect ? instSelect.value : '', 'filter_faculty', <?php echo $fac_filter ?: 'null'; ?>);
        });
    </script>
</body>
</html>
