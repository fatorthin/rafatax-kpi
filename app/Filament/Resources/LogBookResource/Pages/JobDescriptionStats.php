<?php

namespace App\Filament\Resources\LogBookResource\Pages;

use App\Filament\Resources\LogBookResource;
use App\Models\JobDescription;
use App\Models\LogBook;
use App\Models\Staff;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;
use Illuminate\Support\Collection;

class JobDescriptionStats extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = LogBookResource::class;

    protected static string $view = 'filament.resources.log-book-resource.pages.job-description-stats';

    protected static ?string $title = 'Job Description Statistics';

    protected static ?string $navigationLabel = 'Statistik Job Description';

    public ?int $selectedMonth = null;
    public ?int $selectedYear = null;
    public Collection $jobDescriptionStats;

    public function mount(): void
    {
        // Default ke bulan dan tahun saat ini
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
        $this->loadJobDescriptionStats();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('job_description')
                    ->label('Job Description')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('position.name')
                    ->label('Position')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('point')
                    ->label('Point')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('logbook_count')
                    ->label('Jumlah LogBook')
                    ->sortable()
                    ->alignCenter()
                    ->color(function ($state) {
                        if ($state === 0) return 'danger';
                        if ($state < 5) return 'warning';
                        return 'success';
                    }),
                Tables\Columns\TextColumn::make('total_points')
                    ->label('Total Point')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn($state, $record) => $record->logbook_count * $record->point),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('position_id')
                    ->label('Position')
                    ->relationship('position', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('viewLogBooks')
                    ->label('View LogBooks')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn(JobDescription $record): string => 'LogBook Entries - ' . $record->job_description)
                    ->modalDescription(fn(JobDescription $record): string => 'Periode: ' . $this->getSelectedPeriod())
                    ->modalContent(function (JobDescription $record) {
                        $user = Auth::user();
                        $panel = Filament::getCurrentPanel();

                        $query = LogBook::with(['staff'])
                            ->where('job_description_id', $record->id)
                            ->whereMonth('date', $this->selectedMonth)
                            ->whereYear('date', $this->selectedYear);

                        // Filter berdasarkan panel
                        if ($panel && $panel->getId() === 'staff') {
                            if ($user && $user->staff_id) {
                                $query->where('staff_id', $user->staff_id);
                            }
                        }

                        $logbooks = $query->orderBy('date', 'desc')->get();

                        return view('filament.resources.log-book-resource.pages.logbook-list-modal', [
                            'logbooks' => $logbooks,
                            'jobDescription' => $record,
                        ]);
                    })
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->defaultSort('logbook_count', 'desc')
            ->emptyStateHeading('Tidak ada data Job Description')
            ->emptyStateDescription('Tidak ada data untuk periode yang dipilih atau posisi staff.');
    }

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();
        $panel = Filament::getCurrentPanel();

        $query = JobDescription::with(['position'])
            ->withCount([
                'logBooks as logbook_count' => function (Builder $query) {
                    $query->whereMonth('date', $this->selectedMonth)
                        ->whereYear('date', $this->selectedYear);

                    // Filter berdasarkan panel
                    $panel = Filament::getCurrentPanel();
                    if ($panel && $panel->getId() === 'staff') {
                        $user = Auth::user();
                        if ($user && $user->staff_id) {
                            $query->where('staff_id', $user->staff_id);
                        }
                    }
                }
            ]);

        // Filter job descriptions berdasarkan panel
        if ($panel && $panel->getId() === 'staff') {
            if ($user && $user->staff_id) {
                $staff = Staff::find($user->staff_id);
                if ($staff) {
                    $query->where('position_id', $staff->position_reference_id);
                }
            }
        }

        return $query;
    }

    public function loadJobDescriptionStats(): void
    {
        $user = Auth::user();
        $panel = Filament::getCurrentPanel();

        $query = JobDescription::with(['position'])
            ->withCount([
                'logBooks as logbook_count' => function (Builder $query) {
                    $query->whereMonth('date', $this->selectedMonth)
                        ->whereYear('date', $this->selectedYear);

                    // Filter berdasarkan panel
                    $panel = Filament::getCurrentPanel();
                    if ($panel && $panel->getId() === 'staff') {
                        $user = Auth::user();
                        if ($user && $user->staff_id) {
                            $query->where('staff_id', $user->staff_id);
                        }
                    }
                }
            ]);

        // Filter job descriptions berdasarkan panel
        if ($panel && $panel->getId() === 'staff') {
            if ($user && $user->staff_id) {
                $staff = Staff::find($user->staff_id);
                if ($staff) {
                    $query->where('position_id', $staff->position_reference_id);
                }
            }
        }

        $this->jobDescriptionStats = $query->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('filterPeriod')
                ->label('Filter Periode')
                ->icon('heroicon-o-funnel')
                ->color('primary')
                ->form([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('selectedMonth')
                                ->label('Bulan')
                                ->options([
                                    1 => 'Januari',
                                    2 => 'Februari',
                                    3 => 'Maret',
                                    4 => 'April',
                                    5 => 'Mei',
                                    6 => 'Juni',
                                    7 => 'Juli',
                                    8 => 'Agustus',
                                    9 => 'September',
                                    10 => 'Oktober',
                                    11 => 'November',
                                    12 => 'Desember',
                                ])
                                ->default($this->selectedMonth)
                                ->required(),
                            Forms\Components\Select::make('selectedYear')
                                ->label('Tahun')
                                ->options(function () {
                                    $currentYear = now()->year;
                                    $years = [];
                                    for ($year = $currentYear - 2; $year <= $currentYear + 1; $year++) {
                                        $years[$year] = $year;
                                    }
                                    return $years;
                                })
                                ->default($this->selectedYear)
                                ->required(),
                        ]),
                ])
                ->action(function (array $data): void {
                    $this->selectedMonth = $data['selectedMonth'];
                    $this->selectedYear = $data['selectedYear'];
                    $this->loadJobDescriptionStats();
                }),
            Actions\Action::make('refresh')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->action(fn() => $this->loadJobDescriptionStats())
                ->color('gray'),
        ];
    }

    public function getStatsCards(): array
    {
        $totalJobs = $this->jobDescriptionStats->count();
        $totalLogBooks = $this->jobDescriptionStats->sum('logbook_count');
        $totalPoints = $this->jobDescriptionStats->sum(function ($job) {
            return $job->logbook_count * $job->point;
        });
        $avgLogBooksPerJob = $totalJobs > 0 ? round($totalLogBooks / $totalJobs, 2) : 0;

        return [
            'total_jobs' => $totalJobs,
            'total_logbooks' => $totalLogBooks,
            'total_points' => $totalPoints,
            'avg_logbooks_per_job' => $avgLogBooksPerJob,
        ];
    }

    public function getSelectedPeriod(): string
    {
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return $monthNames[$this->selectedMonth] . ' ' . $this->selectedYear;
    }
}
