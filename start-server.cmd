@echo off
cd /d "%~dp0"
start "" http://localhost:8000/login.html
npm start
pause
