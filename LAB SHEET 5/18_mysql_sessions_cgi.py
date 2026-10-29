#!/usr/bin/env python3
"""Small CGI example storing session state in MySQL."""

import json
import os
import secrets
from http.cookies import SimpleCookie

try:
    import cgi
except ModuleNotFoundError as exc:
    raise SystemExit(
        "Python 3.13+ needs the compatibility module: "
        "python -m pip install legacy-cgi"
    ) from exc

import mysql.connector


def main():
    cookie = SimpleCookie()
    cookie.load(os.getenv("HTTP_COOKIE", ""))
    session_id = cookie["python_lab_session"].value if "python_lab_session" in cookie else ""
    if len(session_id) != 64 or any(ch not in "0123456789abcdef" for ch in session_id):
        session_id = secrets.token_hex(32)

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
            """
            CREATE TABLE IF NOT EXISTS web_sessions (
                session_id CHAR(64) PRIMARY KEY,
                data JSON NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                INDEX (expires_at)
            ) ENGINE=InnoDB
            """
        )
        cursor.execute("DELETE FROM web_sessions WHERE expires_at < CURRENT_TIMESTAMP")
        cursor.execute("SELECT data FROM web_sessions WHERE session_id = %s", (session_id,))
        row = cursor.fetchone()
        state = json.loads(row[0]) if row else {"visits": 0}
        state["visits"] = int(state.get("visits", 0)) + 1
        cursor.execute(
            """
            INSERT INTO web_sessions (session_id, data, expires_at)
            VALUES (%s, %s, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 HOUR))
            ON DUPLICATE KEY UPDATE
                data = VALUES(data),
                expires_at = VALUES(expires_at)
            """,
            (session_id, json.dumps(state)),
        )
        connection.commit()
    finally:
        connection.close()

    cookie["python_lab_session"] = session_id
    cookie["python_lab_session"]["httponly"] = True
    cookie["python_lab_session"]["secure"] = os.getenv("HTTPS", "").lower() in ("on", "1")
    cookie["python_lab_session"]["samesite"] = "Lax"
    cookie["python_lab_session"]["path"] = "/"
    print(f"Set-Cookie: {cookie.output(header='').strip()}; Max-Age=3600")
    print("Content-Type: text/html; charset=utf-8")
    print()
    print(f"<h1>Session visit number {state['visits']}</h1>")


if __name__ == "__main__":
    main()
