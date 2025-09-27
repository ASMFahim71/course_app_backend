<?php

namespace App\Filament\Resources\Lessons\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use App\Models\Course;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('course_id')
                    ->required()
                    ->numeric(),
                Select::make('course_id')
                    ->label('Course')
                    ->options(Course::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required(),
                
                    FileUpload::make('thumbnail')
                    ->disk('public')
                    ->directory('video-thumbnails')
                    ->visibility('public')
                    ->required(fn(string $context): bool => $context === 'create')
                    ->dehydrated(true),
                   
                   
                    Repeater::make('video')
                    ->label('Videos')
                    ->schema([
                        TextInput::make('name')
                            ->label('Video Name')
                            ->placeholder('Input Name')
                            ->required()
                            ->columnSpan(1),
                        FileUpload::make('thumbnail')
                            ->label('Thumbnail')
                            ->placeholder('Select image')
                            ->disk('public')
                            ->directory('lesson-thumbnails')
                            ->visibility('public')
                            ->image()
                           
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->columnSpan(1),
                            FileUpload::make('video')
                            ->disk('public')
                            ->maxSize(51200000)
                            ->directory('lesson-videos')
                            ->default(null)
                            ->visibility('public')
                            ->acceptedFileTypes(['video/mp4', 'video/mov', 'video/avi', 'video/wmv', 'video/mp3', 'video/m4a', 'video/wma']),
        
                    ])
                    ->columns(3)
                    ->defaultItems(0)
                    ->addActionLabel('New')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Video')
                    ->columnSpanFull(),



                
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
