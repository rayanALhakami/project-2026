<?php

namespace App\Enums;

enum PlaceCategory: string
{
    case Landmark = 'landmark';
    case Museum = 'museum';
    case Heritage = 'heritage';
    case Restaurant = 'restaurant';
    case Park = 'park';
    case Nature = 'nature';
    case Beach = 'beach';
    case Shopping = 'shopping';
}
