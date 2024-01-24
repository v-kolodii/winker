<?php

namespace App\Entity\Enum;

enum Status: int
{
    case NEW = 0;
    case IN_PROGRESS = 1;
    case FINISHED = 2;
}
