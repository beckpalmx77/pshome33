@echo off
:loop
php mq_send.php
timeout /t 10 /nobreak > NUL
goto :loop