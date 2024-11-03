<?php

namespace App\Enums;

enum NotificationType: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case SUCCESS = 'success';
    case ERROR = 'error';
}
