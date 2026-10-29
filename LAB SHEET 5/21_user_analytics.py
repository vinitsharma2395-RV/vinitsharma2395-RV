"""Print user-count and average-age summary metrics for an admin dashboard."""

import os

import mysql.connector

connection = mysql.connector.connect(
    host=os.getenv("MYSQL_HOST", "127.0.0.1"),
    port=int(os.getenv("MYSQL_PORT", "3306")),
    user=os.environ["MYSQL_USER"],
    password=os.environ["MYSQL_PASSWORD"],
    database=os.getenv("MYSQL_DATABASE", "python_web_db"),
)
try:
    cursor = connection.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT COUNT(*) AS user_count,
               ROUND(AVG(age), 2) AS average_age,
               MIN(age) AS youngest_age,
               MAX(age) AS oldest_age
        FROM users
        """
    )
    summary = cursor.fetchone()
    print("User analytics")
    for metric, value in summary.items():
        print(f"{metric}: {value if value is not None else 'N/A'}")
finally:
    connection.close()
