"""Verify that MySQL Connector/Python is available in the active environment."""

try:
    import mysql.connector
except ImportError as exc:
    raise SystemExit(
        "MySQL Connector/Python is missing. Install it with: "
        "python -m pip install mysql-connector-python"
    ) from exc

print(f"MySQL Connector/Python {mysql.connector.__version__} is installed.")
