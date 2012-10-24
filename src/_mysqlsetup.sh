#!/bin/bash

confirm(){
  local ans=''
  while [ ! "$ans" ]; do
    read -r -p "$1 [y/n] " ans
    case $ans in 
      [Yy])
        return 0
        ;;
      [Nn])
        return 1
        ;;
      *)
        echo "please enter 'y' or 'n'"
        ans=''
        ;;
    esac
  done
}

mkdb(){
  echo "Creating database of '$db' for account '$user',"
  echo "you will be asked for MySQL root password"
  if [ ! "$pass" -o "$pass" = '__''SQLPASS''__' ]; then
      echo "Password empty or not assigned, generate random password"
      pass=`mktemp -u -p . | sed 's/.*\.//'`
  fi
cat <<EOF | mysql -uroot -p
CREATE DATABASE $db; 
GRANT ALL ON $db.* TO '$user'@'%' IDENTIFIED BY '$pass'; 
DELETE FROM mysql.user WHERE User = '';
FLUSH PRIVILEGES;
EOF
  if [ $? -ne 0 ]; then
    echo "Error during creating database, exiting..."
    exit 1
  fi
}

write_access(){
  echo "MySQL access stored into mysql.access,"
  touch mysql.access
  chmod 600 mysql.access
  cat <<EOF > mysql.access
\$MySQLuser = '$user';
\$MySQLpass = '$pass';
\$MySQLdatabase = '$db';
EOF
  chmod 400 mysql.access
}


### Begin procedure

CONF='__JCONF__'
if [ -r "$CONF" ]; then
  retstr=$( (
              cat "$CONF" | sed '/?>/,//d;s/<?php//i'
              echo -e 'print "$MySQLuser\\n$MySQLpass\\n$MySQLdatabase\\n";'
            ) | perl )
  user=$(echo "$retstr" | sed -n '1 p')
  pass=$(echo "$retstr" | sed -n '2 p')
    db=$(echo "$retstr" | sed -n '3 p')
fi


if [ "$user" -a "$user" != '__''SQLUSER''__' ]; then
  echo "Found db user name $user for managing database $db"
else
  echo "Configuration file not found or not properly setup"

  read -r -p "Enter database name for the judgesystem [$db]: " db
  read -r -p "Enter DB user for MySQL [$user]: " user
  if confirm "Shall I create database accordingly?";
  then
    echo "Enter password, leave empty for random generation"
    read -r -s -p "password(will not be echoed): " pass
    echo
    if [ ! "$pass" ];
    then
      echo "Genrating random password..."
      pass=`mktemp -u -p . | sed 's/.*\.//'`
    fi
    mkdb
  else
    read -r -s -p "Enter password(will not be echoed): " pass
    echo
  fi
fi

echo "show tables;" | mysql -u"$user" -p"$pass" "$db" 2>&1 >/dev/null
if [ $? -ne 0 ]; then
  echo "The provided MySQL access is invalid"
  write_access
  exit 1
fi

if [ -e "$CONF" ]; then
  echo "Updating configuration file..."
  sed -i "1,/?>/ {
        s@.*\$MySQLuser.*@\$MySQLuser = '$user';@
        s@.*\$MySQLpass.*@\$MySQLpass = '$pass';@
        s@.*\$MySQLdatabase.*@\$MySQLdatabase = '$db';@
      }" "$CONF"
else
  echo "Cannot find configuration file."
  echo "you have to update the information to 'conf.php' manually"
  write_access
fi
