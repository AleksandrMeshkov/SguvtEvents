<?php
require 'db.php';

$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$event_id) { 
    $_SESSION['error'] = "Некорректный ID мероприятия.";
    header("Location: index.php"); exit; 
}

$event = $eventModel->getById($event_id);
if (!$event) { 
    $_SESSION['error'] = "Мероприятие не найдено.";
    header("Location: index.php"); exit; 
}

$participants = $participantModel->getByEventId($event_id);
$categories = $categoryModel->getAll();
$institutes = $instituteModel->getForSelection();

$org_parts = explode(' / ', $event['organizer']);
$org_inst = $org_parts[0] ?? '';
$org_fio = $org_parts[1] ?? '';

$is_custom_loc = !in_array($event['location'], STD_LOCATIONS);

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $institute_id = isset($_POST['is_general']) ? 1 : filter_input(INPUT_POST, 'institute_id', FILTER_VALIDATE_INT);
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $faculty_id = filter_input(INPUT_POST, 'faculty_id', FILTER_VALIDATE_INT) ?: null;
        
        $title = trim($_POST['title'] ?? '');
        $date_start_raw = trim($_POST['date_start'] ?? '');
        $date_end_raw = trim($_POST['date_end'] ?? '');
        // Определяем location: если выбран "другой", берём из location_custom, иначе из location_std
        $location_std = trim($_POST['location_std'] ?? '');
        $location_custom = trim($_POST['location_custom'] ?? '');
        $location = ($location_std === 'other' && $location_custom !== '') ? $location_custom : $location_std;
        $post_org_inst = trim($_POST['org_inst'] ?? '');
        $post_org_fio = trim($_POST['org_fio'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || empty($date_start_raw) || empty($location) || empty($post_org_inst) || empty($post_org_fio) || empty($description) || !$institute_id || !$category_id) {
            throw new Exception("Не все обязательные поля заполнены.");
        }

        if (!validateDate($date_start_raw)) throw new Exception("Неверный формат даты начала (ДД.ММ.ГГГГ).");
        $date_start = date('Y-m-d', strtotime($date_start_raw));
        
        $date_end = $date_start;
        if (!empty($date_end_raw)) {
            if (!validateDate($date_end_raw)) throw new Exception("Неверный формат даты окончания.");
            $date_end = date('Y-m-d', strtotime($date_end_raw));
            if ($date_end < $date_start) throw new Exception("Дата окончания не может быть раньше даты начала.");
        }

        $organizer = $post_org_inst . ' / ' . $post_org_fio;

        $pdo->beginTransaction();

        $eventModel->update($event_id, [
            'title' => $title,
            'category_id' => $category_id,
            'institute_id' => $institute_id,
            'faculty_id' => $faculty_id,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'location' => $location,
            'organizer' => $organizer,
            'description' => $description
        ]);
        
        $participantsData = [];
        if (!empty($_POST['participant_info']) && is_array($_POST['participant_info'])) {
            for ($i = 0; $i < count($_POST['participant_info']); $i++) {
                $participantsData[] = [
                    'info' => $_POST['participant_info'][$i],
                    'role' => $_POST['role_in_event'][$i] ?? 'Участник',
                    'award' => $_POST['award_details'][$i] ?? '',
                    'reward' => $_POST['financial_reward'][$i] ?? '0'
                ];
            }
        }
        $participantModel->saveForEvent($event_id, $participantsData);
        
        $pdo->commit();
        $_SESSION['success'] = "Изменения успешно сохранены.";
        header("Location: index.php"); exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error_msg = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать запись - СГУВТ</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="lib/flatpickr/flatpickr.min.css">
    <script src="lib/flatpickr/flatpickr.min.js"></script>
    <script src="lib/flatpickr/l10n/ru.js"></script>
    <script src="assets/js/common.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2 class="page-title">Редактировать мероприятие</h2>
        
        <?php if($error_msg): ?>
            <div class="alert alert-error"><b>Ошибка:</b> <?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card">
            <div class="general-event-block">
                <label class="general-event-label">
                    <input type="checkbox" name="is_general" id="is_general" onchange="toggleGeneral()" <?php if($event['institute_id'] == 1) echo 'checked'; ?>>
                    Отметить как ОБЩЕУНИВЕРСИТЕТСКОЕ мероприятие
                </label>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Институт:</label>
                    <select name="institute_id" id="institute_id" class="form-control" <?php if($event['institute_id'] == 1) echo 'disabled style="background-color:#eee;"'; ?> required onchange="loadFacultiesForForm(this.value, 'faculty_id', <?php echo $event['faculty_id'] ?: 'null'; ?>)">
                        <option value="">-- Выберите --</option>
                        <?php foreach($institutes as $inst): ?>
                            <option value="<?php echo $inst['id']; ?>" <?php if($event['institute_id'] == $inst['id']) echo 'selected'; ?>><?php echo htmlspecialchars($inst['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Факультет:</label>
                    <select name="faculty_id" id="faculty_id" class="form-control" disabled>
                        <option value="">-- Выберите --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Категория (Тип):</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- Выберите --</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php if($event['category_id'] == $cat['id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group span-2"><label>Название мероприятия:</label><input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? $event['title']); ?>" class="form-control" required></div>
                <div class="form-group"><label>Дата начала:</label><input type="text" name="date_start" value="<?php echo htmlspecialchars($_POST['date_start'] ?? date('d.m.Y', strtotime($event['date_start']))); ?>" class="form-control datepicker" required></div>
                <div class="form-group"><label>Дата окончания:</label><input type="text" name="date_end" value="<?php echo htmlspecialchars($_POST['date_end'] ?? date('d.m.Y', strtotime($event['date_end']))); ?>" class="form-control datepicker"></div>
                
                <div class="form-group span-2">
                    <label>Место проведения:</label>
                    <?php
                        $current_loc = $_POST['location_std'] ?? $event['location'];
                        $is_custom_now = !in_array($current_loc, STD_LOCATIONS);
                    ?>
                    <select id="location_select" name="location_std" class="form-control" onchange="toggleCustomLocation()" required>
                        <?php foreach(STD_LOCATIONS as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc); ?>" <?php if(!$is_custom_now && $current_loc == $loc) echo 'selected'; ?>><?php echo htmlspecialchars($loc); ?></option>
                        <?php endforeach; ?>
                        <option value="other" class="option-custom" <?php if($is_custom_now) echo 'selected'; ?>>-- Свой вариант (ввести вручную) --</option>
                    </select>
                    <input type="text" id="location_custom" name="location_custom" value="<?php echo $is_custom_now ? htmlspecialchars($current_loc) : ''; ?>" class="form-control input-custom-location" placeholder="Введите свой адрес..." <?php echo $is_custom_now ? 'required' : 'disabled'; ?> style="<?php echo $is_custom_now ? '' : 'display:none;'; ?>">
                </div>

                <div class="form-group"><label>Институт (Организатор):</label><input type="text" name="org_inst" value="<?php echo htmlspecialchars($_POST['org_inst'] ?? $org_inst); ?>" class="form-control" required></div>
                <div class="form-group"><label>ФИО (Организатор):</label><input type="text" name="org_fio" value="<?php echo htmlspecialchars($_POST['org_fio'] ?? $org_fio); ?>" class="form-control" required></div>
                
                <div class="form-group span-2"><label>Краткое описание:</label><textarea name="description" rows="3" class="form-control" required><?php echo htmlspecialchars($_POST['description'] ?? $event['description']); ?></textarea></div>
            </div>
            
            <h3 class="section-title">Участники и Награды</h3>
            <div id="participants_container">
                <?php foreach($participants as $index => $part): ?>
                    <div class="participant-card" id="part_<?php echo $index; ?>">
                        <button type="button" class="btn-delete" onclick="document.getElementById('part_<?php echo $index; ?>').remove()">✖ Удалить</button>
                        <div class="form-grid form-grid-with-delete">
                            <div class="form-group"><label>Студент или Группа:</label><input type="text" name="participant_info[]" value="<?php echo htmlspecialchars($part['participant_info']); ?>" class="form-control" required></div>
                            <div class="form-group"><label>Роль:</label>
                                <select name="role_in_event[]" class="form-control">
                                    <?php foreach(PARTICIPANT_ROLES as $role): ?>
                                        <option value="<?php echo $role; ?>" <?php if($part['role_in_event']==$role) echo 'selected'; ?>><?php echo $role; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group span-2"><label>Награда / Достижение:</label><input type="text" name="award_details[]" value="<?php echo htmlspecialchars($part['award_details']); ?>" class="form-control"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" onclick="addParticipantRow()" class="btn btn-gray btn-add-participant">+ Добавить еще участника / группу</button>
            
            <div class="form-actions">
                <a href="index.php" class="btn btn-back btn-large">Назад</a>
                <button type="submit" class="btn btn-large">Сохранить изменения</button>
            </div>
        </form>
    </div>
    <script>
        flatpickr(".datepicker", { locale: "ru", dateFormat: "d.m.Y", allowInput: true });
        
        // Загрузить факультеты при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            const instSelect = document.getElementById('institute_id');
            if (instSelect && instSelect.value) {
                loadFacultiesForForm(instSelect.value, 'faculty_id', <?php echo $event['faculty_id'] ?: 'null'; ?>);
            }
        });
    </script>
</body>
</html>
