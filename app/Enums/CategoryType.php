<?php

namespace App\Enums;

enum CategoryType: string
{
    case PriceTimesQuantity = 'harga_x_qty';
    case Flat = 'flat';
    case Custom = 'bebas';
    case Yearly = 'tahunan';
}
