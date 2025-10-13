<?php
header('Content-Type: text/plain; charset=utf-8');
echo "TZ=" . date_default_timezone_get() . "\n";
echo "NOW=" . date('c') . "\n";
echo "INI=" . php_ini_loaded_file() . "\n";
echo "SAPI=" . php_sapi_name() . "\n";
