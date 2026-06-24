from fastapi import APIRouter, HTTPException

from database import get_connection

router = APIRouter(prefix="/api", tags=["events"])


@router.get("/categories")
def get_categories():
    with get_connection() as connection:
        with connection.cursor() as cursor:
            cursor.execute(
                """
                SELECT
                    id,
                    name,
                    slug
                FROM categories
                ORDER BY name
                """
            )

            return cursor.fetchall()


@router.get("/events")
def get_events(category: str | None = None):
    query = """
        SELECT
            events.id,
            events.title,
            events.description,
            events.location,
            events.starts_at,
            events.ends_at,
            events.image_url,
            categories.name AS category,
            categories.slug AS category_slug,
            users.name AS author_name,
            COUNT(event_registrations.id) AS participants_count
        FROM events
        JOIN categories ON categories.id = events.category_id
        JOIN users ON users.id = events.user_id
        LEFT JOIN event_registrations ON event_registrations.event_id = events.id
    """

    params: list[str] = []

    if category:
        query += " WHERE categories.slug = %s "
        params.append(category)

    query += """
        GROUP BY
            events.id,
            events.title,
            events.description,
            events.location,
            events.starts_at,
            events.ends_at,
            events.image_url,
            categories.name,
            categories.slug,
            users.name
        ORDER BY events.starts_at ASC
    """

    with get_connection() as connection:
        with connection.cursor() as cursor:
            cursor.execute(query, params)
            return cursor.fetchall()


@router.get("/events/{event_id}")
def get_event(event_id: int):
    with get_connection() as connection:
        with connection.cursor() as cursor:
            cursor.execute(
                """
                SELECT
                    events.id,
                    events.title,
                    events.description,
                    events.location,
                    events.starts_at,
                    events.ends_at,
                    events.image_url,
                    categories.name AS category,
                    categories.slug AS category_slug,
                    users.name AS author_name,
                    COUNT(event_registrations.id) AS participants_count
                FROM events
                JOIN categories ON categories.id = events.category_id
                JOIN users ON users.id = events.user_id
                LEFT JOIN event_registrations ON event_registrations.event_id = events.id
                WHERE events.id = %s
                GROUP BY
                    events.id,
                    events.title,
                    events.description,
                    events.location,
                    events.starts_at,
                    events.ends_at,
                    events.image_url,
                    categories.name,
                    categories.slug,
                    users.name
                LIMIT 1
                """,
                (event_id,),
            )

            event = cursor.fetchone()

            if not event:
                raise HTTPException(status_code=404, detail="Event not found")

            return event


@router.get("/events/{event_id}/participants")
def get_event_participants(event_id: int):
    with get_connection() as connection:
        with connection.cursor() as cursor:
            cursor.execute(
                """
                SELECT COUNT(*) AS participants_count
                FROM event_registrations
                WHERE event_id = %s
                """,
                (event_id,),
            )

            result = cursor.fetchone()

            return {
                "event_id": event_id,
                "participants_count": result["participants_count"],
            }
