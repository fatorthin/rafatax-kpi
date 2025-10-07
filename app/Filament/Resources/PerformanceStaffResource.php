<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Staff;
use App\Models\Period;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PerformanceStaff;
use Filament\Resources\Resource;
use App\Models\PeriodPerformanceReview;
use Illuminate\Database\Eloquent\Builder;
use App\Models\PerformanceReviewReference;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PerformanceStaffResource\Pages;

class PerformanceStaffResource extends Resource
{
    protected static ?string $model = PerformanceStaff::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('performance_reference_id')
                    ->options(PerformanceReviewReference::all()->pluck('name', 'id'))
                    ->required(),
                Forms\Components\Select::make('staff_id')
                    ->options(Staff::all()->pluck('name', 'id'))
                    ->required(),   
                Forms\Components\TextInput::make('supervisor_score')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('performanceReference.period.name')
                    ->label('Period')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('staff.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('performanceReference.name')
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('self_score')
                    ->searchable()
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('supervisor_score')
                    ->searchable()
                    ->sortable()
                    ->alignEnd(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('staff_id')
                    ->options(Staff::all()->pluck('name', 'id'))
                    ->label('Staff'),
                Tables\Filters\SelectFilter::make('performance_reference_id')
                    ->relationship('performanceReference.period', 'name')
                    ->label('Period'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPerformanceStaff::route('/'),
            'create' => Pages\CreatePerformanceStaff::route('/create'),
            'view' => Pages\ViewPerformanceStaff::route('/{record}'),
            'edit' => Pages\EditPerformanceStaff::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
