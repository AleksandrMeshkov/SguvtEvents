<?php
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'sguvt_events_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

define('STD_LOCATIONS', [
    "Главный корпус: г. Новосибирск, ул. Щетинкина, 33",
    "УЛК1: г. Новосибирск, ул. Советская, 60",
    "УЛК2: г. Новосибирск, ул. Мичурина, 48"
]);

define('PARTICIPANT_ROLES', ['Участник', 'Победитель', 'Призер', 'Волонтер']);
