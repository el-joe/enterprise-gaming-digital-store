<?php

namespace App\Domains\Orders\Enums;

enum RiskAssessmentDecision: string
{
    case Pass = 'pass';
    case Review = 'review';
    case Block = 'block';
}
