<?php

declare(strict_types=1);

namespace App\Enums;

enum Status: string
{
    case COMPLETED = 'COMPLETED';
    case NOT_COMPLETED = 'NOT_COMPLETED';
    case ACCEPT = 'ACCEPTED';
    case NOT_ACCEPT = 'NOT_ACCEPTED';

}
