@echo off
chcp 65001 >nul
setlocal

rem ============================================================
rem  СГУВТ Events - Сброс к начальному состоянию
rem ============================================================

rem Определяем корневую директорию (где лежит этот скрипт)
set "ROOT=%~dp0"
set "DATA_DIR=%ROOT%server\mysql\data"
set "LOG_DIR=%ROOT%logs"
set "FONTS_DIR=%ROOT%server\libs\tcpdf_fonts"

echo ============================================================
echo   СГУВТ Events - Сброс к начальному состоянию
echo ============================================================
echo.
echo ВНИМАНИЕ: Будут удалены все данные базы данных, логи и кэш!
echo Это действие необратимо!
echo.
set /p confirm="Вы уверены, что хотите продолжить? (да/нет): "
if /i not "%confirm%"=="да" (
    echo Операция отменена пользователем.
    pause
    exit /b 0
)
echo.

rem --- Остановка серверов если запущены ---
echo Остановка серверов (если запущены)...
taskkill /f /im mariadbd.exe >nul 2>&1
taskkill /f /im php.exe >nul 2>&1
timeout /t 2 /nobreak >nul
echo Серверы остановлены.
echo.

rem --- Очистка папки data ---
if exist "%DATA_DIR%" (
    echo Очистка папки data...
    del /q /s "%DATA_DIR%\*" >nul 2>&1
    for /d %%d in ("%DATA_DIR%\*") do (
        rmdir /s /q "%%d"
    )
    echo Папка data очищена.
) else (
    echo Папка data не существует - пропуск.
)

echo.

rem --- Очистка папки logs ---
if exist "%LOG_DIR%" (
    echo Очистка папки logs...
    del /q /s "%LOG_DIR%\*" >nul 2>&1
    for /d %%d in ("%LOG_DIR%\*") do (
        rmdir /s /q "%%d"
    )
    echo Папка logs очищена.
) else (
    echo Папка logs не существует - пропуск.
)

echo.

rem --- Очистка папки tcpdf_fonts ---
if exist "%FONTS_DIR%" (
    echo Очистка папки tcpdf_fonts...
    del /q /s "%FONTS_DIR%\*" >nul 2>&1
    for /d %%d in ("%FONTS_DIR%\*") do (
        rmdir /s /q "%%d"
    )
    echo Папка tcpdf_fonts очищена.
) else (
    echo Папка tcpdf_fonts не существует - пропуск.
)

echo.
echo ============================================================
echo   Сброс завершён!
echo   При следующем запуске start_server.bat
echo   база данных будет инициализирована заново.
echo ============================================================
echo.

pause
exit /b 0
