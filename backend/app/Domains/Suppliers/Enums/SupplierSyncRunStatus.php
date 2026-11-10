<?php

namespace App\Domains\Suppliers\Enums;

enum SupplierSyncRunStatus: string
{
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
}
