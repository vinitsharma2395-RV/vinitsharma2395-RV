"""Map the users table with SQLAlchemy ORM and MySQL Connector/Python."""

import os

from sqlalchemy import String, create_engine
from sqlalchemy.orm import DeclarativeBase, Mapped, mapped_column, sessionmaker
from sqlalchemy.engine import URL


class Base(DeclarativeBase):
    pass


class User(Base):
    __tablename__ = "users"

    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str] = mapped_column(String(120), nullable=False)
    email: Mapped[str] = mapped_column(String(254), unique=True, nullable=False)
    age: Mapped[int | None] = mapped_column(nullable=True)


url = URL.create(
    "mysql+mysqlconnector",
    username=os.environ["MYSQL_USER"],
    password=os.environ["MYSQL_PASSWORD"],
    host=os.getenv("MYSQL_HOST", "127.0.0.1"),
    port=int(os.getenv("MYSQL_PORT", "3306")),
    database=os.getenv("MYSQL_DATABASE", "python_web_db"),
)
engine = create_engine(url, pool_pre_ping=True)
Base.metadata.create_all(engine)
Session = sessionmaker(bind=engine)
with Session() as session:
    print(f"Mapped users: {session.query(User).count()}")
engine.dispose()
