jsql () 
{
	local CONF="$HOME/public_html/judgegirl/config.php"
	local user="$(cat $CONF |  grep MySQLuser | cut -d\" -f 2)"
	local pass="$(cat $CONF |  grep MySQLpass | cut -d\" -f 2)"
	local db="$(cat $CONF |  grep MySQLdatabase | cut -d\" -f 2)"
	mysql -u "$user" "-p$pass" "$db" "$@"
}
jsqldump () 
{
	local CONF="$HOME/public_html/judgegirl/config.php"
	local user="$(cat $CONF |  grep MySQLuser | cut -d\" -f 2)"
	local pass="$(cat $CONF |  grep MySQLpass | cut -d\" -f 2)"
	local db="$(cat $CONF |  grep MySQLdatabase | cut -d\" -f 2)"
	mysqldump -u "$user" "-p$pass" "$db" "$@"
}
