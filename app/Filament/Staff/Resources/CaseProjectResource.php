<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\CaseProjectResource\Pages;
use App\Filament\Staff\Resources\CaseProjectResource\RelationManagers;
use App\Models\CaseProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CaseProjectResource extends Resource
{
    protected static ?string $model = CaseProject::class;

    protected static ?string $navigationLabel = 'Daftar Proyek Kasus';

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationGroup = 'Menu KPI';

    protected static ?string $modelLabel = 'Kasus Proyek';

    protected static ?string $pluralModelLabel = 'Kasus Proyek';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('case_date')
                    ->label('Tanggal Kasus')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'closed' => 'Closed',
                    ])
                    ->required(),
                Forms\Components\Hidden::make('staff_id')
                    ->label('Staff ID')
                    ->default(function () {
                        $user = Auth::user();
                        // Di panel staff, default selalu ke staff_id user yang login
                        return $user ? $user->staff_id : null;
                    })
                    ->disabled()
                    ->required(),
                Forms\Components\TextInput::make('staff_name')
                    ->label('Nama Staff')
                    ->default(function () {
                        $user = Auth::user();
                        // Tampilkan nama staff untuk referensi
                        return $user && $user->staff ? $user->staff->name : ($user ? $user->name : null);
                    })
                    ->disabled()
                    ->dehydrated(false), // Tidak disimpan ke database
                Forms\Components\Select::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'company_name')
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\Textarea::make('link_dokumen')
                    ->label('Link Dokumen')
                    ->columnSpanFull()
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.name')
                    ->label('Staff')
                    ->searchable()
                    ->visible(function () {
                        $user = Auth::user();
                        // Tampilkan kolom staff hanya untuk admin
                        return $user && $user->hasRole('admin');
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.company_name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('case_date')
                    ->label('Tanggal Kasus')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'in_progress',
                        'success' => 'closed',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'closed' => 'Closed',
                        default => $state
                    }),
                Tables\Columns\TextColumn::make('link_dokumen')
                    ->label('Link Dokumen')
                    ->limit(30)
                    ->copyable()
                    ->copyableState(fn(CaseProject $record): string => $record->link_dokumen ?? '')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('staff_id')
                    ->label('Staff')
                    ->relationship('staff', 'name')
                    ->preload()
                    ->searchable()
                    ->visible(function () {
                        $user = Auth::user();
                        // Filter staff hanya untuk admin
                        return $user && $user->hasRole('admin');
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'closed' => 'Closed',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('case_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('case_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('case_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (CaseProject $record, array $data): array {
                        // Di panel staff, staff_id default ke user yang login
                        $user = Auth::user();
                        if ($user && $user->staff_id && !isset($data['staff_id'])) {
                            $data['staff_id'] = $user->staff_id;
                        }
                        // Staff biasa tidak bisa mengubah staff_id, admin bisa
                        if ($user && $user->hasRole('staff') && !$user->hasRole('admin')) {
                            $data['staff_id'] = $user->staff_id;
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('case_date', 'desc');
    }

    // Method untuk filter case projects 
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $user = Auth::user();

        // Saat ini di-comment karena admin juga perlu melihat semua case project
        // Jika ingin staff hanya melihat case milik sendiri, uncomment baris ini:
        // if ($user && $user->hasRole('staff') && !$user->hasRole('admin') && $user->staff_id) {
        //     $query->where('staff_id', $user->staff_id);
        // }

        // Sementara filter berdasarkan user staff_id untuk semua user (admin/staff)
        if ($user && $user->staff_id) {
            $query->where('staff_id', $user->staff_id);
        }

        return $query;
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
            'index' => Pages\ManageCaseProjects::route('/'),
        ];
    }
}
