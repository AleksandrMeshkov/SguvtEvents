@echo off
chcp 65001 >nul
setlocal

rem ============================================================
rem  СГУВТ Events - Запуск сервера (.bat версия лаунчера)
rem ============================================================

rem Определяем корневую директорию (где лежит этот скрипт)
set "ROOT=%~dp0"
set "MYSQL_DIR=%ROOT%server\mysql"
set "PHP_DIR=%ROOT%server\php"
set "WWW_DIR=%ROOT%src"
set "SQL_DIR=%ROOT%sql"
set "LOG_DIR=%ROOT%logs"
set "DATA_DIR=%MYSQL_DIR%\data"

rem Создаём папку логов
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"

echo ============================================================
echo   СГУВТ Events - Портативный бандл
echo ============================================================
echo.

rem --- Проверка наличия бинарников ---
if not exist "%MYSQL_DIR%\bin\mariadbd.exe" goto err_mariadb
if not exist "%PHP_DIR%\php.exe" goto err_php

rem --- Проверка занятости портов ---
netstat -ano | findstr ":3306" | findstr "LISTENING" >nul 2>&1
if not errorlevel 1 goto err_port3306
netstat -ano | findstr ":8080" | findstr "LISTENING" >nul 2>&1
if not errorlevel 1 goto err_port8080

rem --- Проверка инициализации БД (наличие системных таблиц data\mysql) ---
if not exist "%DATA_DIR%\mysql" goto init_db

goto run_start_servers

:init_db
echo Первый запуск - инициализация базы данных...
echo.

rem Очистка data directory от остатков
if exist "%DATA_DIR%" rmdir /s /q "%DATA_DIR%"
mkdir "%DATA_DIR%"

rem Инициализация data directory (создание системных таблиц mysql.*)
echo Инициализация data directory...
"%MYSQL_DIR%\bin\mariadb-install-db.exe" --datadir="%DATA_DIR%"
if not exist "%DATA_DIR%\mysql" goto err_init

rem Запуск MariaDB для настройки
echo Запуск MariaDB для настройки...
start /b "" "%MYSQL_DIR%\bin\mariadbd.exe" --defaults-file="%MYSQL_DIR%\my.ini" --basedir="%MYSQL_DIR%" --datadir="%DATA_DIR%" --tmpdir="%LOG_DIR%" --log-error="%LOG_DIR%\mysql_init.log"

rem Ожидание готовности MariaDB
echo Ожидание готовности MariaDB...
set ready=0
for /l %%i in (1,1,60) do (
    "%MYSQL_DIR%\bin\mysqladmin.exe" -h 127.0.0.1 -u root ping >nul 2>&1
    if not errorlevel 1 (
        set ready=1
        goto ready_ok
    )
    timeout /t 1 /nobreak >nul
)
goto err_mariadb_start

:ready_ok
rem Создание базы данных и таблиц
echo Создание базы данных и таблиц...
"%MYSQL_DIR%\bin\mysql.exe" -h 127.0.0.1 -u root -e "source %SQL_DIR%\create_db.sql"

rem Заполнение тестовыми данными
echo Заполнение тестовыми данными...
"%MYSQL_DIR%\bin\mysql.exe" -h 127.0.0.1 -u root sguvt_events_db -e "source %SQL_DIR%\fill_db.sql"

rem Остановка MariaDB
echo Остановка MariaDB...
"%MYSQL_DIR%\bin\mysqladmin.exe" -h 127.0.0.1 -u root shutdown
timeout /t 2 /nobreak >nul

echo База данных инициализирована!
echo.

:run_start_servers
rem --- Запуск MariaDB ---
echo Запуск MariaDB...
start /b "" "%MYSQL_DIR%\bin\mariadbd.exe" --defaults-file="%MYSQL_DIR%\my.ini" --basedir="%MYSQL_DIR%" --datadir="%DATA_DIR%" --tmpdir="%LOG_DIR%" --log-error="%LOG_DIR%\mysql_error.log"

rem Ожидание готовности MariaDB
echo Ожидание готовности MariaDB...
set ready=0
for /l %%i in (1,1,60) do (
    "%MYSQL_DIR%\bin\mysqladmin.exe" -h 127.0.0.1 -u root ping >nul 2>&1
    if not errorlevel 1 (
        set ready=1
        goto :ready_ok2
    )
    timeout /t 1 /nobreak >nul
)
goto err_mariadb_start

:ready_ok2
rem --- Запуск PHP сервера ---
echo Запуск PHP сервера...
start /b "" "%PHP_DIR%\php.exe" -c "%PHP_DIR%\php.ini" -d extension_dir="%PHP_DIR%\ext" -d error_log="%LOG_DIR%\php_error.log" -d session.save_path="%LOG_DIR%" -S localhost:8080 -t "%WWW_DIR%" >> "%LOG_DIR%\php_access.log" 2>&1

rem --- Открытие браузера ---
start "" "http://localhost:8080"

cls
echo.
echo ================================================================
echo   Сервер запущен!
echo   Откройте браузер: http://localhost:8080
echo   Для остановки закройте это окно
echo ================================================================
echo.

rem --- Ожидание завершения (пока окно открыто) ---
pause

rem --- Остановка серверов при закрытии ---
echo Остановка серверов...
"%MYSQL_DIR%\bin\mysqladmin.exe" -h 127.0.0.1 -u root shutdown >nul 2>&1
taskkill /f /im php.exe >nul 2>&1
echo Серверы остановлены.
exit /b 0

:err_mariadb
echo ОШИБКА: MariaDB не найден в server\mysql\bin\
pause
exit /b 1

:err_php
echo ОШИБКА: PHP не найден в server\php\
pause
exit /b 1

:err_port3306
echo ОШИБКА: Порт 3306 уже занят! Возможно, уже запущен другой MySQL сервер.
pause
exit /b 1

:err_port8080
echo ОШИБКА: Порт 8080 уже занят!
pause
exit /b 1

:err_init
echo ОШИБКА: Инициализация data directory не удалась (нет системных таблиц).
pause
exit /b 1

:err_mariadb_start
echo ОШИБКА: MariaDB не запустился.
pause
exit /b 1