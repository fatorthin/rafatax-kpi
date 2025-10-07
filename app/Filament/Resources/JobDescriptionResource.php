<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobDescriptionResource\Pages;
use App\Filament\Resources\JobDescriptionResource\RelationManagers;
use App\Models\JobDescription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobDescriptionResource extends Resource
{
    protected static ?string $model = JobDescription::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('position_id')
                    ->relationship('position', 'name')
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn ($record) => ucfirst($record->name))
                    ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? '')),
                Forms\Components\Textarea::make('job_description')
                    ->required()
                    ->maxLength(255),   
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('position.name')
                    ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? '')),
                Tables\Columns\TextColumn::make('job_description')
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageJobDescriptions::route('/'),
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
