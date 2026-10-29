"""Validate CGI form values before a parameterized database insert."""

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
        name = " ".join(form.getfirst("name", "").split())
        email = form.getfirst("email", "").strip()
        age_text = form.getfirst("age", "").strip()
        age = None
        if age_text:
            age = int(age_text)
        if not name or len(name) > 120 or any(ord(char) < 32 for char in name):
            raise ValueError("Name must contain 1-120 printable characters.")
        if len(email) > 254 or not re.fullmatch(r"[^@\s]+@[^@\s]+\.[^@\s]+", email):
            raise ValueError("Enter a valid email address.")
        if age is not None and not 0 <= age <= 120:
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
        except mysql.connector.Error:
            connection.rollback()
            raise
        finally:
            connection.close()
        print("<h1>User saved</h1>")
    except (ValueError, mysql.connector.Error, KeyError) as exc:
        if isinstance(exc, mysql.connector.Error):
            logging.exception("Validated CGI form insert failed.")
            message = "The user could not be saved."
        elif isinstance(exc, KeyError):
            message = "Database credentials are not configured."
        else:
            message = str(exc)
        print(f"<h1>Unable to save user</h1><p>{html.escape(message)}</p>")


if __name__ == "__main__":
    main()
