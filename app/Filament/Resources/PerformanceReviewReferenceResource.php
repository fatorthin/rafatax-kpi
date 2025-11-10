<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\PeriodPerformanceReview;
use Illuminate\Database\Eloquent\Builder;
use App\Models\PerformanceReviewReference;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PerformanceReviewReferenceResource\Pages;
use App\Filament\Resources\PerformanceReviewReferenceResource\RelationManagers;

class PerformanceReviewReferenceResource extends Resource
{
    protected static ?string $model = PerformanceReviewReference::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Daftar Referensi Penilaian Kinerja';

    protected static ?string $navigationGroup = 'Referensi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'Kompetensi Dasar' => 'Kompetensi Dasar',
                        'Kompetensi Teknis' => 'Kompetensi Teknis',
                    ])
                    ->required(),
                Forms\Components\Select::make('group')
                    ->options([
                        'Rispek' => 'Rispek',
                        'Antusias' => 'Antusias',
                        'Fatanah' => 'Fatanah',
                        'Amanah' => 'Amanah',
                        'Aspek Tanggung Jawab sesuai Uraian Tugas' => 'Aspek Tanggung Jawab sesuai Uraian Tugas',
                        'Pendidikan' => 'Pendidikan',
                        'Pengalaman Kerja' => 'Pengalaman Kerja',

                    ])
                    ->required(),
                Forms\Components\Select::make('period_id')
                    ->options(PeriodPerformanceReview::all()->pluck('name', 'id'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('group')
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('period.name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'Kompetensi Dasar' => 'Kompetensi Dasar',
                        'Kompetensi Teknis' => 'Kompetensi Teknis',
                    ]),
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'Rispek' => 'Rispek',
                        'Antusias' => 'Antusias',
                        'Fatanah' => 'Fatanah',
                        'Amanah' => 'Amanah',
                    ]),
                Tables\Filters\SelectFilter::make('period')
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
            'index' => Pages\ListPerformanceReviewReferences::route('/'),
            'create' => Pages\CreatePerformanceReviewReference::route('/create'),
            'view' => Pages\ViewPerformanceReviewReference::route('/{record}'),
            'edit' => Pages\EditPerformanceReviewReference::route('/{record}/edit'),
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
