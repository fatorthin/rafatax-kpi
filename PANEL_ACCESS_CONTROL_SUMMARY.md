# Panel-Based Access Control Implementation Summary

## Overview

Implementasi kontrol akses berbasis panel untuk resource LogBook di aplikasi Laravel Filament dual-panel (admin + staff).

## Architecture

### Panel Structure

-   **Admin Panel**: `/app` - Akses penuh dengan permission gates
-   **Staff Panel**: `/staff` - Akses terbatas berdasarkan staff_id

### Key Features

#### 1. Dynamic Panel Detection

```php
$panel = Filament::getCurrentPanel();
if ($panel && $panel->getId() === 'admin') {
    // Admin panel logic
} elseif ($panel && $panel->getId() === 'staff') {
    // Staff panel logic
}
```

#### 2. Conditional Access Control

**Admin Panel**:

-   Menggunakan Gate permissions: `Gate::allows('logbook.viewAny')`
-   Dapat melihat semua LogBook records
-   Memiliki filter Staff untuk menyaring data

**Staff Panel**:

-   Hanya bisa akses jika user memiliki `staff_id`
-   Otomatis filter hanya data milik staff tersebut
-   Tidak menampilkan filter Staff (karena tidak diperlukan)

## Implementation Details

### 1. LogBookResource.php

#### canViewAny() Method

```php
public static function canViewAny(): bool
{
    $panel = Filament::getCurrentPanel();

    if ($panel && $panel->getId() === 'admin') {
        return Gate::allows('logbook.viewAny');
    }

    if ($panel && $panel->getId() === 'staff') {
        $user = Auth::user();
        return $user && $user->staff_id;
    }

    return false;
}
```

#### getEloquentQuery() Method

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    $panel = Filament::getCurrentPanel();
    if ($panel && $panel->getId() === 'staff') {
        $user = Auth::user();
        if ($user && $user->staff_id) {
            $query->where('staff_id', $user->staff_id);
        }
    }

    return $query;
}
```

#### Staff Filter Visibility

```php
Tables\Filters\SelectFilter::make('staff_id')
    ->relationship('staff', 'name')
    ->searchable()
    ->preload()
    ->visible(fn(): bool => Auth::user()?->hasRole('admin') ?? false),
```

### 2. Panel Registration

#### AdminPanelProvider.php

```php
->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
```

#### StaffPanelProvider.php

```php
->resources([
    \App\Filament\Resources\LogBookResource::class,
    \App\Filament\Resources\ClientReportResource::class,
])
```

## Testing Results

### Route Registration

```
GET|HEAD  app/log-books    filament.admin.resources.log-books.index
GET|HEAD  staff/log-books  filament.staff.resources.log-books.index
```

### Data Filtering Test

-   Total LogBook records: 2
-   Records for staff_id 25: 1 (staff user)
-   Records for staff_id 30: 1 (admin user with staff_id)

### User Setup

-   Admin: admin@rafatax.com (staff_id: 30, role: admin)
-   Staff: staff@rafatax.com (staff_id: 25, role: staff)

## Security Benefits

1. **Data Isolation**: Staff hanya melihat data mereka sendiri
2. **UI Simplification**: Interface disesuaikan dengan kebutuhan panel
3. **Permission Integration**: Memanfaatkan Laravel Gate dan Spatie Permission
4. **Flexible Access**: Admin dapat switch between panels dengan akses berbeda

## Usage Examples

### Admin Panel Access

-   URL: `/app/log-books`
-   Behavior:
    -   Menampilkan semua LogBook records
    -   Tersedia filter by Staff
    -   Memerlukan permission 'logbook.viewAny'

### Staff Panel Access

-   URL: `/staff/log-books`
-   Behavior:
    -   Hanya menampilkan LogBook milik staff yang login
    -   Tidak ada filter Staff (otomatis terfilter)
    -   Memerlukan user memiliki staff_id

## Error Handling

### Compilation Warnings

-   Warning: `Undefined method 'hasRole'` - False positive karena method ada di Spatie trait
-   Solution: Method tetap berfungsi normal, warning dapat diabaikan

### Access Denied Scenarios

-   User tanpa staff_id di staff panel → return false
-   User tanpa permission di admin panel → return false

## Maintenance Notes

1. Pastikan setiap user memiliki staff_id yang valid
2. Role assignment harus sesuai dengan panel akses
3. Test filtering setelah perubahan data atau user
4. Monitor performance jika data LogBook bertambah besar

## Future Enhancements

1. Cache query results untuk performance
2. Tambah audit log untuk akses data
3. Implementasi soft delete protection
4. Advanced filtering options per panel
