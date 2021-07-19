#!/bin/bash
SCRIPT="$0"
echo "script:${SCRIPT}"
SCRIPT_FOLDER=$(dirname $(readlink -f "$SCRIPT"))
echo "path:${SCRIPT_FOLDER}"
echo "start deploy crotab:"
if [ ! -e /var/spool/cron/ ];then
	mkdir -p /var/spool/cron/
fi
#创建定时文件
touch /var/spool/cron/root
#添加执行权限
chown root "${SCRIPT_FOLDER}/cron.sh"
chgrp root "${SCRIPT_FOLDER}/cron.sh"
chmod 754 "${SCRIPT_FOLDER}/cron.sh"
#删除已有的定时任务
SCRIPT_FOLDER_TMP=${SCRIPT_FOLDER//\//\\\/}
echo "sed -i /${SCRIPT_FOLDER_TMP}\/cron.sh/d /var/spool/cron/root"
sed -i "/${SCRIPT_FOLDER_TMP}\/cron.sh/d" /var/spool/cron/root
#添加定时任务 每隔二个小时执行一次
echo "0 */2 * * * bash ${SCRIPT_FOLDER}/cron.sh >/dev/null 2>&1" >> /var/spool/cron/root
chown root "${SCRIPT_FOLDER}/login.sh"
chgrp root "${SCRIPT_FOLDER}/login.sh"
chmod 754 "${SCRIPT_FOLDER}/login.sh"
#删除已有的定时任务
SCRIPT_FOLDER_TMP=${SCRIPT_FOLDER//\//\\\/}
echo "sed -i /${SCRIPT_FOLDER_TMP}\/login.sh/d /var/spool/cron/root"
sed -i "/${SCRIPT_FOLDER_TMP}\/login.sh/d" /var/spool/cron/root
#添加定时任务 每分钟执行一次
echo "*/1 * * * * bash ${SCRIPT_FOLDER}/login.sh >/dev/null 2>&1" >> /var/spool/cron/root
#重载配置
systemctl restart crond
systemctl crond reload
systemctl status crond
#显示配置
crontab -u root -l
echo "done"