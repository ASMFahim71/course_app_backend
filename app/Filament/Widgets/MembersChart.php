<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Member;
use Carbon\Carbon;

class MembersChart extends ChartWidget
{
    protected ?string $heading = 'Students Overview';

    protected function getData(): array
    {


        $membersPerMonth = Member::query()
            ->whereYear('created_at', Carbon::now()->year)
            ->where('role', 'student')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');


        $labels = collect(range(1, 12))
            ->map(fn($m) => Carbon::create()->month($m)->format('M'))
            ->toArray();

        // Fill in missing months with 0
        $data = collect(range(1, 12))
            ->map(fn($m) => $membersPerMonth->get($m, 0))
            ->toArray();

        return [

            'datasets' => [
                [
                    'label' => 'Students Joined',
                    'data' => $data,
                    'borderColor' => '#4F46E5',
                    'backgroundColor' => 'rgba(79,70,229,0.3)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,

        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
