<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Kode')
                    ->required(),

                Forms\Components\TextInput::make('company_name')
                    ->label('Nama Perusahaan')
                    ->required(),

                Forms\Components\TextInput::make('address')
                    ->label('Alamat')
                    ->required(),

                Forms\Components\TextInput::make('phone')
                    ->label('Telepon')
                    ->required(),

                Forms\Components\TextInput::make('owner_name')
                    ->label('Nama Pemilik')
                    ->required(),

                Forms\Components\TextInput::make('owner_role')
                    ->label('Jabatan Pemilik')
                    ->required(),

                Forms\Components\TextInput::make('contact_person')
                    ->label('Kontak Person')
                    ->required(),

                Forms\Components\TextInput::make('npwp')
                    ->label('NPWP')
                    ->required(),

                Forms\Components\TextInput::make('jenis_wp')
                    ->label('Jenis WP')
                    ->required(),

                Forms\Components\TextInput::make('grade')
                    ->label('Grade')
                    ->required(),

                Forms\Components\TextInput::make('type')
                    ->label('Tipe')
                    ->required(),

                Forms\Components\Toggle::make('pph_25_reporting')
                    ->label('PPh 25 Reporting')
                    ->required(),

                Forms\Components\Toggle::make('pph_23_reporting')
                    ->label('PPh 23 Reporting')
                    ->required(),

                Forms\Components\Toggle::make('pph_21_reporting')
                    ->label('PPh 21 Reporting')
                    ->required(),

                Forms\Components\Toggle::make('pph_4_reporting')
                    ->label('PPh 4(2) Reporting')
                    ->required(),

                Forms\Components\Toggle::make('ppn_reporting')
                    ->label('PPN Reporting')
                    ->required(),

                Forms\Components\Toggle::make('spt_reporting')
                    ->label('SPT Reporting')
                    ->required(),

                Forms\Components\Toggle::make('status')
                    ->label('Status')
                    ->required(),

                Forms\Components\Select::make('staff_id')
                    ->label('Staff')
                    ->relationship('staff', 'name'),

                Forms\Components\Select::make('team_id')
                    ->label('Team')
                    ->relationship('team', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('owner_role')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('npwp')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('jenis_wp')
                    ->label('Jenis WP')
                    ->formatStateUsing(fn($state) => $state === 'op' ? 'Perseorangan' : 'Badan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('grade')
                    ->searchable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => $state === 'pt' ? 'PT' : 'KKP'),
                Tables\Columns\IconColumn::make('pph_25_reporting')
                    ->label('PPh 25 Reporting')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('pph_23_reporting')
                    ->label('PPh 23 Reporting')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('pph_21_reporting')
                    ->label('PPh 21 Reporting')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('pph_4_reporting')
                    ->label('PPh 4 Reporting')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('ppn_reporting')
                    ->label('PPN Reporting')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('spt_reporting')
                    ->label('SPT Tahunan Reporting')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->badge()
                    ->color(fn($state) => $state === 'active' ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('staff.name')
                    ->label('Staff')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
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
