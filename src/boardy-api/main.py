import asyncio

from fastapi import FastAPI

from routers.events import router as events_router
from routers.ws import redis_listener, router as websocket_router

app = FastAPI(
    title="EventBoard API",
    version="1.0.0",
)


@app.get("/health")
def health():
    return {"ok": True}


@app.get("/api/health")
def api_health():
    return {"ok": True}


app.include_router(events_router)
app.include_router(websocket_router)


@app.on_event("startup")
async def startup_event():
    asyncio.create_task(redis_listener())
