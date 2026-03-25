<?php

namespace App\Filament\Resources;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

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
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (TaskPriority $state): string => match ($state) {
                        TaskPriority::High => 'danger',
                        TaskPriority::Medium => 'warning',
                        TaskPriority::Low => 'success',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (TaskStatus $state): string => match ($state) {
                        TaskStatus::Pending => 'warning',
                        TaskStatus::InProgress => 'info',
                        TaskStatus::Completed => 'success',
                    })
                    ->sortable(),
                TextColumn::make('due_date')->date()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('priority')->options(TaskPriority::class),
                Tables\Filters\SelectFilter::make('status')->options(TaskStatus::class),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->defaultSort('due_date', 'asc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
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
