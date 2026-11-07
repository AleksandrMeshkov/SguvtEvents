<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_inst'])) {
            $short = trim($_POST['short_name'] ?? '');
            $full = trim($_POST['full_name'] ?? '');
            if ($short === '' || $full === '') throw new Exception("Поля подразделения не могут быть пустыми.");
            $instituteModel->create($short, $full);
            $_SESSION['success'] = "Подразделение успешно добавлено.";
        }
        
        if (isset($_POST['del_inst'])) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $instituteModel->delete($id);
                $_SESSION['success'] = "Подразделение удалено.";
            }
        }
        
        if (isset($_POST['add_cat'])) {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') throw new Exception("Название категории не может быть пустым.");
            $categoryModel->create($name);
            $_SESSION['success'] = "Категория успешно добавлена.";
        }
        
        if (isset($_POST['del_cat'])) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $categoryModel->delete($id);
                $_SESSION['success'] = "Категория удалена.";
            }
        }
        
        if (isset($_POST['add_faculty'])) {
            $name = trim($_POST['faculty_name'] ?? '');
            $institute_id = filter_input(INPUT_POST, 'faculty_institute_id', FILTER_VALIDATE_INT);
            if ($name === '' || !$institute_id) throw new Exception("Название факультета и институт обязательны.");
            $facultyModel->create($name, $institute_id);
            $_SESSION['success'] = "Факультет успешно добавлен.";
        }
        
        if (isset($_POST['del_faculty'])) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if ($id) {
                $facultyModel->delete($id);
                $_SESSION['success'] = "Факультет удален.";
            }
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Ошибка: Запись уже используется в базе данных (привязана к мероприятию) или произошел сбой БД.";
    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header("Location: directories.php");
    exit();
}

$institutes = $instituteModel->getAll();
$categories = $categoryModel->getAll();
$faculties = $facultyModel->getAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Справочники - СГУВТ</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2 class="page-title">Справочники системы</h2>
        
        <?php displayAlerts(); ?>
        
        <div class="directories-grid">
            <div class="form-card">
                <h3>Подразделения</h3>
                <form method="POST" class="form-inline-add">
                    <input type="text" name="short_name" class="form-control" placeholder="Аббревиатура" required>
                    <input type="text" name="full_name" class="form-control" placeholder="Полное название" required>
                    <button type="submit" name="add_inst" class="btn">Добавить</button>
                </form>
                <table class="main-table">
                    <?php foreach($institutes as $i): ?>
                        <tr>
                            <td><b><?php echo htmlspecialchars($i['short_name']); ?></b> - <?php echo htmlspecialchars($i['full_name']); ?></td>
                            <td class="col-delete">
                                <form method="POST" onsubmit="return confirm('Точно удалить?');">
                                    <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                                    <button type="submit" name="del_inst" class="btn btn-red">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            
            <div class="form-card">
                <h3>Типы мероприятий</h3>
                <form method="POST" class="form-inline-add">
                    <input type="text" name="name" class="form-control input-full" placeholder="Название категории" required>
                    <button type="submit" name="add_cat" class="btn">Добавить</button>
                </form>
                <table class="main-table">
                    <?php foreach($categories as $c): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c['name']); ?></td>
                            <td class="col-delete">
                                <form method="POST" onsubmit="return confirm('Точно удалить?');">
                                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                    <button type="submit" name="del_cat" class="btn btn-red">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        
        <div class="form-card" style="margin-top: 20px;">
            <h3>Факультеты (Кафедры)</h3>
            <form method="POST" class="form-inline-add">
                <input type="text" name="faculty_name" class="form-control" placeholder="Название факультета" required style="flex: 2;">
                <select name="faculty_institute_id" class="form-control" required>
                    <option value="">-- Институт --</option>
                    <?php foreach($institutes as $inst): ?>
                        <?php if($inst['short_name'] !== 'ОБЩЕЕ'): ?>
                            <option value="<?php echo $inst['id']; ?>"><?php echo htmlspecialchars($inst['short_name']); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="add_faculty" class="btn">Добавить</button>
            </form>
            <table class="main-table">
                <?php foreach($faculties as $f): ?>
                    <tr>
                        <td><b><?php echo htmlspecialchars($f['institute_short']); ?></b> — <?php echo htmlspecialchars($f['name']); ?></td>
                        <td class="col-delete">
                            <form method="POST" onsubmit="return confirm('Точно удалить факультет?');">
                                <input type="hidden" name="id" value="<?php echo $f['id']; ?>">
                                <button type="submit" name="del_faculty" class="btn btn-red">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
