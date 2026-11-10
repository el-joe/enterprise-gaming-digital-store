<?php

namespace App\Domains\Catalog\Enums;

enum ProductRelationType: string
{
    case Related = 'related';
    case CrossSell = 'cross_sell';
    case Upsell = 'upsell';
}
