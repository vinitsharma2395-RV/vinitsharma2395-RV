"""Delete a user by ID and report database errors."""

import os
import sys

import mysql.connector

if len(sys.argv) != 2:
    raise SystemExit("Usage: python 09_delete_user.py USER_ID")
try:
    user_id = int(sys.argv[1])
    if user_id < 1:
        raise ValueError
except ValueError as exc:
    raise SystemExit("USER_ID must be a positive integer.") from exc

connection = None
try:
    connection = mysql.connector.connect(
        host=os.getenv("MYSQL_HOST", "127.0.0.1"),
        port=int(os.getenv("MYSQL_PORT", "3306")),
        user=os.environ["MYSQL_USER"],
        password=os.environ["MYSQL_PASSWORD"],
        database=os.getenv("MYSQL_DATABASE", "python_web_db"),
    )
    cursor = connection.cursor()
    cursor.execute("DELETE FROM users WHERE id = %s", (user_id,))
    connection.commit()
    print("User deleted." if cursor.rowcount else "No user found with that ID.")
except mysql.connector.Error as exc:
    if connection is not None and connection.is_connected():
        connection.rollback()
    print(f"Database operation failed: {exc}", file=sys.stderr)
    raise SystemExit(1) from exc
finally:
    if connection is not None and connection.is_connected():
        connection.close()
