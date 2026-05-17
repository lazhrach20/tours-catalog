from __future__ import annotations

import logging
import os
from contextlib import asynccontextmanager
from typing import Any

from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field

from app import model_loader

logger = logging.getLogger("embeddings")
_log_level = os.getenv("LOG_LEVEL", "INFO").upper()
logging.basicConfig(level=_log_level)


class EmbedRequest(BaseModel):
    text: str = Field(max_length=8000)


class EmbedResponse(BaseModel):
    vector: list[float]


@asynccontextmanager
async def lifespan(_app: FastAPI):
    model_loader.warmup()
    yield


app = FastAPI(title="Tours Catalog Embeddings", version="0.1.0", lifespan=lifespan)


@app.get("/health")
def health() -> dict[str, Any]:
    loaded = model_loader.is_model_loaded()
    payload: dict[str, Any] = {
        "status": "ok",
        "model_loaded": loaded,
    }
    error = model_loader.get_model_error()
    if error:
        payload["model_error"] = error
    return payload


@app.post("/embed", response_model=EmbedResponse)
def embed(request: EmbedRequest) -> EmbedResponse:
    text = request.text.strip()
    if not text:
        raise HTTPException(status_code=400, detail="text must not be empty")

    try:
        vector = model_loader.embed_text(text)
    except Exception as exc:  # noqa: BLE001
        raise HTTPException(status_code=503, detail=str(exc)) from exc

    return EmbedResponse(vector=vector)
