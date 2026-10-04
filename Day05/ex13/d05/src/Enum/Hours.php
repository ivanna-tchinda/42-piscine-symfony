<?php

namespace App\Enum;

enum Hours: int
{
    case FULL_TIME = 8;
    case PART_TIME = 6;
    case HALF_TIME = 4;
}