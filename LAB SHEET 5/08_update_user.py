"""Update a user's profile from command-line arguments."""

import os
import re
import sys

import mysql.connector

if len(sys.argv) != 5:
    raise SystemExit("Usage: python 08_update_user.py USER_ID NAME EMAIL AGE")

try:
    user_id = int(sys.argv[1])
    age = int(sys.argv[4])
except ValueError as exc:
    raise SystemExit("USER_ID and AGE must be integers.") from exc

name, email = sys.argv[2].strip(), sys.argv[3].strip()
if user_id < 1 or not name or len(name) > 120:
    raise SystemExit("USER_ID must be positive and NAME must contain 1-120 characters.")
if not re.fullmatch(r"[^@\s]+@[^@\s]+\.[^@\s]+", email) or len(email) > 254:
    raise SystemExit("EMAIL must be valid and no longer than 254 characters.")
if not 0 <= age <= 120:
    raise SystemExit("AGE must be between 0 and 120.")

connection = mysql.connector.connect(
    host=os.getenv("MYSQL_HOST", "127.0.0.1"),
    port=int(os.getenv("MYSQL_PORT", "3306")),
    user=os.environ["MYSQL_USER"],
    password=os.environ["MYSQL_PASSWORD"],
    database=os.getenv("MYSQL_DATABASE", "python_web_db"),
)
try:
    cursor = connection.cursor()
    cursor.execute(
        "UPDATE users SET name = %s, email = %s, age = %s WHERE id = %s",
        (name, email, age, user_id),
    )
    connection.commit()
    print("User updated." if cursor.rowcount else "No user found with that ID.")
except mysql.connector.Error:
    connection.rollback()
    raise
finally:
    connection.close()
