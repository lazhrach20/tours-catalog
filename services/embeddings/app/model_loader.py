from __future__ import annotations

import logging
import os
import random
import threading
from typing import Any

logger = logging.getLogger(__name__)

MODEL_NAME = os.getenv(
    "EMBEDDINGS_MODEL",
    "sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2",
)
EMBEDDING_DIM = 384

_model: Any | None = None
_model_error: str | None = None
_load_lock = threading.Lock()


def _mock_enabled() -> bool:
    return os.getenv("MOCK_EMBEDDINGS", "").strip().lower() in ("1", "true", "yes")


def cache_folder() -> str:
    return (
        os.getenv("TRANSFORMERS_CACHE")
        or os.getenv("HF_HOME")
        or "/var/cache/huggingface"
    )


def is_model_loaded() -> bool:
    if _mock_enabled():
        return True
    return _model is not None


def get_model_error() -> str | None:
    return _model_error


def _background_load() -> None:
    try:
        load_model()
        logger.info("Embeddings model ready (%d dimensions)", EMBEDDING_DIM)
    except Exception:  # noqa: BLE001
        logger.warning("Background model load failed; /embed will retry loading")


def warmup() -> None:
    logger.warning(
        "Embeddings model %s will load lazily (first run may take several minutes; cache: %s)",
        MODEL_NAME,
        cache_folder(),
    )
    if _mock_enabled():
        logger.warning("MOCK_EMBEDDINGS is enabled — /embed returns random %d-dim vectors", EMBEDDING_DIM)
        return
    threading.Thread(target=_background_load, name="embeddings-model-load", daemon=True).start()


def load_model() -> Any:
    global _model, _model_error

    if _mock_enabled():
        return None

    if _model is not None:
        return _model

    if _model_error is not None:
        raise RuntimeError(_model_error)

    with _load_lock:
        if _model is not None:
            return _model
        if _model_error is not None:
            raise RuntimeError(_model_error)

        try:
            from sentence_transformers import SentenceTransformer

            logger.info("Downloading/loading model %s", MODEL_NAME)
            _model = SentenceTransformer(MODEL_NAME, cache_folder=cache_folder())
            return _model
        except Exception as exc:  # noqa: BLE001
            _model_error = str(exc)
            logger.exception("Failed to load embeddings model")
            raise


def embed_text(text: str) -> list[float]:
    if _mock_enabled():
        return [random.uniform(-1.0, 1.0) for _ in range(EMBEDDING_DIM)]

    model = load_model()
    vector = model.encode(text, normalize_embeddings=True)
    values = vector.tolist()
    if len(values) != EMBEDDING_DIM:
        raise RuntimeError(f"Expected {EMBEDDING_DIM}-dim vector, got {len(values)}")
    return values
