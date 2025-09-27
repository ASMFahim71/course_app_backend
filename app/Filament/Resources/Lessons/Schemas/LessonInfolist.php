<?php

namespace App\Filament\Resources\Lessons\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use App\Models\Course;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
class LessonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('course_id')
                    ->numeric(),
                    TextEntry::make('course.name')
                    ->label('Course'),
                TextEntry::make('name'),
               
                ImageEntry::make('thumbnail')
                ->disk('public')
                ->imageHeight(100),

                RepeatableEntry::make('video')
                    ->label('Videos')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Video Name')
                            ->columnSpan(1),
                        ImageEntry::make('thumbnail')
                            ->label('Thumbnail')
                            ->disk('public')
                            ->imageHeight(80)
                            ->columnSpan(1),
                            TextEntry::make('video')
                            ->label('url')
                            ->html()
                            ->formatStateUsing(fn($state) => $state
                                ? "<video width='320' height='200' controls>
                                       <source src='" . asset('storage/' . $state) . "' type='video/mp4'>
                                       Your browser does not support the video tag.
                                   </video>"
                                : 'No video available'),
        
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
