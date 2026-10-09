@echo off
setlocal enabledelayedexpansion
title Opunga Cyber and Electronics - Host

set "ROOT=%~dp0"
set "XAMPP=C:\xampp"
set "LOGDIR=%TEMP%\opunga-host"
set "TUNNELLOG=%LOGDIR%\tunnel.log"
if not exist "%LOGDIR%" mkdir "%LOGDIR%"

echo(
echo   Opunga Cyber and Electronics - hosting stack
echo   -------------------------------------------
echo(

REM 1. Database (MariaDB / MySQL)
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if errorlevel 1 (
    echo   [1/4] Starting database...
    start "Opunga DB" /min "%XAMPP%\mysql\bin\mysqld.exe" --standalone
    timeout /t 6 /nobreak >nul
) else (
    echo   [1/4] Database already running.
)

REM 2. Apache web server
tasklist /FI "IMAGENAME eq httpd.exe" 2>nul | find /I "httpd.exe" >nul
if errorlevel 1 (
    echo   [2/4] Starting Apache...
    start "Opunga Apache" /min "%XAMPP%\apache\bin\httpd.exe"
    timeout /t 4 /nobreak >nul
) else (
    echo   [2/4] Apache already running.
)

REM 3. Sanity check the local site
echo   [3/4] Checking local site...
curl -s -o nul -w "         local site status: %%{http_code}\n" "http://localhost/opunga-electronics/index.php"

REM 4. Public tunnel (always start a fresh one and capture the URL)
echo   [4/4] Starting public tunnel...
taskkill /IM cloudflared.exe /F >nul 2>&1
if exist "%TUNNELLOG%" del /q "%TUNNELLOG%"
start "Opunga Tunnel" /min cmd /c "npx -y cloudflared tunnel --url http://localhost > "%TUNNELLOG%" 2>&1"

echo(
echo   Waiting for a public URL...
set "PUBURL="
for /L %%i in (1,1,20) do (
    timeout /t 3 /nobreak >nul
    if exist "%TUNNELLOG%" (
        for /f "tokens=*" %%u in ('findstr /R /C:"https://[a-z0-9-]*\.trycloudflare\.com" "%TUNNELLOG%" 2^>nul') do set "PUBURL=%%u"
    )
    if defined PUBURL goto :ready
)
goto :ready

:ready
echo(
if defined PUBURL (
    for /f "tokens=2" %%a in ("%PUBURL%") do set "LINK=%%a"
    echo   PUBLIC LINK: !LINK!/opunga-electronics/
) else (
    echo   Could not read the URL yet. Open "%TUNNELLOG%" to find it.
)
echo   LOCAL LINK:  http://localhost/opunga-electronics/
echo(
echo   Keep this window open. Close it (and the tunnel window) to stop hosting.
pause
