import asyncio
import json
import os
from typing import Set

import redis.asyncio as redis
from fastapi import APIRouter, WebSocket, WebSocketDisconnect

router = APIRouter()

REDIS_URL = os.getenv("REDIS_URL", "redis://redis:6379")
REDIS_CHANNEL = "event_updates"


class ConnectionManager:
    def __init__(self) -> None:
        self.active_connections: Set[WebSocket] = set()

    async def connect(self, websocket: WebSocket) -> None:
        await websocket.accept()
        self.active_connections.add(websocket)
        print(f"WebSocket connected. Total: {len(self.active_connections)}", flush=True)

    def disconnect(self, websocket: WebSocket) -> None:
        self.active_connections.discard(websocket)
        print(f"WebSocket disconnected. Total: {len(self.active_connections)}", flush=True)

    async def broadcast(self, message: dict) -> None:
        disconnected = []

        for connection in list(self.active_connections):
            try:
                await connection.send_json(message)
            except Exception:
                disconnected.append(connection)

        for connection in disconnected:
            self.disconnect(connection)


manager = ConnectionManager()


@router.websocket("/ws")
async def websocket_endpoint(websocket: WebSocket):
    await manager.connect(websocket)

    try:
        while True:
            await websocket.receive_text()
    except WebSocketDisconnect:
        manager.disconnect(websocket)
    except Exception:
        manager.disconnect(websocket)


async def redis_listener():
    while True:
        client = None
        pubsub = None

        try:
            client = redis.from_url(REDIS_URL, decode_responses=True)
            pubsub = client.pubsub()

            await pubsub.subscribe(REDIS_CHANNEL)
            print(f"Subscribed to Redis channel: {REDIS_CHANNEL}", flush=True)

            while True:
                message = await pubsub.get_message(
                    ignore_subscribe_messages=True,
                    timeout=1.0,
                )

                if message is None:
                    await asyncio.sleep(0.05)
                    continue

                raw_data = message.get("data")

                try:
                    payload = json.loads(raw_data)
                except Exception:
                    payload = {
                        "type": "raw_message",
                        "data": raw_data,
                    }

                print(f"Redis event received: {payload}", flush=True)

                await manager.broadcast(payload)

        except asyncio.CancelledError:
            raise

        except Exception as exception:
            print(f"Redis listener error: {exception}", flush=True)
            await asyncio.sleep(3)

        finally:
            if pubsub is not None:
                try:
                    await pubsub.unsubscribe(REDIS_CHANNEL)
                    await pubsub.close()
                except Exception:
                    pass

            if client is not None:
                try:
                    await client.close()
                except Exception:
                    pass
