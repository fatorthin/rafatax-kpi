<?php

namespace App\Filament\Resources\LogBookResource\Pages;

use Filament\Forms;
use App\Models\Staff;
use Filament\Actions;
use App\Models\Client;
use App\Models\LogBook;
use App\Models\ClientReport;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LogBookResource;
use Filament\Resources\Pages\ManageRecords;

class ManageLogBooks extends ManageRecords
{
    protected static string $resource = LogBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('job_description_stats')
                ->label('Job Description Statistics')
                ->icon('heroicon-o-chart-bar')
                ->url(fn(): string => static::$resource::getUrl('job-description-stats'))
                ->color('info'),
            Actions\CreateAction::make()->label('Tambah Log Book')
                ->modalHeading('Tambah Log Book')
                ->form([
                    Forms\Components\DatePicker::make('date')
                        ->required()
                        ->default(now()->toDateString()),
                    Forms\Components\Select::make('job_description_id')
                        ->relationship('jobDescription', 'description')
                        ->required(),
                    Forms\Components\TextInput::make('count_task')
                        ->label('Jumlah Tugas')
                        ->numeric()
                        ->required(),
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi Pekerjaan')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    // create log book
                    LogBook::create([
                        'staff_id' => Auth::user()->staff_id,
                        'job_description_id' => $data['job_description_id'],
                        'description' => $data['description'],
                        'count_task' => $data['count_task'],
                        'date' => $data['date'],
                        'status' => 'pending',
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Log Book created')
                        ->send();
                }),

            Actions\Action::make('add_client_report')
                ->label('Tambah Laporan Client')
                ->modalHeading('Tambah Laporan Client')
                ->form([
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
                ])
                ->action(function (array $data): void {
                    // create client report
                    $report = ClientReport::create([
                        'staff_id' => $data['staff_id'],
                        'client_id' => $data['client_id'],
                        'report_date' => $data['report_date'],
                        'report_month' => $data['report_month'],
                        'report_content' => $data['report_content'],
                        'score' => $data['score'] ?? 0,
                    ]);


                    $logBook = LogBook::create([
                        'staff_id' => $data['staff_id'],
                        'job_description_id' => '13',
                        'description' => 'Created client report ID: ' . $report->id,
                        'count_task' => 1,
                        'date' => now()->toDateString(),
                        'status' => 'pending',
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Client report created')
                        ->send();
                }),
        ];
    }

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
