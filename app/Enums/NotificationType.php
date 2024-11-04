<?php

namespace App\Enums;

enum NotificationType: string
{
    // New notification types for the social networking app
    case ROUND_REQUEST = 'round_request'; // Notification for requesting to join a round
    case ROUND_ACCEPTED = 'round_accepted'; // Notification when a user is accepted into a round
    case ROUND_REJECTED = 'round_rejected'; // Notification when a user is rejected from a round
}
