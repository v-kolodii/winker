<?php

namespace App\Entity\Enum;

enum TaskType: int
{
    case TASK_TYPE_TYPICAL = 0;
    case TASK_TYPE_REGULAR = 1;
}
