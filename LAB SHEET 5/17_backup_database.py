"""Write CREATE statements and table rows to a SQL backup file."""

import os
from datetime import date, datetime, time
from decimal import Decimal

import mysql.connector


def sql_literal(value):
    if value is None:
        return "NULL"
    if isinstance(value, bool):
        return "1" if value else "0"
    if isinstance(value, (int, float, Decimal)):
        return str(value)
    if isinstance(value, bytes):
        return "X'" + value.hex() + "'"
    if isinstance(value, (date, datetime, time)):
        value = value.isoformat(sep=" ") if isinstance(value, datetime) else value.isoformat()
    escaped = str(value).replace("\\", "\\\\").replace("'", "''").replace("\0", "\\0")
    return "'" + escaped + "'"


database = os.getenv("MYSQL_DATABASE", "python_web_db")
connection = mysql.connector.connect(
    host=os.getenv("MYSQL_HOST", "127.0.0.1"),
    port=int(os.getenv("MYSQL_PORT", "3306")),
    user=os.environ["MYSQL_USER"],
    password=os.environ["MYSQL_PASSWORD"],
    database=database,
)
try:
    cursor = connection.cursor()
    cursor.execute("SHOW TABLES")
    tables = [row[0] for row in cursor.fetchall()]
    with open(f"{database}_backup.sql", "w", encoding="utf-8", newline="\n") as backup:
        backup.write(f"CREATE DATABASE IF NOT EXISTS `{database}`;\nUSE `{database}`;\n\n")
        for table in tables:
            safe_table = table.replace("`", "``")
            cursor.execute(f"SHOW CREATE TABLE `{safe_table}`")
            create_sql = cursor.fetchone()[1]
            backup.write(f"DROP TABLE IF EXISTS `{safe_table}`;\n{create_sql};\n")
            cursor.execute(f"SELECT * FROM `{safe_table}`")
            columns = [description[0] for description in cursor.description]
            safe_columns = ", ".join(f"`{column.replace('`', '``')}`" for column in columns)
            for row in cursor.fetchall():
                values = ", ".join(sql_literal(value) for value in row)
                backup.write(f"INSERT INTO `{safe_table}` ({safe_columns}) VALUES ({values});\n")
            backup.write("\n")
    print(f"Backup written to {database}_backup.sql")
finally:
    connection.close()
