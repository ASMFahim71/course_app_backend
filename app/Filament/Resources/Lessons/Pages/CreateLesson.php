<?php

namespace App\Filament\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\LessonResource;
use Filament\Resources\Pages\CreateRecord;
use App\Jobs\ConvertVideoToHLS;
class CreateLesson extends CreateRecord
{
    protected static string $resource = LessonResource::class;
    // protected function afterCreate(): void
    // {
    //     $videoPath = $this->record->video; // e.g. 'videos/lesson1.mp4'

    //     if ($videoPath) {
    //         ConvertVideoToHLS::dispatch($videoPath);
    //     }
    // }
}
