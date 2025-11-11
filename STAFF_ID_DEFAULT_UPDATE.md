# 🔄 Update: Staff ID Default Behavior

## Perubahan

Sebelumnya di panel staff:

-   **Admin**: Staff ID field kosong (tidak ada default)
-   **Staff**: Staff ID field default ke diri sendiri

Sekarang di panel staff:

-   **Admin**: Staff ID field default ke staff_id admin yang login
-   **Staff**: Staff ID field default ke staff_id sendiri

## Alasan

Karena ini adalah **panel staff**, semua aktivitas case project diasumsikan untuk staff yang sedang aktif (baik admin maupun staff biasa).

## Implementation

### Form Default Value:

```php
->default(function () {
    $user = Auth::user();
    // Di panel staff, default selalu ke staff_id user yang login
    return $user ? $user->staff_id : null;
})
```

### Create Action:

```php
->mutateFormDataUsing(function (array $data): array {
    // Di panel staff, auto-assign staff_id ke user yang login (admin/staff)
    $user = Auth::user();
    if ($user && $user->staff_id) {
        $data['staff_id'] = $user->staff_id;
    }
    return $data;
})
```

### Edit Action:

```php
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
})
```

## Behavior

### Admin di Panel Staff:

✅ Staff ID **default terisi** dengan admin yang login  
✅ Field **enabled** (bisa diubah ke staff lain)  
✅ Bisa assign case project ke staff manapun

### Staff di Panel Staff:

✅ Staff ID **default terisi** dengan staff yang login  
🔒 Field **disabled** (tidak bisa diubah)  
🔒 Hanya bisa assign case project ke diri sendiri

## Benefit

-   **Konsistensi**: Semua user di panel staff mendapat default yang logis
-   **Efisiensi**: Admin tidak perlu manual pilih staff_id setiap kali
-   **Context-aware**: Behavior sesuai dengan konteks panel staff
