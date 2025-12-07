# 🌱 Mendaur API

Waste Management & Recycling Rewards Platform built with Laravel 12 + React Vite.

## 📋 Overview

**Mendaur** adalah platform pengelolaan sampah dan sistem reward untuk mendorong daur ulang. Aplikasi ini menggabungkan backend Laravel yang robust dengan frontend React modern.

## 🎯 Features

- ✅ **Waste Deposit Tracking** - Track sampah yang disetor dengan kategori hierarki
- ✅ **Point System** - Earn points dari deposit sampah dan aktivitas lainnya
- ✅ **Badge Achievement** - Unlock badges dan leaderboard system
- ✅ **Admin Dashboard** - Analytics dan reporting untuk admin
- ✅ **Role-Based Access Control (RBAC)** - 3 roles: nasabah, admin, superadmin
- ✅ **Product Redemption** - Tukar points dengan produk
- ✅ **Cash Withdrawal** - Withdraw saldo dalam bentuk uang tunai

## 🛠️ Tech Stack

**Backend:**
- Laravel 12
- Sanctum (JWT Authentication)
- MySQL Database
- 29 Migrations + 24 Active Tables

**Frontend:**
- React 18 with Vite
- Tailwind CSS
- Axios HTTP Client

**Status:** 98% Complete
- Backend: ✅ 100%
- Frontend: 🔄 80%

## 🚀 Quick Start

### Backend Setup

```bash
# Clone repository
git clone https://github.com/Adib121140210/mendaur.git
cd mendaur

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate:fresh --seed

# Start server
php artisan serve
# Backend: http://127.0.0.1:8000
```

### Frontend Setup

```bash
# Install dependencies
pnpm install

# Start dev server
pnpm run dev
# Frontend: http://localhost:5173
```

## 🔐 Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@test.com | admin123 |
| Superadmin | superadmin@test.com | superadmin123 |
| User | user@test.com | user123 |

## 📚 Documentation

Dokumentasi lengkap tersedia di folder `/DOCUMENTATION`:
- **API Endpoints** - `DOCUMENTATION/API_DOCUMENTATION/API_ENDPOINTS_QUICK_REFERENCE.md`
- **Database Schema** - `DOCUMENTATION/DATABASE_&_SCHEMA/`
- **Feature Implementation** - `DOCUMENTATION/FEATURE_IMPLEMENTATION/`
- **Admin Dashboard** - `DOCUMENTATION/ADMIN_DASHBOARD/`

## 📊 API Endpoints (Key)

### Authentication
```
POST   /api/login          - User login
POST   /api/register       - User registration
POST   /api/logout         - User logout
GET    /api/profile        - Get user profile
```

### Waste Management
```
GET    /api/kategori-sampah        - Get all waste categories
GET    /api/jenis-sampah-all       - Get all waste types
POST   /api/tabung-sampah          - Create waste deposit
GET    /api/tabung-sampah          - Get user deposits
```

### Points & Badges
```
GET    /api/poin-saya              - Get user points
GET    /api/badges                 - Get user badges
GET    /api/leaderboard            - Global leaderboard
```

### Admin Dashboard
```
GET    /api/admin/dashboard/overview       - KPI statistics
GET    /api/admin/dashboard/users          - User list
GET    /api/admin/dashboard/waste-summary  - Waste analytics
GET    /api/admin/dashboard/point-summary  - Point analytics
```

## 🔑 Key Architecture

### Database Structure
- **24 Active Tables** organized by feature
- **Foreign Key Constraints** with cascade/restrict
- **Timestamps** on all tables (created_at, updated_at)
- **Soft Deletes** where applicable

### RBAC System
- **3 Roles**: nasabah (user), admin, superadmin
- **62 Permissions** granular access control
- **Role Inheritance**: Admin inherits nasabah perms; superadmin inherits admin
- **Middleware Protection**: `AdminMiddleware`, `CheckRole`, `CheckPermission`

### Service Layer
- `BadgeTrackingService` - Badge award logic
- `PointService` - Point calculation & validation
- `BadgeProgressService` - Progress tracking

## 🧪 Testing

### Run Tests
```bash
php artisan test
```

### Manual API Testing
```bash
# Get admin token
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"admin123"}' | jq -r '.data.token')

# Test protected endpoint
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/admin/dashboard/overview
```

## 📝 Project Status

**December 2025:**
- ✅ Core backend features complete
- ✅ RBAC system fully implemented
- ✅ Database schema optimized
- ✅ All 6 admin dashboard endpoints working
- 🔄 Frontend integration in progress
- ⏳ Production deployment pending

## 🐛 Known Issues & Fixes

- ✅ Empty migration files (OneDrive sync artifacts) - removed from tracking
- ✅ UTF-8 BOM in AuthController - fixed
- ✅ Admin login response format - returns role object with permissions
- ⏳ Frontend admin dashboard - pending integration testing

## 📞 Support

For issues or questions:
1. Check `/DOCUMENTATION` folder for detailed guides
2. Review API endpoints documentation
3. Check test accounts and credentials above

## 📄 License

MIT License - Free for educational and commercial use.

---

**Repository**: https://github.com/Adib121140210/mendaur  
**Last Updated**: December 7, 2025
