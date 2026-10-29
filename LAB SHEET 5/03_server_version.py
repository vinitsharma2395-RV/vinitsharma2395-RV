"""Connect to MySQL and print the server version."""

import os
import sys

import mysql.connector


def main():
    user = os.getenv("MYSQL_USER")
    password = os.getenv("MYSQL_PASSWORD")
    if not user or password is None:
        raise SystemExit(
            "Set MYSQL_USER and MYSQL_PASSWORD environment variables before running this script."
        )

    try:
        port = int(os.getenv("MYSQL_PORT", "3306"))
        connection = mysql.connector.connect(
            host=os.getenv("MYSQL_HOST", "127.0.0.1"),
            port=port,
            user=user,
            password=password,
        )
        try:
            cursor = connection.cursor()
            try:
                cursor.execute("SELECT VERSION()")
                result = cursor.fetchone()
                if result is None:
                    raise RuntimeError("The MySQL server returned no version.")
                print(f"MySQL server version: {result[0]}")
            finally:
                cursor.close()
        finally:
            connection.close()
    except (mysql.connector.Error, ValueError) as exc:
        print(f"Unable to retrieve the MySQL server version: {exc}", file=sys.stderr)
        raise SystemExit(1) from exc


if __name__ == "__main__":
    main()
