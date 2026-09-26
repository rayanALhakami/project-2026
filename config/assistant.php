<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Assistant Timezone
    |--------------------------------------------------------------------------
    |
    | The timezone used to compute the current date and time injected into the
    | finance assistant's instructions. This is what lets the agent resolve
    | relative phrases such as "today", "yesterday", or "last week" correctly.
    |
    */

    'timezone' => env('ASSISTANT_TIMEZONE', 'Asia/Riyadh'),

];
