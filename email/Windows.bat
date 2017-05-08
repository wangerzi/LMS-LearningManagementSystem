@echo off
:: 转为utf8编码
chcp 65001

:: 执行脚本，注意系统的php.exe是否在环境变量中
php time.php
pause