<?php

namespace App\Entity\Enum;

enum WinkType: int
{
    case WINK_TYPE_MEDIUM = 0;
    case TASK_TYPE_HIGH = 1;
    case TASK_TYPE_ASAP = 2;
}
