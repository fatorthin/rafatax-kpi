<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffResource\Pages;
use App\Filament\Resources\StaffResource\RelationManagers;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use League\CommonMark\Extension\Table\TableSectionRenderer;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('birth_place')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('birth_date')
                    ->required(),
                Forms\Components\TextInput::make('address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('no_ktp')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('no_spk')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                Forms\Components\Select::make('jenjang')
                    ->options([
                        'SMA' => 'SMA',
                        'D-3' => 'D-3',
                        'D-4' => 'D-4',
                        'S-1' => 'S-1',
                        'S-2' => 'S-2',
                        'S-3' => 'S-3',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('jurusan')
                    ->required(),
                Forms\Components\TextInput::make('university')
                    ->required(),
                Forms\Components\TextInput::make('no_ijazah')
                    ->required(),
                Forms\Components\DatePicker::make('tmt_training')
                    ->required(),
                Forms\Components\TextInput::make('periode')
                    ->required()
                    ->maxLength(20),
                Forms\Components\DatePicker::make('selesai_training')
                    ->required(),
                Forms\Components\Select::make('position_reference_id')
                    ->relationship('positionReference', 'name')
                    ->required(),
                Forms\Components\Select::make('department_reference_id')
                    ->relationship('departmentReference', 'name')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->onColor('success')
                    ->offColor('danger')
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-mark'),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'company_name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('team_id')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ttl')
                    ->label('TTL')
                    ->searchable(['birth_place', 'birth_date'])
                    ->getStateUsing(function (Staff $record) {
                        return $record->birth_place . ', ' . date('d-m-Y', strtotime($record->birth_date));
                    }),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->wrap()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('positionReference.name')
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departmentReference.name')
                    ->searchable()
                    ->wrap()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->icon(fn(string $state): string => match ($state) {
                        '1' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-x-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        '1' => 'success',
                        default => 'danger',
                    })

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('is_active')
                    ->label('Staff Aktif')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true))
                    ->default()
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                // Tables\Actions\ForceDeleteAction::make(),
                // Tables\Actions\RestoreAction::make(),
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
            'index' => Pages\ManageStaff::route('/'),
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
