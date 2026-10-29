"""Filter users by name without interpolating user input into SQL."""

import os
import sys

import mysql.connector

term = " ".join(sys.argv[1:]).strip()
if not term or len(term) > 120:
    raise SystemExit("Provide a search term of 1-120 characters.")

connection = mysql.connector.connect(
    host=os.getenv("MYSQL_HOST", "127.0.0.1"),
    port=int(os.getenv("MYSQL_PORT", "3306")),
    user=os.environ["MYSQL_USER"],
    password=os.environ["MYSQL_PASSWORD"],
    database=os.getenv("MYSQL_DATABASE", "python_web_db"),
)
try:
    cursor = connection.cursor(dictionary=True)
    cursor.execute(
        "SELECT id, name, email, age FROM users WHERE name LIKE %s ORDER BY name",
        (f"%{term}%",),
    )
    for user in cursor.fetchall():
        print(user)
finally:
    connection.close()
