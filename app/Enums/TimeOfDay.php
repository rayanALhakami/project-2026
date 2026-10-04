<?php

namespace App\Enums;

enum TimeOfDay: string
{
    case Dawn = 'dawn';
    case Morning = 'morning';
    case Noon = 'noon';
    case Afternoon = 'afternoon';
    case Evening = 'evening';
    case Sunset = 'sunset';
    case Night = 'night';
}
