# 🤖 Copilot Instructions for Mendaur API

## Project Overview
**Mendaur** is a waste management & recycling rewards platform built with Laravel 12 + React Vite.
- **Backend**: Laravel 12 with Sanctum authentication, RBAC (3 roles, 62 permissions)
- **Frontend**: React with Vite + Tailwind CSS
- **Database**: MySQL with 29 migrations, hierarchical waste category system
- **Status**: 98% complete (backend 100%, frontend 80%)

## Critical Architecture Patterns

### 1. RBAC System (Role-Based Access Control)
**Location**: `app/Models/Role.php`, `database/seeders/RolePermissionSeeder.php`
- **3 Roles**: `nasabah` (users), `admin` (40 perms), `superadmin` (62 perms)
- **Inheritance**: Admin inherits nasabah perms; superadmin inherits admin perms
- **Key Pattern**: `Role::getInheritedPermissions()` for permission cascading
- **Middleware**: Use `app/Http/Middleware/AdminMiddleware.php` to protect admin routes
- **Implementation**: Every controller needing role checks should use `$request->user()->role_id`

### 2. Service Layer Pattern
**Use for**: Complex business logic (avoid putting everything in controllers)
- **Example**: `BadgeTrackingService` handles badge award logic
- **Pattern**: Services contain reusable methods called by multiple controllers
- **Transaction Safety**: Wrap updates in `DB::transaction()` for atomic operations
- **Location**: Create service classes in `app/Services/` directory

### 3. Hierarchical Category System
**Location**: `app/Models/KategoriSampah.php`, `JenisSampah.php`
- **Structure**: Categories contain multiple waste types (many-to-one)
- **API Endpoints**: 
  - `GET /api/kategori-sampah` - All categories with nested waste types
  - `GET /api/jenis-sampah-all` - Flat list for dropdowns
- **Query Pattern**: Use `with('jenis')` for eager loading to avoid N+1 queries

### 4. Point System Architecture
**Components**:
- `app/Models/PoinTransaksi.php` - Audit trail of all point changes
- `app/Http/Controllers/PointController.php` - User point endpoints
- **Key Logic**: Points awarded on deposit, product redemption, badge achievement
- **Deduction Pattern**: Always validate available points before deduction (prevent negatives)
- **Transactions**: Wrap updates in DB::transaction() - critical for consistency

### 5. Authentication Flow (Sanctum)
**Process**: 
1. Login returns Bearer token + user data with `role` field
2. Frontend stores token in localStorage
3. All API requests include: `Authorization: Bearer {token}`
4. Routes protected with `middleware('auth:sanctum')`
- **Test Accounts**:
  - `admin@test.com / admin123` (40 permissions)
  - `superadmin@test.com / superadmin123` (62 permissions)
  - 6 nasabah test accounts (17 permissions each)

## Key Code Locations

| Feature | Files |
|---------|-------|
| **Authentication** | `app/Http/Controllers/AuthController.php`, `routes/api.php` (lines 24-26) |
| **RBAC** | `app/Models/Role.php`, `app/Http/Middleware/AdminMiddleware.php` |
| **Admin Dashboard** | `app/Http/Controllers/DashboardAdminController.php` (502 lines) |
| **Waste Categories** | `app/Http/Controllers/KategoriSampahController.php`, `routes/api.php` (lines 43-46) |
| **Point System** | `app/Http/Controllers/PointController.php`, `app/Models/PoinTransaksi.php` |
| **Badge System** | `app/Http/Controllers/BadgeController.php`, Badge* models |
| **Routes** | `routes/api.php` (212 lines - organized by feature) |

## Testing Workflow

### Database Reset
```bash
php artisan migrate:fresh --seed  # Recreates DB with test data
php verify_roles.php              # Confirms RBAC is working
```

### API Testing
```bash
# Get admin token
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"admin123"}' | jq -r '.data.token')

# Test protected endpoint
curl -H "Authorization: Bearer $TOKEN" http://127.0.0.1:8000/api/admin/dashboard/overview
```

### Common Commands
- `php artisan serve` - Start backend (port 8000)
- `pnpm run dev` - Start frontend (port 5173)
- `php artisan tinker` - Interactive shell for testing
- `php artisan migrate --rollback` - Undo last migration batch

## Important Project Conventions

### 1. Naming Conventions
- **Database columns**: snake_case (e.g., `total_poin`, `created_at`)
- **Model properties**: $fillable lists fields to mass-assign
- **Controllers**: Singular resource (UserController, not UsersController)
- **Models**: Singular class names (User, not Users)

### 2. API Response Format (Consistent)
```json
{
  "status": "success",
  "message": "Optional message",
  "data": { /* payload */ }
}
```
- Always include `status` field
- Use standard HTTP codes: 200 (OK), 201 (Created), 400 (Validation), 401 (Auth), 404 (Not Found)

### 3. Error Handling Pattern
```php
try {
  // business logic
  return response()->json(['status' => 'success', 'data' => $result], 200);
} catch (\Exception $e) {
  return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
}
```

### 4. Eloquent Query Best Practices
- Use `with()` for eager loading relationships (prevents N+1)
- Use query scopes for reusable filters: `public function scopeActive($query) { return $query->where('status', 'active'); }`
- Use `select()` to limit columns when fetching large datasets
- Pagination: `Model::paginate(15)` returns `data`, `current_page`, `total`

### 5. Foreign Keys
- All ForeignKeyConstraints use `onDelete('cascade')` or `onDelete('restrict')` based on business logic
- Migrations disable FK checks: `DB::statement('SET FOREIGN_KEY_CHECKS=0;')` before truncate
- Always verify FK constraints after modifications with `php artisan db:show --tables`

## Common Pitfalls to Avoid

❌ **Don't**:
- Mix business logic in controllers - extract to services
- Query N+1: Always use `with('relationship')` when fetching multiple rows
- Hardcode role names - reference via `Role::where('nama_role', 'admin')->first()`
- Forget to include `role` field in login response
- Create migrations without descriptive names (date_feature_name.php)
- Truncate tables without `DB::statement('SET FOREIGN_KEY_CHECKS=0;')`

✅ **Do**:
- Put complex logic in Service classes
- Use query scopes for filtering: `User::active()->get()`
- Validate role via middleware not inline: Use `middleware('admin')` on routes
- Wrap multi-step updates in `DB::transaction()`
- Test with test accounts before merging changes
- Document API changes in `/DOCUMENTATION` folder

## The 6 Admin Dashboard Endpoints (All Implemented ✅)

**Base URL**: `http://127.0.0.1:8000/api` (requires Bearer token + admin role)

1. **GET /api/admin/dashboard/overview** - System KPI statistics
2. **GET /api/admin/dashboard/users** - Paginated user list with search
3. **GET /api/admin/dashboard/waste-summary** - Waste by type & period
4. **GET /api/admin/dashboard/point-summary** - Points by source & period
5. **GET /api/admin/dashboard/waste-by-user** - User-level waste breakdown
6. **GET /api/admin/dashboard/report** - Daily/monthly reports

**All 6 endpoints are fully implemented** in `app/Http/Controllers/DashboardAdminController.php`

## Documentation Quick Links

| Use Case | Document |
|----------|----------|
| 📋 Overview | `DOCUMENTATION/START_HERE_&_README/00_README_START_HERE.md` |
| 🔌 API Specs | `DOCUMENTATION/API_DOCUMENTATION/API_ENDPOINTS_QUICK_REFERENCE.md` |
| 🗂️ Categories | `DOCUMENTATION/FEATURE_IMPLEMENTATION/CATEGORIES/KATEGORI_SAMPAH_IMPLEMENTATION_COMPLETE.md` |
| 💰 Points | `DOCUMENTATION/FEATURE_IMPLEMENTATION/POINT_SYSTEM/POINT_SYSTEM_ANALYSIS_AND_PLAN.md` |
| 🏅 Badges | `DOCUMENTATION/FEATURE_IMPLEMENTATION/BADGES/BADGE_TRACKING_SYSTEM.md` |
| 🐛 Debug | `DOCUMENTATION/FIXES_&_DEBUGGING/FIX_404_ERRORS.md` |

## Database Schema Quick Reference

**Key Tables**:
- `users` - User accounts (with `role_id` for RBAC)
- `roles` - 3 roles: nasabah, admin, superadmin
- `role_permissions` - Links roles to 62 permissions
- `kategori_sampah` - Waste categories (parent table)
- `jenis_sampah` - Waste types (child of categories)
- `tabung_sampah` - User deposit records
- `poin_transaksi` - Audit trail of all point changes
- `badge_progress` - Tracks user progress toward badges
- `penukaran_produk` - Product redemption history

**Foreign Key Relationships**:
- Users → Roles (via `role_id`)
- JenisSampah → KategoriSampah (via `kategori_sampah_id`)
- BadgeProgress → Users & Badges
- All tables have `created_at`, `updated_at` timestamps

## When You Get Stuck

1. **Route not found (404)**: Check `routes/api.php` for exact endpoint
2. **401 Unauthorized**: Verify Bearer token + login with test account first
3. **500 Error**: Check `storage/logs/laravel.log` for exception
4. **Database issues**: Run `php artisan migrate:fresh --seed` to reset
5. **Permission denied**: Verify `$user->role_id` matches required role in middleware
6. **N+1 Queries**: Use `with('relationship')` on model queries
7. **Validation fails**: Check controller's `$request->validate()` rules

## File Creation Best Practices

⚠️ **CRITICAL**: Avoid creating duplicate files
- ✅ Create documentation files in root folder (allowed)
- ❌ DO NOT call create_file twice with the same filename
- ❌ DO NOT create the same file in both root AND subdirectories
- **Check first**: Before creating a file, verify it doesn't already exist
- **If accidental duplicates appear**: Use `cleanup_duplicates.ps1` to remove them

---

**Last Updated**: December 2, 2025 | **Version**: 1.0 | **Maintainer**: Copilot Agents
