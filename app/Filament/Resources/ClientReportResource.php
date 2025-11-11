<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientReportResource\Pages;
use App\Models\ClientReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;

class ClientReportResource extends Resource
{
    protected static ?string $model = ClientReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Daftar Laporan Klien';

    protected static ?string $navigationGroup = 'Menu KPI';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('staff_id')
                    ->relationship(
                        name: 'staff',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query) {
                            $panel = Filament::getCurrentPanel();
                            if ($panel && $panel->getId() === 'staff') {
                                $userStaffId = Auth::user()?->staff_id;
                                if ($userStaffId) {
                                    $query->where('id', $userStaffId);
                                }
                            }
                        },
                    )
                    ->default(fn() => Auth::user()?->staff_id)
                    ->disabled(fn() => Filament::getCurrentPanel()?->getId() === 'staff')
                    ->dehydrated(true)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // Reset client_id when staff changes
                        $set('client_id', null);
                    }),
                Forms\Components\Select::make('client_id')
                    ->options(function () {
                        $user = Auth::user();

                        // Jika user adalah staff, filter client berdasarkan team
                        if ($user && $user->staff_id && $user->staff) {
                            // Ambil team yang dimiliki staff
                            $teamIds = $user->staff->team()->pluck('teams.id');

                            // Jika staff tidak punya team, tampilkan semua client
                            if ($teamIds->isEmpty()) {
                                return \App\Models\Client::pluck('company_name', 'id');
                            }

                            // Ambil client berdasarkan team
                            return \App\Models\Client::whereHas('team', function ($query) use ($teamIds) {
                                $query->whereIn('teams.id', $teamIds);
                            })->pluck('company_name', 'id');
                        }

                        // Jika admin atau tidak ada staff, tampilkan semua client
                        return \App\Models\Client::pluck('company_name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // Reset report_content when client changes
                        $set('report_content', null);
                    }),
                Forms\Components\DatePicker::make('report_date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        // Calculate score when report_date changes
                        $reportContent = $get('report_content');
                        if ($state && $reportContent) {
                            $score = self::calculateScore($reportContent, $state);
                            $set('score', $score);
                        }
                    })
                    ->afterStateHydrated(function ($state, Forms\Set $set, Forms\Get $get) {
                        // Calculate score when form is loaded with existing data
                        $reportContent = $get('report_content');
                        if ($state && $reportContent) {
                            $score = self::calculateScore($reportContent, $state);
                            $set('score', $score);
                        }
                    }),
                Forms\Components\TextInput::make('report_month')
                    ->type('month')
                    ->label('Report Month')
                    ->default(now()->format('Y-m'))
                    ->required(),
                Forms\Components\Select::make('report_content')
                    ->options(function (Forms\Get $get) {
                        $clientId = $get('client_id');

                        if (!$clientId) {
                            return [];
                        }

                        // Get the client and their reporting settings
                        $client = \App\Models\Client::find($clientId);

                        if (!$client) {
                            return [];
                        }

                        $reportingOptions = [];

                        // Check which reporting types are enabled for this client
                        if ($client->pph_25_reporting) {
                            $reportingOptions['pph25'] = 'PPH Pasal 25';
                        }

                        if ($client->pph_23_reporting) {
                            $reportingOptions['pph23'] = 'PPH Pasal 23';
                        }

                        if ($client->pph_21_reporting) {
                            $reportingOptions['pph21'] = 'PPH Pasal 21/Basil';
                        }

                        if ($client->pph_4_reporting) {
                            $reportingOptions['pph4'] = 'PPH Pasal 4';
                        }

                        if ($client->ppn_reporting) {
                            $reportingOptions['ppn'] = 'PPN';
                        }

                        if ($client->spt_reporting) {
                            $reportingOptions['spt'] = 'SPT';
                        }

                        return $reportingOptions;
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(fn(Forms\Get $get) => !$get('client_id'))
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        // Calculate score when report_content changes
                        $reportDate = $get('report_date');
                        if ($state && $reportDate) {
                            $score = self::calculateScore($state, $reportDate);
                            $set('score', $score);
                        }
                    })
                    ->afterStateHydrated(function ($state, Forms\Set $set, Forms\Get $get) {
                        // Calculate score when form is loaded with existing data
                        $reportDate = $get('report_date');
                        if ($state && $reportDate) {
                            $score = self::calculateScore($state, $reportDate);
                            $set('score', $score);
                        }
                    }),
                Forms\Components\TextInput::make('score')
                    ->label('Score')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Score akan dihitung otomatis berdasarkan jenis laporan dan tanggal pelaporan'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['client', 'staff']))
            ->columns([
                Tables\Columns\TextColumn::make('client.company_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('staff.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_date')
                    ->searchable()
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_month')
                    ->searchable()
                    ->date('M-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('report_content')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if ($state === 'pph25') {
                            return 'PPH Pasal 25';
                        } elseif ($state === 'pph21') {
                            return 'PPH Pasal 21/Basil';
                        } elseif ($state === 'ppn') {
                            return 'PPN';
                        } elseif ($state === 'spt') {
                            return 'SPT';
                        } elseif ($state === 'pph23') {
                            return 'PPH Pasal 23';
                        } elseif ($state === 'pph4') {
                            return 'PPH Pasal 4';
                        }
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('score')
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->icon(fn($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn($state) => $state ? 'success' : 'danger')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('verified_by')
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state ? (Staff::find($state)?->name ?? $state) : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('verified_at')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(function ($record): bool {
                        $panel = Filament::getCurrentPanel();
                        if ($panel && $panel->getId() === 'staff') {
                            return !$record?->is_verified;
                        }
                        return true;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(function ($record): bool {
                        $panel = Filament::getCurrentPanel();
                        if ($panel && $panel->getId() === 'staff') {
                            return !$record?->is_verified;
                        }
                        return true;
                    }),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(function ($record): bool {
                        // Only show to users who can verify or on admin panel
                        $can = \Illuminate\Support\Facades\Gate::allows('clientreport.verify');
                        $isAdminPanel = Filament::getCurrentPanel()?->getId() === 'admin';
                        return (bool) $record && ($can || $isAdminPanel);
                    })
                    ->action(function ($record) {
                        $record->update([
                            'is_verified' => true,
                            'verified_by' => \Illuminate\Support\Facades\Auth::user()?->staff_id,
                            'verified_at' => now(),
                        ]);
                    })
                    ->after(function () {
                        \Filament\Notifications\Notification::make()
                            ->title('Client report verified')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(function () {
                            $panel = Filament::getCurrentPanel();
                            if ($panel && $panel->getId() === 'staff') {
                                return false; // Hide bulk delete for staff
                            }
                            return true;
                        }),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageClientReports::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $panel = Filament::getCurrentPanel();
        if ($panel && $panel->getId() === 'staff') {
            $userStaffId = Auth::user()?->staff_id;
            if ($userStaffId) {
                $query->where('staff_id', $userStaffId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    /**
     * Calculate score based on report content and report date
     */
    public static function calculateScore(string $reportContent, string $reportDate): float
    {
        $day = (int) date('j', strtotime($reportDate));

        switch ($reportContent) {
            case 'pph25':
                // PPH 25: nilai 1 jika tanggal 1-15, 0 jika tidak
                return ($day >= 1 && $day <= 15) ? 1.0 : 0.0;

            case 'pph21':
                // PPH 21: nilai 2 jika tanggal 1-15, nilai 1 jika tanggal 16-20, 0 jika tidak
                if ($day >= 1 && $day <= 15) {
                    return 2.0;
                } elseif ($day >= 16 && $day <= 20) {
                    return 1.0;
                } else {
                    return 0.0;
                }

            case 'ppn':
                // PPN: nilai 2 jika tanggal 16-23, nilai 1 jika tanggal 24-31, 0 jika tidak
                if ($day >= 16 && $day <= 23) {
                    return 2.0;
                } elseif ($day >= 24 && $day <= 31) {
                    return 1.0;
                } else {
                    return 0.0;
                }

            case 'pph23':
            case 'pph4':
            case 'spt':
                // PPH 23, PPH 4, SPT: nilai default 1
                return 1.0;

            default:
                return 0.0;
        }
    }
}
