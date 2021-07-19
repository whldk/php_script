#!/bin/bash
SCRIPT_FOLDER=$(dirname $(readlink -f "$0"))
echo "login/refresh..."
/usr/bin/php "$SCRIPT_FOLDER/script.php" login/refresh
echo