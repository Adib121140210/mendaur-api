<?php

/**
 * Badge Tracking System - API Testing Guide
 *
 * How to test the badge tracking system endpoints
 */

// ========================================
// STEP 1: Get Authentication Token
// ========================================

/*
First, you need to create a user and get a token:

POST /api/register
Content-Type: application/json

{
    "name": "Test User",
    "no_hp": "08123456789",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}

Response:
{
    "status": "success",
    "message": "User registered successfully",
    "data": {
        "id": 1,
        "no_hp": "08123456789",
        "email": "test@example.com",
        "name": "Test User",
        "token": "YOUR_TOKEN_HERE"
    }
}

Save the token for next requests.
*/

// ========================================
// STEP 2: Test Badge Progress Endpoint
// ========================================

/*
GET /api/user/badges/progress
Authorization: Bearer YOUR_TOKEN_HERE

Response:
{
    "status": "success",
    "data": {
        "user": {
            "id": 1,
            "no_hp": "08123456789",
            "name": "Test User",
            "poin": 500
        },
        "summary": {
            "total_badges": 15,
            "completed": 0,
            "in_progress": 15,
            "almost_complete": 2
        },
        "badges": [
            {
                "badge_id": 1,
                "name": "Eco Hero",
                "description": "Earn 1000 points",
                "type": "poin",
                "current_value": 500,
                "target_value": 1000,
                "progress_percentage": 50,
                "progress_status": "In Progress",
                "is_unlocked": false,
                "unlocked_at": null
            }
        ]
    }
}
*/

// ========================================
// STEP 3: Test Completed Badges Endpoint
// ========================================

/*
GET /api/user/badges/completed
Authorization: Bearer YOUR_TOKEN_HERE

Response:
{
    "status": "success",
    "data": {
        "user": {
            "id": 1,
            "name": "Test User"
        },
        "completed_badges": [
            {
                "badge_id": 1,
                "name": "Badge Name",
                "description": "Badge description",
                "earned_at": "2025-11-26 10:30:00",
                "reward_poin": 50
            }
        ],
        "total_completed": 0,
        "total_reward_poin": 0
    }
}
*/

// ========================================
// STEP 4: Test Leaderboard Endpoint
// ========================================

/*
GET /api/badges/leaderboard?limit=10
Authorization: Bearer YOUR_TOKEN_HERE

Response:
{
    "status": "success",
    "data": {
        "leaderboard": [
            {
                "rank": 1,
                "user_id": 2,
                "name": "Top User",
                "badges_earned": 12,
                "total_reward_poin": 1500,
                "latest_badge": "2025-11-26"
            },
            {
                "rank": 2,
                "user_id": 1,
                "name": "Test User",
                "badges_earned": 5,
                "total_reward_poin": 250,
                "latest_badge": "2025-11-25"
            }
        ],
        "your_rank": 2,
        "your_badges_earned": 5,
        "total_users": 10
    }
}
*/

// ========================================
// STEP 5: Test Available Badges Endpoint
// ========================================

/*
GET /api/badges/available
Authorization: Bearer YOUR_TOKEN_HERE

Response:
{
    "status": "success",
    "data": {
        "badges": [
            {
                "id": 1,
                "name": "Eco Hero",
                "description": "Earn 1000 points",
                "type": "poin",
                "icon_url": "http://example.com/icons/eco-hero.png",
                "requirement": 1000,
                "reward_poin": 50,
                "user_progress": {
                    "current_value": 500,
                    "target_value": 1000,
                    "progress_percentage": 50,
                    "is_unlocked": false
                }
            },
            {
                "id": 2,
                "name": "Setor Pro",
                "description": "Make 50 deposits",
                "type": "setor",
                "icon_url": "http://example.com/icons/setor-pro.png",
                "requirement": 50,
                "reward_poin": 100,
                "user_progress": {
                    "current_value": 30,
                    "target_value": 50,
                    "progress_percentage": 60,
                    "is_unlocked": false
                }
            }
        ],
        "total_badges": 15,
        "total_unlocked": 0,
        "total_in_progress": 15
    }
}
*/

// ========================================
// STEP 6: Test Admin Analytics Endpoint
// ========================================

/*
GET /api/admin/badges/analytics
Authorization: Bearer ADMIN_TOKEN_HERE

Response:
{
    "status": "success",
    "data": {
        "summary": {
            "total_badges": 15,
            "total_distributed": 45,
            "total_users": 10,
            "average_per_user": 4.5,
            "total_reward_poin_distributed": 2250
        },
        "by_type": {
            "poin": {
                "total": 20,
                "distributed": 15,
                "percentage": 75
            },
            "setor": {
                "total": 20,
                "distributed": 10,
                "percentage": 50
            },
            "kombinasi": {
                "total": 10,
                "distributed": 8,
                "percentage": 80
            },
            "special": {
                "total": 10,
                "distributed": 7,
                "percentage": 70
            },
            "ranking": {
                "total": 5,
                "distributed": 5,
                "percentage": 100
            }
        },
        "top_badges": [
            {
                "badge_id": 1,
                "name": "Eco Hero",
                "earned_count": 8,
                "percentage": 80
            },
            {
                "badge_id": 2,
                "name": "Setor Pro",
                "earned_count": 6,
                "percentage": 60
            }
        ],
        "recent_unlocks": [
            {
                "user_id": 5,
                "user_name": "User Name",
                "badge_id": 1,
                "badge_name": "Eco Hero",
                "unlocked_at": "2025-11-26 10:30:00"
            }
        ]
    }
}
*/

// ========================================
// CURL TESTING EXAMPLES
// ========================================

/*

# 1. Register User
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "no_hp": "08123456789",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# 2. Get User Progress (after getting TOKEN from registration)
curl -X GET http://127.0.0.1:8000/api/user/badges/progress \
  -H "Authorization: Bearer YOUR_TOKEN"

# 3. Get Completed Badges
curl -X GET http://127.0.0.1:8000/api/user/badges/completed \
  -H "Authorization: Bearer YOUR_TOKEN"

# 4. Get Leaderboard
curl -X GET "http://127.0.0.1:8000/api/badges/leaderboard?limit=10" \
  -H "Authorization: Bearer YOUR_TOKEN"

# 5. Get Available Badges
curl -X GET http://127.0.0.1:8000/api/badges/available \
  -H "Authorization: Bearer YOUR_TOKEN"

# 6. Get Admin Analytics
curl -X GET http://127.0.0.1:8000/api/admin/badges/analytics \
  -H "Authorization: Bearer ADMIN_TOKEN"

*/

// ========================================
// POSTMAN TESTING STEPS
// ========================================

/*

1. Create New Collection: "Badge Tracking System"

2. Create Environment Variable:
   - base_url: http://127.0.0.1:8000
   - token: (leave empty, will fill after login)
   - admin_token: (leave empty if not admin)

3. Create Requests:

   a) Register User
      - Method: POST
      - URL: {{base_url}}/api/register
      - Body (JSON):
        {
          "name": "Test User",
          "no_hp": "08123456789",
          "email": "test@example.com",
          "password": "password123",
          "password_confirmation": "password123"
        }
      - Tests: Extract token to {{token}}

   b) Get User Progress
      - Method: GET
      - URL: {{base_url}}/api/user/badges/progress
      - Headers: Authorization: Bearer {{token}}

   c) Get Completed Badges
      - Method: GET
      - URL: {{base_url}}/api/user/badges/completed
      - Headers: Authorization: Bearer {{token}}

   d) Get Leaderboard
      - Method: GET
      - URL: {{base_url}}/api/badges/leaderboard?limit=10
      - Headers: Authorization: Bearer {{token}}

   e) Get Available Badges
      - Method: GET
      - URL: {{base_url}}/api/badges/available
      - Headers: Authorization: Bearer {{token}}

   f) Get Admin Analytics
      - Method: GET
      - URL: {{base_url}}/api/admin/badges/analytics
      - Headers: Authorization: Bearer {{admin_token}}

*/

// ========================================
// PHP Testing Example
// ========================================

/*

$client = new GuzzleHttp\Client();

// 1. Register
$response = $client->post('http://127.0.0.1:8000/api/register', [
    'json' => [
        'name' => 'Test User',
        'no_hp' => '08123456789',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]
]);

$data = json_decode($response->getBody(), true);
$token = $data['data']['token'];

// 2. Get Badge Progress
$response = $client->get('http://127.0.0.1:8000/api/user/badges/progress', [
    'headers' => [
        'Authorization' => "Bearer $token"
    ]
]);

$progress = json_decode($response->getBody(), true);
echo json_encode($progress, JSON_PRETTY_PRINT);

*/

echo "Badge Tracking System - API Testing Guide\n";
echo "=========================================\n\n";
echo "1. Read the documentation above\n";
echo "2. Use curl, Postman, or PHP to test endpoints\n";
echo "3. Make sure Laravel server is running on http://127.0.0.1:8000\n";
echo "4. Get a token from /api/register first\n";
echo "5. Use token in Authorization header for protected routes\n";
?>
