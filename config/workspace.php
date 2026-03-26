<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Invitation Expiry
    |--------------------------------------------------------------------------
    |
    | Number of hours before a workspace invitation expires. Can be overridden
    | via the INVITATION_EXPIRY_HOURS environment variable.
    |
    */
    'invitation_expiry_hours' => (int) env('INVITATION_EXPIRY_HOURS', 48),
];
