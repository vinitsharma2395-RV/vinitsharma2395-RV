"""Reuse pooled connections across requests handled by one WSGI process.

Create one pool per Apache worker process and size it for that process's
concurrency; multiply pool_size by process count when planning DB capacity.
"""

import os

from mysql.connector.pooling import MySQLConnectionPool

pool = MySQLConnectionPool(
    pool_name="python_web_pool",
    pool_size=10,
    host=os.getenv("MYSQL_HOST", "127.0.0.1"),
    port=int(os.getenv("MYSQL_PORT", "3306")),
    user=os.environ["MYSQL_USER"],
    password=os.environ["MYSQL_PASSWORD"],
    database=os.getenv("MYSQL_DATABASE", "python_web_db"),
)


def get_user_count():
    connection = pool.get_connection()
    try:
        cursor = connection.cursor()
        cursor.execute("SELECT COUNT(*) FROM users")
        return cursor.fetchone()[0]
    finally:
        connection.close()


if __name__ == "__main__":
    print(f"User count: {get_user_count()}")
