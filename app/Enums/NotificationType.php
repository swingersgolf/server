<?php

namespace App\Enums;

enum NotificationType: string
{
    // New notification types for the social networking app
    case GROUP_REQUEST = 'group_request'; // Notification for requesting to join a group
    case GROUP_ACCEPTED = 'group_accepted'; // Notification when a user is accepted into a group
    case GROUP_REJECTED = 'group_rejected'; // Notification when a user is rejected from a group
}
