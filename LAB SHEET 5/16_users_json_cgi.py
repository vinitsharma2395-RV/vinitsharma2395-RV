#!/usr/bin/env python3
"""CGI endpoint returning user records as JSON."""

import json
import logging
import os
import sys

import mysql.connector


def main():
    try:
        connection = mysql.connector.connect(
            host=os.getenv("MYSQL_HOST", "127.0.0.1"),
            port=int(os.getenv("MYSQL_PORT", "3306")),
            user=os.environ["MYSQL_USER"],
            password=os.environ["MYSQL_PASSWORD"],
            database=os.getenv("MYSQL_DATABASE", "python_web_db"),
        )
        try:
            cursor = connection.cursor(dictionary=True)
            cursor.execute("SELECT id, name, email, age FROM users ORDER BY id")
            users = cursor.fetchall()
        finally:
            connection.close()
        print("Status: 200 OK")
        print("Content-Type: application/json; charset=utf-8")
        print()
        print(json.dumps(users, default=str))
    except (mysql.connector.Error, KeyError, ValueError):
        logging.exception("CGI user query failed.")
        print("Status: 500 Internal Server Error")
        print("Content-Type: application/json; charset=utf-8")
        print()
        print(json.dumps({"error": "Unable to retrieve users."}))
        print("User query failed.", file=sys.stderr)


if __name__ == "__main__":
    main()
