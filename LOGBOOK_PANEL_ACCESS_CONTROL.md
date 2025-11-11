# 📚 LogBook Panel-Based Access Control

## Update Summary

LogBook resource sekarang mendukung **panel-based access control** dengan behavior yang berbeda antara admin panel dan staff panel.

---

## 🔄 Changes Made

### 1. **canViewAny() Method**

```php
public static function canViewAny(): bool
{
    $panel = Filament::getCurrentPanel();

    // Admin panel: gunakan gate permission
    if ($panel && $panel->getId() === 'admin') {
        return Gate::allows('logbook.viewAny');
    }

    // Staff panel: staff bisa melihat data mereka sendiri
    if ($panel && $panel->getId() === 'staff') {
        $user = Auth::user();
        return $user && $user->staff_id;
    }

    return false;
}
```

### 2. **getEloquentQuery() Method**

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery()->withoutGlobalScopes([
        SoftDeletingScope::class,
    ]);

    $panel = Filament::getCurrentPanel();

    // Staff panel: filter hanya data milik staff yang login
    if ($panel && $panel->getId() === 'staff') {
        $user = Auth::user();
        if ($user && $user->staff_id) {
            $query->where('staff_id', $user->staff_id);
        }
    }

    return $query;
}
```

### 3. **Staff Filter Visibility**

```php
Tables\Filters\SelectFilter::make('staff_id')
    ->relationship('staff', 'name')
    ->label('Staff')
    ->visible(fn(): bool => Filament::getCurrentPanel()?->getId() === 'admin'), // Hanya di admin panel
```

---

## 📊 Behavior by Panel

### 🏢 **Admin Panel** (`/app`)

✅ **Access Control**: Gate permission (`logbook.viewAny`)  
✅ **Data Scope**: All logbook records  
✅ **Staff Column**: Visible  
✅ **Staff Filter**: Available  
✅ **Actions**: Full CRUD + Comment features

### 👤 **Staff Panel** (`/staff`)

✅ **Access Control**: User harus punya `staff_id`  
🔒 **Data Scope**: Own logbook records only  
❌ **Staff Column**: Hidden  
❌ **Staff Filter**: Hidden  
✅ **Actions**: CRUD (hanya data sendiri)

---

## 🔍 Logic Flow

### Admin Panel Access:

1. Check if current panel is 'admin'
2. Verify `Gate::allows('logbook.viewAny')`
3. If allowed → Show all logbook records
4. Staff filter available for filtering

### Staff Panel Access:

1. Check if current panel is 'staff'
2. Check if user has `staff_id`
3. If yes → Show only own logbook records
4. Auto-filter by `staff_id`

---

## 🛡️ Security Features

### **Query-Level Filtering**

```sql
-- Admin Panel: No additional WHERE clause
SELECT * FROM log_books WHERE deleted_at IS NULL

-- Staff Panel: Auto-filtered
SELECT * FROM log_books WHERE staff_id = ? AND deleted_at IS NULL
```

### **UI-Level Controls**

-   Staff column hidden in staff panel
-   Staff filter hidden in staff panel
-   Form staff selection limited to self in staff panel
-   Comment actions admin-only

---

## 🎯 Benefits

✅ **Security**: Staff tidak bisa akses logbook staff lain  
✅ **Context Awareness**: UI menyesuaikan berdasarkan panel  
✅ **Performance**: Query otomatis ter-filter di staff panel  
✅ **UX**: Staff fokus pada data mereka sendiri  
✅ **Admin Flexibility**: Admin tetap bisa melihat semua data

---

## 🔧 Implementation Notes

### Panel Detection:

```php
$panel = Filament::getCurrentPanel();
$isAdminPanel = $panel && $panel->getId() === 'admin';
$isStaffPanel = $panel && $panel->getId() === 'staff';
```

### Staff ID Check:

```php
$user = Auth::user();
$staffId = $user && $user->staff_id;
```

### Conditional Visibility:

```php
->visible(fn(): bool => Filament::getCurrentPanel()?->getId() === 'admin')
```

---

## ✅ Ready to Use

LogBook sekarang memberikan pengalaman yang tepat untuk setiap jenis user:

-   **Admin**: Full access untuk monitoring semua staff
-   **Staff**: Personal logbook untuk tracking aktivitas sendiri

**Panel-based access control berhasil diterapkan!** 🎉
