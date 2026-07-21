<?php

namespace App\Enums;

enum TipeKategori: string
{
    case HargaXQty = 'harga_x_qty';
    case Flat = 'flat';
    case Bebas = 'bebas';
    case Tahunan = 'tahunan';
}
