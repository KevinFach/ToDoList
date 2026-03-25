<?php

namespace App\Filament\Resources;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\Textarea::make('description')->maxLength(1000),
                Forms\Components\Select::make('priority')
                    ->options(array_combine(TaskPriority::values(), TaskPriority::values()))
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(array_combine(TaskStatus::values(), TaskStatus::values()))
                    ->required(),
                Forms\Components\DatePicker::make('due_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                BadgeColumn::make('priority')->colors(['danger' => 'High', 'warning' => 'Medium', 'success' => 'Low']),
                BadgeColumn::make('status')->colors(['warning' => 'pending', 'primary' => 'in_progress', 'success' => 'completed']),
                TextColumn::make('due_date')->date()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('priority')->options(array_combine(TaskPriority::values(), TaskPriority::values())),
                SelectFilter::make('status')->options(array_combine(TaskStatus::values(), TaskStatus::values())),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->defaultSort('due_date', 'asc')
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn (Task $record): bool => $record->status !== TaskStatus::Completed),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
