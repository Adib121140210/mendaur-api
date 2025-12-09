# 🌱 Mendaur API - Waste Management Gamification Platform

**Laravel 11 Backend API** for a waste recycling management system with gamification features.

## 🎯 **Project Overview**

Mendaur API is a comprehensive backend system for waste management with engaging gamification features including badges, rewards, leaderboards, and level progression. Users deposit waste, earn points, unlock badges, and compete on leaderboards.

## ✨ **Key Features**

### 🏆 **Gamification System**
- **Badge System**: 7 badges with automatic rewards (50-500 bonus points)
- **Point System**: Base points from waste + bonus points from badges
- **Level Progression**: Pemula → Bronze → Silver → Gold → Platinum
- **Leaderboard**: Multiple ranking types (points, deposits, badges)
- **Activity Logging**: Complete transparency of all point transactions

### ♻️ **Waste Management**
- Waste deposit submission and tracking
- Admin approval workflow
- Multiple waste types support
- Pickup scheduling system
- Transaction history

### 👤 **User Management**
- Authentication (Laravel Sanctum)
- Profile management with photo upload
- Badge collection tracking
- Activity history
- Statistics and progress tracking

### 📊 **Dashboard & Analytics**
- User statistics and rank
- Global platform metrics
- Monthly performance tracking
- Leaderboard rankings
- Badge progress tracking

## 📚 **Documentation**

### **Core Documentation:**
- 📖 **[GAMIFICATION_SYSTEM.md](./GAMIFICATION_SYSTEM.md)** - Complete gamification overview
- 📖 **[BADGE_REWARD_SYSTEM.md](./BADGE_REWARD_SYSTEM.md)** - Badge rewards with bonus points
- 📖 **[LEADERBOARD_SYSTEM.md](./LEADERBOARD_SYSTEM.md)** - Ranking and competition
- 📖 **[DASHBOARD_API.md](./DASHBOARD_API.md)** - User stats and dashboard
- 📖 **[PROFILE_API_GUIDE.md](./PROFILE_API_GUIDE.md)** - Profile management
- 📖 **[API_DOCUMENTATION.md](./API_DOCUMENTATION.md)** - Complete API reference
- 📖 **[DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md)** - Database structure

### **Implementation Guides:**
- 📖 **[LEADERBOARD_IMPLEMENTATION_COMPLETE.md](./LEADERBOARD_IMPLEMENTATION_COMPLETE.md)** - Latest implementation
- 📖 **[TABUNG_SAMPAH_API.md](./TABUNG_SAMPAH_API.md)** - Waste deposit API
- 📖 **[CORS_FIX_GUIDE.md](./CORS_FIX_GUIDE.md)** - Frontend integration
- 📖 **[FIX_404_ERRORS.md](./FIX_404_ERRORS.md)** - Common issues

## 🚀 **Quick Start**

### **1. Installation**

```bash
# Clone repository
git clone <repository-url>
cd mendaur-api

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### **2. Database Setup**

```bash
# Configure .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mendaur_api
DB_USERNAME=root
DB_PASSWORD=

# Run migrations and seeders
php artisan migrate:fresh --seed
```

### **3. Start Server**

```bash
php artisan serve
# Server: http://127.0.0.1:8000
```

### **4. Test API**

```bash
# Test login
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"adib@example.com","password":"password"}'

# Get leaderboard
curl http://127.0.0.1:8000/api/dashboard/leaderboard
```

## 🎮 **Gamification Features**

### **🏆 Badges & Rewards**

| Badge | Requirement | Bonus Points |
|-------|-------------|--------------|
| 🌱 Pemula Peduli | 1 deposit | +50 |
| ♻️ Eco Warrior | 5 deposits | +100 |
| 🦸 Green Hero | 10 deposits | +200 |
| 🌍 Planet Saver | 25 deposits | +500 |
| 🥉 Bronze Collector | 100 points | +100 |
| 🥈 Silver Collector | 300 points | +200 |
| 🥇 Gold Collector | 600 points | +400 |

### **📈 Level System**

| Level | Points Required |
|-------|----------------|
| 🌱 Pemula | 0 - 99 |
| 🥉 Bronze | 100 - 299 |
| 🥈 Silver | 300 - 599 |
| 🥇 Gold | 600 - 999 |
| 💎 Platinum | 1000+ |

### **🏅 Leaderboard Types**

- **By Points** - Rank by total points earned
- **By Deposits** - Rank by waste deposit count
- **By Badges** - Rank by badge achievements

## 🔌 **API Endpoints**

### **Authentication**
```
POST /api/login          - User login
POST /api/register       - User registration
POST /api/logout         - User logout
```

### **Badges & Rewards**
```
GET  /api/badges                        - List all badges
GET  /api/users/{id}/badges             - User's unlocked badges
GET  /api/users/{id}/badge-progress     - Progress tracking
POST /api/users/{id}/check-badges       - Manual badge check
```

### **Leaderboard**
```
GET /api/dashboard/leaderboard              - Top 10 by points
GET /api/dashboard/leaderboard?type=setor   - Top by deposits
GET /api/dashboard/leaderboard?type=badge   - Top by badges
GET /api/dashboard/leaderboard?limit=5      - Custom limit
```

### **Dashboard**
```
GET /api/dashboard/stats/{userId}    - User statistics
GET /api/dashboard/global-stats      - Platform metrics
```

### **Waste Management**
```
POST /api/tabung-sampah                - Submit waste deposit
POST /api/tabung-sampah/{id}/approve   - Approve deposit (auto-checks badges)
POST /api/tabung-sampah/{id}/reject    - Reject deposit
GET  /api/users/{id}/tabung-sampah     - Deposit history
```

### **User Profile**
```
GET  /api/users/{id}                - User profile
PUT  /api/users/{id}                - Update profile
POST /api/users/{id}/update-photo   - Update photo
GET  /api/users/{id}/aktivitas      - Activity history
```

## 🔄 **How It Works**

### **Badge Reward Flow:**

```
User deposits waste (5kg)
        ↓
Admin approves deposit
        ↓
User gets base points (15 points)
        ↓
System checks ALL badge requirements
        ↓
User unlocks "Pemula Peduli" badge
        ↓
System awards BONUS points (+50)
        ↓
Creates notification & activity log
        ↓
User's total: 65 points (15 + 50)
```

## 🗄️ **Database Tables**

- `users` - User accounts and stats
- `badges` - Badge definitions with rewards
- `user_badges` - User badge unlocks
- `tabung_sampah` - Waste deposits
- `jenis_sampah` - Waste types
- `transaksi` - Point transactions
- `log_aktivitas` - Activity logging
- `notifikasi` - User notifications
- `jadwal_penyetoran` - Pickup schedules
- `produk` - Reward products
- `artikel` - Educational articles

## 🧪 **Testing**

### **Test Users (Password: `password`):**

| Name | Email | Points | Deposits | Level | Badges |
|------|-------|--------|----------|-------|--------|
| Adib Surya | adib@example.com | 150 | 5 | Bronze | 3 |
| Siti Aminah | siti@example.com | 300 | 12 | Silver | 5 |
| Budi Santoso | budi@example.com | 50 | 2 | Pemula | 1 |

### **Run Tests:**

```bash
# Test badge system
curl http://127.0.0.1:8000/api/badges

# Test leaderboard
curl http://127.0.0.1:8000/api/dashboard/leaderboard

# Test user stats
curl http://127.0.0.1:8000/api/dashboard/stats/1

# Test badge progress
curl http://127.0.0.1:8000/api/users/1/badge-progress
```

## 📱 **Frontend Integration**

React frontend project: `../Mendaur-TA` (localhost:5173)

### **Example Usage:**

```javascript
// Fetch leaderboard
const leaders = await fetch('http://127.0.0.1:8000/api/dashboard/leaderboard')
  .then(res => res.json());

// Display with medals
leaders.data.map(user => (
  <div>
    <span>{user.rank === 1 ? '🥇' : user.rank === 2 ? '🥈' : user.rank === 3 ? '🥉' : `#${user.rank}`}</span>
    <span>{user.nama}</span>
    <span>{user.total_poin} points</span>
    <span>{user.badge_count} badges</span>
  </div>
));
```

## 🛠️ **Tech Stack**

- **Framework**: Laravel 11
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **API**: RESTful JSON API
- **CORS**: Configured for frontend integration
- **PHP**: 8.2+

## 📊 **System Status**

### **✅ Implemented:**
- [x] Complete gamification system
- [x] Badge rewards with auto-checking
- [x] Multi-type leaderboard
- [x] Level progression
- [x] Activity logging
- [x] Notification system
- [x] Profile management
- [x] Waste management
- [x] Dashboard analytics
- [x] Complete documentation

### **🚀 Ready For:**
- [x] Frontend integration
- [x] Production deployment
- [x] User testing
- [x] Feature expansion

## 🤝 **Contributing**

See implementation guides in documentation for detailed information on system architecture and API usage.

## 📄 **License**

Built on Laravel framework - MIT License

---

## ⚡ **About Laravel**

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
