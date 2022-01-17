#!/bin/bash
set -e

RUNMYSQL="mysql --user=root --password=${MYSQL_ROOT_PASSWORD}"

$RUNMYSQL <<EOSQL
CREATE DATABASE ${MYSQL_TEST_DATABASE};
GRANT ALL PRIVILEGES ON ${MYSQL_TEST_DATABASE}.* TO '${MYSQL_USER}'@'%';
FLUSH PRIVILEGES;
EOSQL