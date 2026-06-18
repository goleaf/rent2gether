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
            self::Single => __('app.bed_type.single'),
            self::Double => __('app.bed_type.double'),
            self::BunkTop => __('app.bed_type.bunk_top'),
            self::BunkBottom => __('app.bed_type.bunk_bottom'),
            self::Sofa => __('app.bed_type.sofa'),
            self::SofaBed => __('app.bed_type.sofa_bed'),
            self::Mattress => __('app.bed_type.mattress'),
            self::FoldOut => __('app.bed_type.fold_out'),
            self::Capsule => __('app.bed_type.capsule'),
        };
    }

    public function isBunk(): bool
    {
        return in_array($this, [self::BunkTop, self::BunkBottom]);
    }
}
