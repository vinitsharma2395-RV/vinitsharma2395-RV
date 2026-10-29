"""Insert several example users with executemany()."""

import os

import mysql.connector

connection = mysql.connector.connect(
    host=os.getenv("MYSQL_HOST", "127.0.0.1"),
    port=int(os.getenv("MYSQL_PORT", "3306")),
    user=os.environ["MYSQL_USER"],
    password=os.environ["MYSQL_PASSWORD"],
    database=os.getenv("MYSQL_DATABASE", "python_web_db"),
)
try:
    cursor = connection.cursor()
    users = [
        ("Aarav Mehta", "aarav@example.com", 21),
        ("Diya Sharma", "diya@example.com", 22),
        ("Kabir Patel", "kabir@example.com", 20),
    ]
    cursor.executemany(
        "INSERT INTO users (name, email, age) VALUES (%s, %s, %s)",
        users,
    )
    connection.commit()
    print(f"Inserted {cursor.rowcount} user record(s).")
except mysql.connector.Error:
    connection.rollback()
    raise
finally:
    connection.close()
