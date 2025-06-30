<?php

namespace App\View\Components\Chart;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CustomerRecommendationChart extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public array $labels = [], public array $data = [])
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.chart.customer-recommendation-chart');
    }
}
