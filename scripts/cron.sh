#!/bin/bash
SCRIPT_FOLDER=$(dirname $(readlink -f "$0"))
echo "wallet-log/refresh..."
/usr/bin/php "$SCRIPT_FOLDER/script.php" wallet-log/refresh
echo