<?php

namespace App\Enums;

enum CategoryType: string
{
    case PriceTimesQuantity = 'harga_x_qty';
    case Flat = 'flat';
    case Custom = 'bebas';
    case Yearly = 'tahunan';

    public function usesQuantity(): bool
    {
        return in_array($this, [self::PriceTimesQuantity, self::Yearly], true);
    }

    public function needsPrice(): bool
    {
        return $this->usesQuantity();
    }

    public static function manageableValues(): array
    {
        return [
            self::PriceTimesQuantity->value,
            self::Yearly->value,
            self::Custom->value,
        ];
    }

    public static function quantityValues(): array
    {
        return [
            self::PriceTimesQuantity->value,
            self::Yearly->value,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::PriceTimesQuantity => 'Harga × Jumlah (tiket, parkir, sewa per item)',
            self::Yearly => 'Tahunan (sewa kios, kontrak per tahun)',
            self::Flat => 'Tarif Flat',
            self::Custom => 'Bebas (nominal diisi manual saat transaksi)',
        };
    }
}
