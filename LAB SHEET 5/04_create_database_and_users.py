"""Create python_web_db and its users table."""

import os

import mysql.connector

connection = mysql.connector.connect(
    host=os.getenv("MYSQL_HOST", "127.0.0.1"),
    port=int(os.getenv("MYSQL_PORT", "3306")),
    user=os.environ["MYSQL_USER"],
    password=os.environ["MYSQL_PASSWORD"],
)
try:
    cursor = connection.cursor()
    cursor.execute("CREATE DATABASE IF NOT EXISTS python_web_db")
    cursor.execute("USE python_web_db")
    cursor.execute(
        """
        CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(254) NOT NULL UNIQUE,
            age TINYINT UNSIGNED NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        """
    )
    print("Database python_web_db and table users are ready.")
finally:
    connection.close()
