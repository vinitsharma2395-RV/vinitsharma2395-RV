"""Bulk-import users from a CSV file with columns name,email,age."""

import csv
import os
import sys

import mysql.connector

if len(sys.argv) != 2:
    raise SystemExit("Usage: python 14_import_users_csv.py users.csv")

with open(sys.argv[1], newline="", encoding="utf-8-sig") as csv_file:
    reader = csv.DictReader(csv_file)
    required = {"name", "email", "age"}
    if reader.fieldnames is None or not required.issubset(reader.fieldnames):
        raise SystemExit("CSV header must include name,email,age.")
    records = []
    for line_number, row in enumerate(reader, start=2):
        name, email = (row["name"] or "").strip(), (row["email"] or "").strip()
        age_value = (row["age"] or "").strip()
        if not name or not email:
            raise SystemExit(f"Missing name or email on CSV line {line_number}.")
        try:
            age = int(age_value) if age_value else None
        except ValueError as exc:
            raise SystemExit(f"Invalid age on CSV line {line_number}.") from exc
        if age is not None and not 0 <= age <= 120:
            raise SystemExit(f"Age out of range on CSV line {line_number}.")
        records.append((name, email, age))

connection = mysql.connector.connect(
    host=os.getenv("MYSQL_HOST", "127.0.0.1"),
    port=int(os.getenv("MYSQL_PORT", "3306")),
    user=os.environ["MYSQL_USER"],
    password=os.environ["MYSQL_PASSWORD"],
    database=os.getenv("MYSQL_DATABASE", "python_web_db"),
)
try:
    cursor = connection.cursor()
    cursor.executemany(
        "INSERT INTO users (name, email, age) VALUES (%s, %s, %s)",
        records,
    )
    connection.commit()
    print(f"Imported {cursor.rowcount} row(s).")
except mysql.connector.Error:
    connection.rollback()
    raise
finally:
    connection.close()
