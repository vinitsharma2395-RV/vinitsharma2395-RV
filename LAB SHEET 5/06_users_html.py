"""Return users as an HTML table suitable for a CGI response."""

import html
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
    cursor.execute("SELECT id, name, email, age FROM users ORDER BY id")
    rows = cursor.fetchall()
finally:
    connection.close()

parts = [
    "Content-Type: text/html; charset=utf-8",
    "",
    "<!doctype html><html lang='en'><meta charset='utf-8'>",
    "<title>Users</title><h1>Users</h1>",
    "<table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Age</th></tr></thead><tbody>",
]
for row in rows:
    cells = (
        row["id"],
        row["name"],
        row["email"],
        "" if row["age"] is None else row["age"],
    )
    parts.append("<tr>" + "".join(f"<td>{html.escape(str(value))}</td>" for value in cells) + "</tr>")
parts.extend(["</tbody></table></html>"])
print("\n".join(parts))
