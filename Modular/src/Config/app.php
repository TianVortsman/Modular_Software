<?php
return [
    'APP_ENV' => getenv('APP_ENV') ?: 'Production',
    'AI_ENDPOINT' => getenv('AI_ENDPOINT') ?: 'http://host.docker.internal:1234/v1/chat/completions',
]; 