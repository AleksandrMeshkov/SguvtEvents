<?php
function getRussianDate() {
    $months = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
    $days = ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'];
    return "Сегодня " . date('j') . " " . $months[date('n')-1] . " " . date('Y') . ", " . $days[date('w')];
}

function validateDate($date, $format = 'd.m.Y') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function displayAlerts() {
    if (!empty($_SESSION['error'])) {
        echo '<div class="alert alert-error"><b>Ошибка:</b> ' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    if (!empty($_SESSION['success'])) {
        echo '<div class="alert alert-success"><b>Успешно:</b> ' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
}
