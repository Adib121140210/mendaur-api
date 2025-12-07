
# Badge Tracking API Routes

Register these routes in your `routes/api.php` file:

```php
<?php

use App\Http\Controllers\Api\BadgeProgressController;
use Illuminate\Support\Facades\Route;

// Badge tracking routes
Route::middleware('auth:sanctum')->group(function () {
    // User badge endpoints
    Route::prefix('user/badges')->group(function () {
        Route::get('progress', [BadgeProgressController::class, 'getUserProgress']);
        Route::get('completed', [BadgeProgressController::class, 'getCompletedBadges']);
    });

    // Public badge endpoints
    Route::prefix('badges')->group(function () {
        Route::get('leaderboard', [BadgeProgressController::class, 'getLeaderboard']);
        Route::get('available', [BadgeProgressController::class, 'getAvailableBadges']);
    });

    // Admin badge endpoints
    Route::middleware('admin')->group(function () {
        Route::prefix('admin/badges')->group(function () {
            Route::get('analytics', [BadgeProgressController::class, 'getAnalytics']);
        });
    });
});
```

## API Endpoints

### 1. Get User's Badge Progress
```
GET /api/user/badges/progress
Authorization: Bearer {token}

Response:
{
  "status": "success",
  "data": {
    "user": {
      "id": 1,
      "nama": "Ahmad",
      "total_poin": 1250,
      "total_setor": 45
    },
    "summary": {
      "completed": 5,
      "incomplete": 10,
      "total_tracked": 15,
      "average_progress_percentage": 62.5,
      "almost_complete": 4,
      "total_reward_poin": 1500
    },
    "completed_badges": [...],
    "in_progress_badges": [...]
  }
}
```

### 2. Get User's Completed Badges
```
GET /api/user/badges/completed
Authorization: Bearer {token}

Response:
{
  "status": "success",
  "count": 5,
  "data": [...]
}
```

### 3. Get Leaderboard (Top Achievers)
```
GET /api/badges/leaderboard?limit=10
Authorization: Bearer {token}

Response:
{
  "status": "success",
  "count": 10,
  "data": [
    {
      "rank": 1,
      "user": {...},
      "badges_earned": 12,
      "total_reward_poin": 4500
    }
  ]
}
```

### 4. Get Available Badges
```
GET /api/badges/available
Authorization: Bearer {token}

Response:
{
  "status": "success",
  "count": 15,
  "data": [...]
}
```

### 5. Get Admin Analytics (Admin Only)
```
GET /api/admin/badges/analytics
Authorization: Bearer {admin-token}

Response:
{
  "status": "success",
  "data": {
    "total_badges": 15,
    "total_earned": 245,
    "total_users": 150,
    "users_tracking": 140,
    "average_badges_per_user": 1.63,
    "average_progress_percentage": 45.8,
    "most_earned_badges": [...],
    "rarest_badges": [...]
  }
}
```

---

**Location**: `routes/api.php`
**Type**: API Routes
**Auth**: Sanctum token
**Admin Check**: Some endpoints require admin middleware
