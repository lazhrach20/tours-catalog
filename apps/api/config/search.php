<?php

return [
    'top_k' => (int) env('SEARCH_TOP_K', 20),
    'min_score' => (float) env('SEARCH_MIN_SCORE', 0.35),
];
