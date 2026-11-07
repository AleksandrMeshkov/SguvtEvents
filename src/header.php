<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="header-wrapper">
    <div class="header-inner">
        <div class="header-brand">
            <img src="img/logo.png" alt="Логотип СГУВТ">
            <div class="header-text">
                СИБИРСКИЙ ГОСУДАРСТВЕННЫЙ УНИВЕРСИТЕТ ВОДНОГО ТРАНСПОРТА
                <div class="header-subtitle">Учет внеучебной деятельности</div>
            </div>
        </div>
        <div class="header-date">
            <?php echo getRussianDate(); ?>
        </div>
    </div>
</div>
<div class="nav-bar">
    <div class="nav-inner">
        <a href="index.php" class="<?php echo ($current_page == 'index.php' || $current_page == 'edit.php') ? 'active' : ''; ?>">СПИСОК МЕРОПРИЯТИЙ</a>
        <a href="add.php" class="<?php echo ($current_page == 'add.php') ? 'active' : ''; ?>">ДОБАВИТЬ ЗАПИСЬ</a>
        <a href="directories.php" class="<?php echo ($current_page == 'directories.php') ? 'active' : ''; ?>">СПРАВОЧНИКИ</a>
        <a href="report.php" class="<?php echo ($current_page == 'report.php') ? 'active' : ''; ?>">ОТЧЕТЫ И ЭКСПОРТ</a>
    </div>
</div>
