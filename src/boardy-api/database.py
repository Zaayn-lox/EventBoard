import os
from contextlib import contextmanager

import pymysql
from pymysql.cursors import DictCursor


def get_db_config() -> dict:
    return {
        "host": os.getenv("DB_HOST", "mysql"),
        "port": int(os.getenv("DB_PORT", "3306")),
        "user": os.getenv("DB_USER", "eventboard"),
        "password": os.getenv("DB_PASSWORD", "eventboard_password"),
        "database": os.getenv("DB_NAME", "eventboard_laravel"),
        "charset": "utf8mb4",
        "cursorclass": DictCursor,
        "autocommit": True,
    }


@contextmanager
def get_connection():
    connection = pymysql.connect(**get_db_config())

    try:
        yield connection
    finally:
        connection.close()
