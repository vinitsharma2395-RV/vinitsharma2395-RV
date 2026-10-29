"""Log database failures and access timestamps to a dedicated log."""

import logging
import os
from datetime import datetime, timezone

import mysql.connector

logging.basicConfig(
    filename=os.getenv("PYTHON_DB_LOG", "python_database.log"),
    level=logging.INFO,
    format="%(asctime)s %(levelname)s %(message)s",
)
logging.info("Database check started at %s", datetime.now(timezone.utc).isoformat())

try:
    connection = mysql.connector.connect(
        host=os.getenv("MYSQL_HOST", "127.0.0.1"),
        port=int(os.getenv("MYSQL_PORT", "3306")),
        user=os.environ["MYSQL_USER"],
        password=os.environ["MYSQL_PASSWORD"],
        database=os.getenv("MYSQL_DATABASE", "python_web_db"),
    )
    try:
        cursor = connection.cursor()
        cursor.execute("SELECT 1")
        logging.info("Database check succeeded.")
    finally:
        connection.close()
except (mysql.connector.Error, KeyError, ValueError):
    logging.exception("Database check failed.")
    raise
