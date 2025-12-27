# MENDAUR API - COMPLETE ENDPOINT REFERENCE

**Updated:** December 22, 2025  
**API Version:** 1.0  
**Status:** Production Ready (100% Compliance)

## Table of Contents
1. [Authentication](#authentication)
2. [Notification System](#1-notification-system)
3. [User Activity Logs](#2-user-activity-logs)
4. [Badge Management](#3-badge-management)
5. [Database Backup](#4-database-backup)
6. [Response Formats](#response-formats)
7. [Error Codes](#error-codes)
8. [Rate Limiting](#rate-limiting)

---

## Authentication

All endpoints (except login/register) require Bearer token authentication.

### Headers Required
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Getting Token
```bash
POST /api/login
{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "success": true,
  "data": {
    "token": "xxx",
    "user": {...}
  }
}
```

Store token in `localStorage.getItem('token')`

---

## 1. NOTIFICATION SYSTEM

### List Notifications
```http
GET /api/notifications?per_page=20

Authorization: Bearer {token}

Response (200 OK):
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "notifikasi_id": 1,
        "user_id": 5,
        "judul": "Approval Success",
        "pesan": "Your withdrawal has been approved",
        "tipe": "success",
        "is_read": false,
        "related_id": null,
        "related_type": null,
        "created_at": "2025-12-22T11:30:00Z",
        "updated_at": "2025-12-22T11:30:00Z"
      }
    ],
    "total": 15,
    "per_page": 20,
    "last_page": 1
  }
}
```

### Get Unread Count
```http
GET /api/notifications/unread-count

Authorization: Bearer {token}

Response (200 OK):
{
  "status": "success",
  "data": {
    "unread_count": 3
  }
}
```

### Get Unread Notifications
```http
GET /api/notifications/unread

Authorization: Bearer {token}

Response (200 OK):
{
  "status": "success",
  "data": [...],
  "count": 3
}
```

### View Single Notification
```http
GET /api/notifications/{id}

Authorization: Bearer {token}

Response (200 OK):
{
  "status": "success",
  "data": {
    "notifikasi_id": 1,
    "user_id": 5,
    "judul": "Approval Success",
    ...
  }
}

Response (404 Not Found):
{
  "status": "error",
  "message": "Notification not found"
}
```

### Mark as Read
```http
PATCH /api/notifications/{id}/read

Authorization: Bearer {token}

Response (200 OK):
{
  "status": "success",
  "message": "Notification marked as read",
  "data": {...}
}
```

### Mark All as Read
```http
PATCH /api/notifications/mark-all-read

Authorization: Bearer {token}

Response (200 OK):
{
  "status": "success",
  "message": "All notifications marked as read"
}
```

### Delete Notification
```http
DELETE /api/notifications/{id}

Authorization: Bearer {token}

Response (200 OK):
{
  "status": "success",
  "message": "Notification deleted"
}
```

### Create Notification (Admin Only)
```http
POST /api/notifications/create

Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 5,
  "judul": "Important Update",
  "pesan": "You have an important update",
  "tipe": "info",
  "related_id": null,
  "related_type": null
}

Response (201 Created):
{
  "status": "success",
  "message": "Notification created",
  "data": {...}
}

Response (403 Forbidden):
{
  "status": "error",
  "message": "Only admins can create notifications"
}
```

---

## 2. USER ACTIVITY LOGS

### Get User Activity Logs
```http
GET /api/admin/users/{userId}/activity-logs?per_page=20

Authorization: Bearer {token}
Admin+ role required

Response (200 OK):
{
  "status": "success",
  "data": {
    "user": {
      "user_id": 5,
      "nama": "John Doe",
      "email": "john@example.com"
    },
    "activity_logs": {
      "current_page": 1,
      "data": [
        {
          "log_user_activity_id": 1,
          "user_id": 5,
          "tipe_aktivitas": "setor_sampah",
          "deskripsi": "Setoran sampah plastik 5kg",
          "poin_perubahan": 50,
          "tanggal": "2025-12-22T11:30:00Z"
        }
      ],
      "total": 25
    }
  }
}

Response (403 Forbidden):
{
  "status": "error",
  "message": "Unauthorized. Admin access required."
}
```

### Get All Activity Logs
```http
GET /api/admin/activity-logs?per_page=50&user_id=5&activity_type=setor_sampah&date_from=2025-12-01&date_to=2025-12-31

Authorization: Bearer {token}
Admin+ role required

Query Parameters:
- per_page: number (default 50)
- user_id: number (optional)
- activity_type: string (optional) - setor_sampah, tukar_poin, badge_unlock, poin_bonus, level_up
- date_from: date YYYY-MM-DD (optional)
- date_to: date YYYY-MM-DD (optional)

Response (200 OK):
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [...]
  }
}
```

### Get Specific Log Entry
```http
GET /api/admin/activity-logs/{logId}

Authorization: Bearer {token}
Admin+ role required

Response (200 OK):
{
  "status": "success",
  "data": {
    "log_user_activity_id": 1,
    "user": {
      "user_id": 5,
      "nama": "John Doe"
    },
    "tipe_aktivitas": "setor_sampah",
    "deskripsi": "Setoran sampah plastik 5kg",
    "poin_perubahan": 50,
    "tanggal": "2025-12-22T11:30:00Z"
  }
}
```

### Get Activity Statistics
```http
GET /api/admin/activity-logs/stats/overview?date_from=2025-12-01&date_to=2025-12-31

Authorization: Bearer {token}
Admin+ role required

Response (200 OK):
{
  "status": "success",
  "data": {
    "total_activities": 150,
    "activity_distribution": [
      {
        "tipe_aktivitas": "setor_sampah",
        "count": 75
      },
      {
        "tipe_aktivitas": "tukar_poin",
        "count": 50
      },
      {
        "tipe_aktivitas": "badge_unlock",
        "count": 25
      }
    ],
    "total_points_changed": 5000
  }
}
```

### Export Activity Logs to CSV
```http
GET /api/admin/activity-logs/export/csv?user_id=5&date_from=2025-12-01&date_to=2025-12-31

Authorization: Bearer {token}
Admin+ role required

Response (200 OK): CSV file download
Content-Type: text/csv
Content-Disposition: attachment; filename="activity_logs_2025-12-22_113000.csv"

Log ID,User ID,User Name,Activity Type,Description,Points Changed,Date
1,5,John Doe,setor_sampah,"Setoran sampah plastik 5kg",50,2025-12-22 11:30:00
2,5,John Doe,tukar_poin,"Tukar poin untuk produk",−100,2025-12-22 12:00:00
```

---

## 3. BADGE MANAGEMENT

### List Badges (Admin)
```http
GET /api/admin/badges

Authorization: Bearer {token}
Admin+ role required

Response (200 OK):
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "badge_id": 1,
        "nama": "Green Guardian",
        "deskripsi": "Recycle 100kg of waste",
        "poin_diperlukan": 1000,
        "users_count": 45,
        "created_at": "2025-12-22T11:30:00Z"
      }
    ],
    "total": 15
  }
}
```

### Get Badge Details (Admin)
```http
GET /api/admin/badges/{badgeId}

Authorization: Bearer {token}
Admin+ role required

Response (200 OK):
{
  "status": "success",
  "data": {
    "badge_id": 1,
    "nama": "Green Guardian",
    "deskripsi": "Recycle 100kg of waste",
    "poin_diperlukan": 1000,
    "users": [
      {
        "user_id": 5,
        "nama": "John Doe",
        "earned_at": "2025-12-15T10:00:00Z"
      }
    ]
  }
}
```

### Create Badge (Admin)
```http
POST /api/admin/badges

Authorization: Bearer {token}
Admin+ role required
Content-Type: multipart/form-data

Form Data:
- nama: string (required)
- deskripsi: string (optional)
- gambar: file (optional)
- poin_diperlukan: number (required)

Response (201 Created):
{
  "status": "success",
  "message": "Badge created",
  "data": {
    "badge_id": 16,
    "nama": "New Badge",
    ...
  }
}
```

### Update Badge (Admin)
```http
PUT /api/admin/badges/{badgeId}

Authorization: Bearer {token}
Admin+ role required

{
  "nama": "Updated Badge Name",
  "poin_diperlukan": 1500
}

Response (200 OK):
{
  "status": "success",
  "data": {...}
}
```

### Delete Badge (Admin)
```http
DELETE /api/admin/badges/{badgeId}

Authorization: Bearer {token}
Admin+ role required

Response (200 OK):
{
  "status": "success",
  "message": "Badge deleted"
}
```

### Assign Badge to User (Admin)
```http
POST /api/admin/badges/{badgeId}/assign

Authorization: Bearer {token}
Admin+ role required

{
  "user_id": 5
}

Response (200 OK):
{
  "status": "success",
  "message": "Badge assigned to user",
  "data": {
    "badge_id": 1,
    "user_id": 5,
    "assigned_at": "2025-12-22T11:30:00Z"
  }
}
```

### Revoke Badge from User (Admin)
```http
POST /api/admin/badges/{badgeId}/revoke

Authorization: Bearer {token}
Admin+ role required

{
  "user_id": 5
}

Response (200 OK):
{
  "status": "success",
  "message": "Badge revoked from user"
}
```

### Get Users with Badge (Admin)
```http
GET /api/admin/badges/{badgeId}/users

Authorization: Bearer {token}
Admin+ role required

Response (200 OK):
{
  "status": "success",
  "data": [
    {
      "user_id": 5,
      "nama": "John Doe",
      "email": "john@example.com",
      "earned_at": "2025-12-15T10:00:00Z"
    }
  ]
}
```

---

## 4. DATABASE BACKUP

### Create Backup (Superadmin Only)
```http
POST /api/superadmin/backup

Authorization: Bearer {token}
Superadmin role required (level 3)

Response (200 OK):
{
  "success": true,
  "message": "Database backup berhasil dibuat",
  "data": {
    "filename": "mendaur_backup_2025-12-22_113000.sql",
    "path": "/storage/backups/mendaur_backup_2025-12-22_113000.sql",
    "database": "mendaur",
    "file_size_mb": 45.23,
    "created_at": "2025-12-22T11:30:00Z",
    "timestamp": "2025-12-22_113000"
  }
}

Response (403 Forbidden):
{
  "success": false,
  "message": "Unauthorized - Superadmin role required"
}
```

### List Backups (Superadmin Only)
```http
GET /api/superadmin/backups

Authorization: Bearer {token}
Superadmin role required (level 3)

Response (200 OK):
{
  "success": true,
  "data": [
    {
      "filename": "mendaur_backup_2025-12-22_113000.sql",
      "size_mb": 45.23,
      "created_at": "2025-12-22 11:30:00"
    },
    {
      "filename": "mendaur_backup_2025-12-21_100000.sql",
      "size_mb": 42.15,
      "created_at": "2025-12-21 10:00:00"
    }
  ],
  "count": 2
}
```

### Delete Backup (Superadmin Only)
```http
DELETE /api/superadmin/backups/{filename}

Authorization: Bearer {token}
Superadmin role required (level 3)

Response (200 OK):
{
  "success": true,
  "message": "Backup berhasil dihapus"
}

Response (404 Not Found):
{
  "success": false,
  "message": "Backup file tidak ditemukan"
}
```

---

## Response Formats

### Success Response (2xx)
```json
{
  "status": "success",
  "message": "Optional success message",
  "data": {
    "key": "value"
  }
}
```

### Paginated Response
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [...],
    "first_page_url": "/api/endpoint?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "/api/endpoint?page=5",
    "links": [...],
    "next_page_url": "/api/endpoint?page=2",
    "path": "/api/endpoint",
    "per_page": 20,
    "prev_page_url": null,
    "to": 20,
    "total": 100
  }
}
```

### Error Response (4xx, 5xx)
```json
{
  "status": "error",
  "message": "Error description",
  "error": "Optional error details"
}
```

### Validation Error Response
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"],
    "another_field": ["Error message 1", "Error message 2"]
  }
}
```

---

## Error Codes

| Code | Message | Solution |
|------|---------|----------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request format or parameters |
| 401 | Unauthorized | Token missing, invalid, or expired - login required |
| 403 | Forbidden | Insufficient permissions for this action |
| 404 | Not Found | Resource does not exist |
| 422 | Validation Error | Request validation failed |
| 429 | Too Many Requests | Rate limit exceeded, wait before retrying |
| 500 | Internal Server Error | Server error, try again later |
| 503 | Service Unavailable | Server temporarily unavailable |

---

## Rate Limiting

### Limits
- **Public endpoints:** 100 requests per hour per IP
- **Authenticated endpoints:** 1000 requests per hour per user
- **Admin endpoints:** 500 requests per hour per admin

### Headers
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1703241000
```

### Exceeding Limit
```json
{
  "status": "error",
  "message": "Too Many Requests",
  "retry_after": 3600
}
```

---

## Summary of Changes (December 22, 2025)

### New Features (4 Total)
✅ Notification System (8 endpoints)
✅ User Activity Logs (5 endpoints)
✅ Badge Management Authorization (8 endpoints moved to admin level)
✅ Database Backup Function (3 endpoints)

### Compliance
- **Before:** 89% (51/57 permissions)
- **After:** 100% (57/57 permissions)
- **Total New Endpoints:** 24 (16 new + 8 moved)

### Controllers
- **New:** NotificationController, ActivityLogController
- **Extended:** SystemSettingsController
- **Updated:** Badge routes moved to admin level

### Status
✅ All endpoints tested and verified
✅ All authorization checks in place
✅ Ready for production deployment
✅ No uncommitted changes
✅ Pushed to GitHub

---

## Support

For issues or questions:
1. Check this documentation
2. Review error messages carefully
3. Verify your authorization token
4. Check user role and permissions
5. Contact API support team

**Last Updated:** December 22, 2025  
**API Version:** 1.0  
**Status:** Production Ready
