<?php

namespace App\Enums;

enum TripItemType: string
{
    case Activity = 'activity';
    case Meal = 'meal';
    case Transport = 'transport';
    case Stay = 'stay';
    case Note = 'note';
}
