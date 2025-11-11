# Job Description Statistics Custom Page Documentation

## Overview

Custom page untuk menampilkan statistik Job Description dengan jumlah LogBook yang terkait, dilengkapi dengan filter bulan dan tahun serta dashboard analytics yang komprehensif.

## Features

### 1. **Dashboard Statistics**

-   **Total Job Descriptions**: Jumlah total job descriptions berdasarkan posisi staff
-   **Total LogBooks**: Jumlah total logbook untuk periode yang dipilih
-   **Total Points**: Akumulasi points dari semua job descriptions
-   **Average LogBooks per Job**: Rata-rata logbook per job description

### 2. **Filter Functionality**

-   **Month Filter**: Dropdown untuk memilih bulan (Januari - Desember)
-   **Year Filter**: Dropdown untuk memilih tahun (2023-2026)
-   **Real-time Update**: Data otomatis ter-refresh saat filter berubah
-   **Default Period**: Default ke bulan dan tahun saat ini

### 3. **Data Table**

-   **Job Description**: Daftar detail job descriptions
-   **Position**: Posisi/jabatan yang terkait
-   **Point**: Point value untuk setiap job description
-   **LogBook Count**: Jumlah logbook untuk periode yang dipilih
-   **Total Points**: Perhitungan otomatis (LogBook Count × Point)

### 4. **Panel-Based Access Control**

-   **Admin Panel**: Melihat semua job descriptions dari semua posisi
-   **Staff Panel**: Hanya melihat job descriptions sesuai posisi staff yang login

### 5. **Performance Indicators**

-   **Needs Attention** (Red): Job descriptions dengan 0 LogBooks
-   **Below Average** (Yellow): Job descriptions dengan 1-4 LogBooks
-   **Good Performance** (Green): Job descriptions dengan 5+ LogBooks

## Technical Implementation

### Custom Page Class

**File**: `app/Filament/Resources/LogBookResource/Pages/JobDescriptionStats.php`

```php
class JobDescriptionStats extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = LogBookResource::class;
    protected static string $view = 'filament.resources.log-book-resource.pages.job-description-stats';

    // Filter properties
    public ?int $selectedMonth = null;
    public ?int $selectedYear = null;
    public Collection $jobDescriptionStats;
}
```

### Key Methods

#### Data Loading

```php
public function loadJobDescriptionStats(): void
{
    $query = JobDescription::with(['position'])
        ->withCount([
            'logBooks as logbook_count' => function (Builder $query) {
                $query->whereMonth('date', $this->selectedMonth)
                      ->whereYear('date', $this->selectedYear);

                // Panel-based filtering
                if (Filament::getCurrentPanel()->getId() === 'staff') {
                    $user = Auth::user();
                    if ($user && $user->staff_id) {
                        $query->where('staff_id', $user->staff_id);
                    }
                }
            }
        ]);

    // Filter by staff position in staff panel
    if (Filament::getCurrentPanel()->getId() === 'staff') {
        $user = Auth::user();
        if ($user && $user->staff_id) {
            $staff = Staff::find($user->staff_id);
            if ($staff) {
                $query->where('position_id', $staff->position_reference_id);
            }
        }
    }

    $this->jobDescriptionStats = $query->get();
}
```

#### Statistics Calculation

```php
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
```

### Blade Template

**File**: `resources/views/filament/resources/log-book-resource/pages/job-description-stats.blade.php`

#### Key Components:

1. **Filter Form**: Month and year selection with live updates
2. **Statistics Cards**: 4 cards showing key metrics
3. **Data Table**: Comprehensive job description list with filtering
4. **Performance Indicators**: Visual guides for performance levels

### Model Relationship

**File**: `app/Models/JobDescription.php`

```php
public function logBooks()
{
    return $this->hasMany(LogBook::class, 'job_description_id');
}
```

### Route Registration

**File**: `app/Filament/Resources/LogBookResource.php`

```php
public static function getPages(): array
{
    return [
        'index' => Pages\ManageLogBooks::route('/'),
        'job-description-stats' => Pages\JobDescriptionStats::route('/job-description-stats'),
    ];
}
```

## URL Access

### Admin Panel

```
GET /app/log-books/job-description-stats
```

### Staff Panel

```
GET /staff/log-books/job-description-stats
```

## Usage Examples

### 1. **Admin Use Case**

-   Admin dapat melihat statistik untuk semua job descriptions
-   Filter berdasarkan bulan/tahun untuk analisis periode tertentu
-   Monitoring performance semua staff dan posisi
-   Identifikasi job descriptions yang kurang aktif

### 2. **Staff Use Case**

-   Staff hanya melihat job descriptions sesuai posisi mereka
-   Memonitor progress logbook pribadi per job description
-   Melihat performance relative terhadap target

### 3. **Management Reporting**

-   Dashboard untuk meeting bulanan
-   Performance tracking per department
-   Resource allocation planning
-   KPI monitoring

## Performance Features

### 1. **Efficient Queries**

-   Menggunakan `withCount()` untuk menghitung LogBooks
-   Eager loading relationships dengan `with(['position'])`
-   Conditional queries berdasarkan panel access

### 2. **Real-time Updates**

-   Live filter updates tanpa page reload
-   Automatic data refresh saat filter berubah
-   Responsive statistics cards

### 3. **Optimized Data Loading**

-   Pagination pada table untuk large datasets
-   Efficient relationship loading
-   Memory-conscious statistics calculation

## Security & Access Control

### Panel-Based Filtering

```php
// Staff panel - filter by staff position
if ($panel && $panel->getId() === 'staff') {
    if ($user && $user->staff_id) {
        $staff = Staff::find($user->staff_id);
        if ($staff) {
            $query->where('position_id', $staff->position_reference_id);
        }
    }
}

// Staff panel - filter LogBooks by staff
if ($panel && $panel->getId() === 'staff') {
    $user = Auth::user();
    if ($user && $user->staff_id) {
        $query->where('staff_id', $user->staff_id);
    }
}
```

## Navigation Integration

### Header Action in ManageLogBooks

```php
Actions\Action::make('job_description_stats')
    ->label('Job Description Statistics')
    ->icon('heroicon-o-chart-bar')
    ->url(fn (): string => static::$resource::getUrl('job-description-stats'))
    ->color('info'),
```

## Testing Results

### Sample Data

-   **Job Descriptions Found**: 52
-   **Period**: November 2025
-   **Sample Jobs**:
    -   "Melakukan rekap laporan keuangan fiskal bulanan" (Tim Pajak) - 1 LogBook
    -   "Membuat e-billing pajak" (Tim Pajak) - 2 LogBooks
    -   "Melakukan pelaporan SPT Bulanan" (Tim Pajak) - 0 LogBooks

### Route Verification

-   ✅ Admin Panel: `/app/log-books/job-description-stats`
-   ✅ Staff Panel: `/staff/log-books/job-description-stats`

## Future Enhancements

1. **Export Functionality**: Excel/PDF export untuk reports
2. **Chart Visualizations**: Grafik untuk trend analysis
3. **Comparison Mode**: Compare multiple periods
4. **Target Setting**: Set targets per job description
5. **Email Reports**: Automated periodic reports
6. **Advanced Filtering**: Filter by department, team, etc.

## Maintenance Notes

1. **Performance**: Monitor query performance dengan large datasets
2. **Caching**: Consider caching untuk frequently accessed data
3. **Data Integrity**: Ensure LogBook dates are properly validated
4. **User Feedback**: Collect feedback untuk UI improvements

## File Structure

```
app/Filament/Resources/LogBookResource/Pages/JobDescriptionStats.php
resources/views/filament/resources/log-book-resource/pages/job-description-stats.blade.php
app/Models/JobDescription.php (updated with logBooks relationship)
app/Filament/Resources/LogBookResource.php (updated with page registration)
app/Filament/Resources/LogBookResource/Pages/ManageLogBooks.php (added navigation action)
```

## Dependencies

-   Laravel Filament v3.x
-   Carbon untuk date handling
-   Eloquent ORM untuk database queries
-   Blade templates untuk views
-   Heroicons untuk icons
