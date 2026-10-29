"""Apache mod_wsgi WSGI application that serves user records as JSON."""

import json
import logging
import os

import mysql.connector


def application(environ, start_response):
    if environ.get("PATH_INFO") != "/users":
        body = json.dumps({"error": "Not found"}).encode("utf-8")
        start_response(
            "404 Not Found",
            [("Content-Type", "application/json; charset=utf-8"),
             ("Content-Length", str(len(body)))],
        )
        return [body]

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
            body = json.dumps(cursor.fetchall(), default=str).encode("utf-8")
        finally:
            connection.close()
        status = "200 OK"
    except (mysql.connector.Error, KeyError, ValueError):
        logging.exception("WSGI user query failed.")
        body = json.dumps({"error": "Unable to retrieve users."}).encode("utf-8")
        status = "500 Internal Server Error"

    start_response(
        status,
        [("Content-Type", "application/json; charset=utf-8"),
         ("Content-Length", str(len(body)))],
    )
    return [body]
