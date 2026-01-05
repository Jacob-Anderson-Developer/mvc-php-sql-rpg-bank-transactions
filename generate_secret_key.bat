@echo off
cd /d "%~dp0"

php config\generate_secret_key.php

pause