#!/usr/bin/env python3
"""CGI form endpoint that inserts a user using a parameterized query."""

import html
import logging
import os
import re

try:
    import cgi
except ModuleNotFoundError as exc:
    raise SystemExit(
        "Python 3.13+ needs the compatibility module: "
        "python -m pip install legacy-cgi"
    ) from exc

import mysql.connector


def main():
    print("Content-Type: text/html; charset=utf-8")
    print()
    try:
        form = cgi.FieldStorage()
        name = form.getfirst("name", "").strip()
        email = form.getfirst("email", "").strip()
        age_text = form.getfirst("age", "").strip()
        if not name or len(name) > 120:
            raise ValueError("Name is required and must be at most 120 characters.")
        if len(email) > 254 or not re.fullmatch(r"[^@\s]+@[^@\s]+\.[^@\s]+", email):
            raise ValueError("Enter a valid email address.")
        age = None
        if age_text:
            age = int(age_text)
            if not 0 <= age <= 120:
                raise ValueError("Age must be between 0 and 120.")

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
                "INSERT INTO users (name, email, age) VALUES (%s, %s, %s)",
                (name, email, age),
            )
            connection.commit()
        finally:
            connection.close()
        print("<h1>User added</h1>")
    except (ValueError, mysql.connector.Error, KeyError) as exc:
        message = "The form could not be processed. Check the submitted values and database configuration."
        if isinstance(exc, ValueError):
            message = str(exc)
        elif not isinstance(exc, KeyError):
            logging.exception("CGI user insert failed.")
        print(f"<h1>Unable to add user</h1><p>{html.escape(message)}</p>")


if __name__ == "__main__":
    main()
