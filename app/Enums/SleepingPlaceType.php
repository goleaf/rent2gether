<?php

namespace App\Enums;

enum SleepingPlaceType: string
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
}
