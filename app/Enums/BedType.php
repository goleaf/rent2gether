<?php

namespace App\Enums;

enum BedType: string
{
    case Single = 'single';
    case Double = 'double';
    case BunkTop = 'bunk_top';
    case BunkBottom = 'bunk_bottom';
    case Sofa = 'sofa';
    case SofaBed = 'sofa_bed';
    case Mattress = 'mattress';
    case FoldOut = 'fold_out';
    case Capsule = 'capsule';

    public function label(): string
    {
        return match ($this) {
            self::Single => 'Single bed',
            self::Double => 'Double bed',
            self::BunkTop => 'Bunk (top)',
            self::BunkBottom => 'Bunk (bottom)',
            self::Sofa => 'Sofa',
            self::SofaBed => 'Sofa bed',
            self::Mattress => 'Mattress',
            self::FoldOut => 'Fold-out bed',
            self::Capsule => 'Capsule',
        };
    }

    public function isBunk(): bool
    {
        return in_array($this, [self::BunkTop, self::BunkBottom]);
    }
}
