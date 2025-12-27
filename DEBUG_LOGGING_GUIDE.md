# 🔍 DEBUG LOGGING - Update User Role

## ✅ Logging Telah Ditambahkan!

Comprehensive logging telah ditambahkan di:
- ✅ `AdminUserController.php` - method `updateRole()`
- ✅ `AdminUserController_RBAC.php` - method `updateRole()`

---

## 📋 LOG YANG AKAN MUNCUL:

### **Saat Request Diterima:**
```
[timestamp] local.INFO: === UPDATE USER ROLE START === 
{
  "userId": 3,
  "request_data": {"role_id": 2},
  "admin_id": 1
}
```

### **Step-by-Step Execution:**
```
[timestamp] local.INFO: Step 1: Validation passed {"validated":{"role_id":2}}
[timestamp] local.INFO: Step 2: User found 
{
  "user_id": 3,
  "nama": "Reno",
  "current_role_id": 1,
  "current_level": "bronze"
}
[timestamp] local.INFO: Step 3: New role found 
{
  "role_id": 2,
  "nama_role": "Admin",
  "level_akses": 2
}
[timestamp] local.INFO: Setting level to admin
[timestamp] local.INFO: Step 4: Prepared update data {"updateData":{"role_id":2,"level":"admin"}}
[timestamp] local.INFO: Step 5: User updated in database
[timestamp] local.INFO: Step 6: Data refreshed from DB 
{
  "user_id": 3,
  "new_role_id": 2,
  "new_level": "admin"
}
[timestamp] local.INFO: === UPDATE USER ROLE SUCCESS ===
```

### **Jika Ada Error:**
```
[timestamp] local.ERROR: === UPDATE ROLE ERROR === 
{
  "message": "SQLSTATE[HY000]: ...",
  "file": "C:\\Users\\Adib\\Desktop\\mendaur-api2\\app\\...",
  "line": 123,
  "trace": "#0 ..."
}
```

---

## 🔍 CARA MELIHAT LOG:

### **Method 1: PowerShell (Real-time)**
```powershell
# Buka terminal di folder project
cd C:\Users\Adib\Desktop\mendaur-api2

# Lihat log secara real-time
Get-Content storage\logs\laravel.log -Wait -Tail 50
```

### **Method 2: VSCode**
1. Buka file: `storage/logs/laravel.log`
2. Scroll ke bawah (atau tekan `Ctrl+End`)
3. Log terbaru ada di bagian bawah

### **Method 3: PowerShell (Filter by Keyword)**
```powershell
# Cari log "UPDATE USER ROLE"
Select-String -Path "storage\logs\laravel.log" -Pattern "UPDATE USER ROLE" | Select-Object -Last 20

# Cari log error
Select-String -Path "storage\logs\laravel.log" -Pattern "ERROR" | Select-Object -Last 20

# Cari log untuk user tertentu
Select-String -Path "storage\logs\laravel.log" -Pattern "userId.*3" | Select-Object -Last 10
```

---

## 🧪 TESTING DENGAN LOG:

### **1. Buka Terminal untuk Monitoring Log:**
```powershell
# Terminal 1 - Monitor log real-time
Get-Content storage\logs\laravel.log -Wait -Tail 50
```

### **2. Test Update Role di Admin Dashboard:**
- Login sebagai Superadmin
- Buka halaman User Management
- Pilih user "Reno" 
- Ubah role dari "Nasabah" ke "Admin"
- Klik Save/Update

### **3. Lihat Output di Terminal:**
Log akan muncul step-by-step seperti contoh di atas!

---

## 🐛 TROUBLESHOOTING:

### **Jika Log Tidak Muncul:**
```powershell
# 1. Check apakah file log ada
Test-Path storage\logs\laravel.log

# 2. Check permission
Get-Acl storage\logs\laravel.log

# 3. Clear cache
php artisan cache:clear
php artisan config:clear

# 4. Check APP_DEBUG di .env
Select-String -Path ".env" -Pattern "APP_DEBUG"
# Pastikan: APP_DEBUG=true
```

### **Jika Error "Permission Denied":**
```powershell
# Berikan write permission
icacls storage\logs /grant Everyone:F /T
```

---

## 📊 CONTOH ERROR YANG BISA TERDETEKSI:

### **1. User Not Found**
```
local.ERROR: User not found {"userId":999}
```

### **2. Role Not Found**
```
local.ERROR: New role not found {"role_id":99}
```

### **3. Database Error**
```
local.ERROR: === UPDATE ROLE ERROR === 
{
  "message": "SQLSTATE[23000]: Integrity constraint violation",
  "file": "...",
  "line": 123
}
```

### **4. Validation Error**
```
local.ERROR: === UPDATE ROLE VALIDATION ERROR === 
{
  "errors": {"role_id": ["The role id field is required."]},
  "message": "The given data was invalid."
}
```

---

## ✅ EXPECTED OUTPUT (Success):

Ketika mengubah Reno dari Nasabah ke Admin:

```
[2025-12-24 15:30:00] local.INFO: === UPDATE USER ROLE START === {"userId":"3","request_data":{"role_id":2},"admin_id":1}
[2025-12-24 15:30:00] local.INFO: Step 1: Validation passed {"validated":{"role_id":2}}
[2025-12-24 15:30:00] local.INFO: Step 2: User found {"user_id":3,"nama":"Reno","current_role_id":1,"current_level":"bronze"}
[2025-12-24 15:30:00] local.INFO: Step 3: New role found {"role_id":2,"nama_role":"Admin","level_akses":2}
[2025-12-24 15:30:00] local.INFO: Setting level to admin
[2025-12-24 15:30:00] local.INFO: Step 4: Prepared update data {"updateData":{"role_id":2,"level":"admin"}}
[2025-12-24 15:30:00] local.INFO: Step 5: User updated in database
[2025-12-24 15:30:00] local.INFO: Step 6: Data refreshed from DB {"user_id":3,"new_role_id":2,"new_level":"admin"}
[2025-12-24 15:30:00] local.INFO: === UPDATE USER ROLE SUCCESS ===
```

✅ **Reno sekarang Admin dengan level='admin'**

---

## 🎯 NEXT STEPS:

1. **Buka terminal** untuk monitoring log
2. **Test update role** di admin dashboard
3. **Lihat log output** untuk debug
4. **Share screenshot log** jika masih ada error

---

**Happy Debugging! 🐛🔍**
