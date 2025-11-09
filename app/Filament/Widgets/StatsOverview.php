<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Member;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Order;
use Illuminate\Support\Number;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalStudents = Member::where('role', 'student')->count();
        $totalTeachers = Member::where('role', 'teacher')->count();
        $totalCourses = Course::count();
        $totalLessons = Lesson::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_amount');
        
        // Calculate new members this month
        $newStudentsThisMonth = Member::where('role', 'student')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $newCoursesThisMonth = Course::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $newTeacherThisMonth=Member::where('role','teacher')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();

     
        $chartData = [];
        for ($i = 15; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $chartData[] = Member::where('role', 'student')
                ->whereDate('created_at', $date)
                ->count();
        }

        $teacherChartData = [];
        for ($i = 15; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $teacherChartData[] = Member::where('role', 'teacher')
                ->whereDate('created_at', $date)
                ->count();
        }


        $courseChartData = [];
        for($i=15;$i>=0;$i--){
            $date = now()->subDays($i)->startOfDay();
            $courseChartData[] = Course::whereDate('created_at', $date)
                ->count();
        }

        $orderChartData = [];
        for($i=15;$i>=0;$i--){
            $date = now()->subDays($i)->startOfDay();
            $orderChartData[] = Order::whereDate('created_at', $date)
                ->count();
        }
        $lessonChartData = [];
        for($i=15;$i>=0;$i--){
            $date = now()->subDays($i)->startOfDay();
            $lessonChartData[] = Lesson::whereDate('created_at', $date)
                ->count();
        }
        $revenueChartData = [];
        for($i=15;$i>=0;$i--){
            $date = now()->subDays($i)->startOfDay();
            $revenueChartData[] = Order::whereDate('created_at', $date)
                ->sum('total_amount');
        }

        return [
            Stat::make('Total Students', Number::format($totalStudents))
                ->description($newStudentsThisMonth . ' new this month')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->icon('heroicon-o-academic-cap')
                ->chart($chartData),
            
            Stat::make('Total Teachers', Number::format($totalTeachers))
                ->description($newTeacherThisMonth . ' new this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->icon('heroicon-o-user-circle')
                ->chart($teacherChartData),
            
            Stat::make('Total Courses', Number::format($totalCourses))
                ->description($newCoursesThisMonth . ' added this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-book-open')
                ->chart($courseChartData),
            
            Stat::make('Total Lessons', Number::format($totalLessons))
                ->description('Across all courses')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning')
                ->icon('heroicon-o-video-camera')
                ->chart($lessonChartData),
            
            Stat::make('Total Orders', Number::format($totalOrders))
                ->description('Course enrollments')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('danger')
                ->icon('heroicon-o-shopping-bag')
                ->chart($orderChartData),

            Stat::make('Total Revenue', Number::format($totalRevenue))
                ->description('Total revenue from all courses')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->icon('heroicon-o-currency-dollar')
                ->chart($revenueChartData),

        ];
    }
}
