<?php

return [
    'url' => rtrim(env('EMBEDDINGS_URL', 'http://embeddings:8000'), '/'),
    'timeout' => (float) env('EMBEDDINGS_TIMEOUT', 3),
];
