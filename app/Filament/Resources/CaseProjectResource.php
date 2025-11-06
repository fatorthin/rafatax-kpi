<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CaseProjectResource\Pages;
use App\Filament\Resources\CaseProjectResource\RelationManagers;
use App\Models\CaseProject;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CaseProjectResource extends Resource
{
    protected static ?string $model = CaseProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('case_date')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'closed' => 'Closed',
                    ])
                    ->required(),
                Forms\Components\Select::make('staff_id')
                    ->relationship('staff', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'company_name')
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\Textarea::make('link_dokumen')
                    ->label('Link Dokumen')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.name')->label('Staff'),
                Tables\Columns\TextColumn::make('description')->limit(50),
                Tables\Columns\TextColumn::make('client.company_name')->label('Client'),
                Tables\Columns\TextColumn::make('case_date')->date('d-m-Y'),
                Tables\Columns\TextColumn::make('link_dokumen')->label('Link Dokumen')->limit(50)->copyable()
                    ->copyableState(fn(CaseProject $record): string => "{$record->link_dokumen}"),
                Tables\Columns\TextColumn::make('status')->formatStateUsing(fn($state) => $state === 'open' ? 'Open' : ($state === 'in_progress' ? 'In Progress' : 'Closed')),

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
            'index' => Pages\ManageCaseProjects::route('/'),
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
