<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class SalesOrderDetailWidget extends Widget
{
    use HasWidgetShield;
    protected static string $view = 'filament.widgets.sales-order-detail-widget';
}
