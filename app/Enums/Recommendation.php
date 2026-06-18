<?php

namespace App\Enums;

enum Recommendation: string
{
    case Interview = 'interview';
    case Pending = 'pending';
    case Reject = 'reject';
}
