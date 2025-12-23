/**
 * MENDAUR API - FRONTEND IMPLEMENTATION GUIDE
 * ============================================
 *
 * Complete guide for integrating the 4 new features into your frontend
 * All endpoints are production-ready and tested
 *
 * API Base URL: http://localhost:8000/api
 * Authentication: Bearer token via Sanctum
 *
 * Updated: December 22, 2025
 */

// ============================================================================
// 1. NOTIFICATION SYSTEM
// ============================================================================

/**
 * Get All Notifications
 *
 * Endpoint: GET /api/notifications
 * Auth: Required (any user)
 * Permissions: view_notifications (nasabah level+)
 *
 * Query Parameters:
 *   - per_page: number (default: 20)
 */
async function getNotifications(perPage = 20) {
  try {
    const response = await fetch(
      `/api/notifications?per_page=${perPage}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    );

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data.data; // Returns paginated notifications with meta
  } catch (error) {
    console.error('Failed to fetch notifications:', error);
    throw error;
  }
}

/**
 * Get Unread Notifications Count
 *
 * Endpoint: GET /api/notifications/unread-count
 * Auth: Required (any user)
 *
 * Returns: { unread_count: number }
 */
async function getUnreadCount() {
  try {
    const response = await fetch(
      `/api/notifications/unread-count`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data.data.unread_count;
  } catch (error) {
    console.error('Failed to fetch unread count:', error);
    throw error;
  }
}

/**
 * Get Unread Notifications Only
 *
 * Endpoint: GET /api/notifications/unread
 * Auth: Required (any user)
 *
 * Returns: Array of unread notifications with count
 */
async function getUnreadNotifications() {
  try {
    const response = await fetch(
      `/api/notifications/unread`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return {
      notifications: data.data,
      count: data.count
    };
  } catch (error) {
    console.error('Failed to fetch unread notifications:', error);
    throw error;
  }
}

/**
 * View Single Notification
 *
 * Endpoint: GET /api/notifications/{id}
 * Auth: Required (any user)
 *
 * Parameters:
 *   - id: notification ID
 */
async function getNotification(notificationId) {
  try {
    const response = await fetch(
      `/api/notifications/${notificationId}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Failed to fetch notification:', error);
    throw error;
  }
}

/**
 * Mark Notification as Read
 *
 * Endpoint: PATCH /api/notifications/{id}/read
 * Auth: Required (any user)
 *
 * Parameters:
 *   - id: notification ID
 *
 * Returns: Updated notification object
 */
async function markNotificationAsRead(notificationId) {
  try {
    const response = await fetch(
      `/api/notifications/${notificationId}/read`,
      {
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Failed to mark notification as read:', error);
    throw error;
  }
}

/**
 * Mark All Notifications as Read
 *
 * Endpoint: PATCH /api/notifications/mark-all-read
 * Auth: Required (any user)
 *
 * Returns: { status, message }
 */
async function markAllNotificationsAsRead() {
  try {
    const response = await fetch(
      `/api/notifications/mark-all-read`,
      {
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Failed to mark all as read:', error);
    throw error;
  }
}

/**
 * Delete Notification
 *
 * Endpoint: DELETE /api/notifications/{id}
 * Auth: Required (any user)
 *
 * Parameters:
 *   - id: notification ID
 */
async function deleteNotification(notificationId) {
  try {
    const response = await fetch(
      `/api/notifications/${notificationId}`,
      {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Failed to delete notification:', error);
    throw error;
  }
}

/**
 * Create Notification (Admin Only)
 *
 * Endpoint: POST /api/notifications/create
 * Auth: Required (admin+ only)
 *
 * Body:
 *   - user_id: number (required) - Target user
 *   - judul: string (required) - Notification title
 *   - pesan: string (required) - Notification message
 *   - tipe: string (optional) - Type (e.g., "info", "warning", "success")
 *   - related_id: number (optional) - Related entity ID
 *   - related_type: string (optional) - Related entity type
 */
async function createNotification(notificationData) {
  try {
    const response = await fetch(
      `/api/notifications/create`,
      {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(notificationData)
      }
    );

    const data = await response.json();
    if (response.status === 201 || data.status === 'success') {
      return data.data;
    } else {
      throw new Error(data.message || 'Failed to create notification');
    }
  } catch (error) {
    console.error('Failed to create notification:', error);
    throw error;
  }
}

// ============================================================================
// 2. USER ACTIVITY LOGS
// ============================================================================

/**
 * Get User Activity Logs
 *
 * Endpoint: GET /api/admin/users/{userId}/activity-logs
 * Auth: Required (admin+ only)
 *
 * Parameters:
 *   - userId: number - Target user ID
 *   - per_page: number (optional, default: 20)
 */
async function getUserActivityLogs(userId, perPage = 20) {
  try {
    const response = await fetch(
      `/api/admin/users/${userId}/activity-logs?per_page=${perPage}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data.data; // Returns user info + paginated activity logs
  } catch (error) {
    console.error('Failed to fetch user activity logs:', error);
    throw error;
  }
}

/**
 * Get All Activity Logs (System Wide)
 *
 * Endpoint: GET /api/admin/activity-logs
 * Auth: Required (admin+ only)
 *
 * Query Parameters:
 *   - per_page: number (default: 50)
 *   - user_id: number (filter by user)
 *   - activity_type: string (filter by type)
 *   - date_from: date (filter from date YYYY-MM-DD)
 *   - date_to: date (filter to date YYYY-MM-DD)
 */
async function getAllActivityLogs(filters = {}) {
  try {
    const params = new URLSearchParams({
      per_page: filters.perPage || 50,
      ...(filters.userId && { user_id: filters.userId }),
      ...(filters.activityType && { activity_type: filters.activityType }),
      ...(filters.dateFrom && { date_from: filters.dateFrom }),
      ...(filters.dateTo && { date_to: filters.dateTo })
    });

    const response = await fetch(
      `/api/admin/activity-logs?${params.toString()}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data.data; // Returns paginated activity logs
  } catch (error) {
    console.error('Failed to fetch activity logs:', error);
    throw error;
  }
}

/**
 * Get Specific Activity Log Entry
 *
 * Endpoint: GET /api/admin/activity-logs/{logId}
 * Auth: Required (admin+ only)
 *
 * Parameters:
 *   - logId: number - Activity log ID
 */
async function getActivityLog(logId) {
  try {
    const response = await fetch(
      `/api/admin/activity-logs/${logId}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Failed to fetch activity log:', error);
    throw error;
  }
}

/**
 * Get Activity Statistics
 *
 * Endpoint: GET /api/admin/activity-logs/stats/overview
 * Auth: Required (admin+ only)
 *
 * Query Parameters:
 *   - date_from: date (optional)
 *   - date_to: date (optional)
 *
 * Returns: Activity distribution, total activities, total points changed
 */
async function getActivityStats(dateFrom = null, dateTo = null) {
  try {
    const params = new URLSearchParams();
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);

    const response = await fetch(
      `/api/admin/activity-logs/stats/overview?${params.toString()}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Failed to fetch activity stats:', error);
    throw error;
  }
}

/**
 * Export Activity Logs to CSV
 *
 * Endpoint: GET /api/admin/activity-logs/export/csv
 * Auth: Required (admin+ only)
 *
 * Query Parameters:
 *   - user_id: number (optional)
 *   - date_from: date (optional)
 *   - date_to: date (optional)
 *
 * Returns: CSV file download
 */
async function exportActivityLogsToCSV(filters = {}) {
  try {
    const params = new URLSearchParams();
    if (filters.userId) params.append('user_id', filters.userId);
    if (filters.dateFrom) params.append('date_from', filters.dateFrom);
    if (filters.dateTo) params.append('date_to', filters.dateTo);

    const response = await fetch(
      `/api/admin/activity-logs/export/csv?${params.toString()}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      }
    );

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    // Create blob and trigger download
    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `activity_logs_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);

    return { status: 'success', message: 'CSV exported successfully' };
  } catch (error) {
    console.error('Failed to export activity logs:', error);
    throw error;
  }
}

// ============================================================================
// 3. BADGE MANAGEMENT (ADMIN LEVEL)
// ============================================================================

/**
 * List All Badges (Admin)
 *
 * Endpoint: GET /api/admin/badges
 * Auth: Required (admin+ only)
 *
 * Returns: Paginated list of badges with user counts
 */
async function listBadges() {
  try {
    const response = await fetch(
      `/api/admin/badges`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data.data; // Returns paginated badges
  } catch (error) {
    console.error('Failed to fetch badges:', error);
    throw error;
  }
}

/**
 * Get Badge Details (Admin)
 *
 * Endpoint: GET /api/admin/badges/{badgeId}
 * Auth: Required (admin+ only)
 */
async function getBadgeDetails(badgeId) {
  try {
    const response = await fetch(
      `/api/admin/badges/${badgeId}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Failed to fetch badge details:', error);
    throw error;
  }
}

/**
 * Create New Badge (Admin)
 *
 * Endpoint: POST /api/admin/badges
 * Auth: Required (admin+ only)
 *
 * Body:
 *   - nama: string (required)
 *   - deskripsi: string (optional)
 *   - gambar: file (optional)
 *   - poin_diperlukan: number (required)
 */
async function createBadge(badgeData) {
  try {
    const formData = new FormData();
    formData.append('nama', badgeData.nama);
    if (badgeData.deskripsi) formData.append('deskripsi', badgeData.deskripsi);
    if (badgeData.gambar) formData.append('gambar', badgeData.gambar);
    formData.append('poin_diperlukan', badgeData.poin_diperlukan);

    const response = await fetch(
      `/api/admin/badges`,
      {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: formData
      }
    );

    const data = await response.json();
    if (data.status === 'success') {
      return data.data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Failed to create badge:', error);
    throw error;
  }
}

/**
 * Update Badge (Admin)
 *
 * Endpoint: PUT /api/admin/badges/{badgeId}
 * Auth: Required (admin+ only)
 */
async function updateBadge(badgeId, badgeData) {
  try {
    const response = await fetch(
      `/api/admin/badges/${badgeId}`,
      {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(badgeData)
      }
    );

    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Failed to update badge:', error);
    throw error;
  }
}

/**
 * Delete Badge (Admin)
 *
 * Endpoint: DELETE /api/admin/badges/{badgeId}
 * Auth: Required (admin+ only)
 */
async function deleteBadge(badgeId) {
  try {
    const response = await fetch(
      `/api/admin/badges/${badgeId}`,
      {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Failed to delete badge:', error);
    throw error;
  }
}

/**
 * Assign Badge to User (Admin)
 *
 * Endpoint: POST /api/admin/badges/{badgeId}/assign
 * Auth: Required (admin+ only)
 *
 * Body:
 *   - user_id: number (required)
 */
async function assignBadgeToUser(badgeId, userId) {
  try {
    const response = await fetch(
      `/api/admin/badges/${badgeId}/assign`,
      {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_id: userId })
      }
    );

    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Failed to assign badge:', error);
    throw error;
  }
}

/**
 * Revoke Badge from User (Admin)
 *
 * Endpoint: POST /api/admin/badges/{badgeId}/revoke
 * Auth: Required (admin+ only)
 *
 * Body:
 *   - user_id: number (required)
 */
async function revokeBadgeFromUser(badgeId, userId) {
  try {
    const response = await fetch(
      `/api/admin/badges/${badgeId}/revoke`,
      {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_id: userId })
      }
    );

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Failed to revoke badge:', error);
    throw error;
  }
}

/**
 * Get Users with Badge (Admin)
 *
 * Endpoint: GET /api/admin/badges/{badgeId}/users
 * Auth: Required (admin+ only)
 */
async function getUsersWithBadge(badgeId) {
  try {
    const response = await fetch(
      `/api/admin/badges/${badgeId}/users`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Failed to get users with badge:', error);
    throw error;
  }
}

// ============================================================================
// 4. DATABASE BACKUP (SUPERADMIN ONLY)
// ============================================================================

/**
 * Create Database Backup
 *
 * Endpoint: POST /api/superadmin/backup
 * Auth: Required (superadmin only)
 *
 * Returns: Backup file details (filename, path, size, created_at)
 */
async function createDatabaseBackup() {
  try {
    const response = await fetch(
      `/api/superadmin/backup`,
      {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    if (data.success) {
      return data.data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Failed to create backup:', error);
    throw error;
  }
}

/**
 * List All Backups
 *
 * Endpoint: GET /api/superadmin/backups
 * Auth: Required (superadmin only)
 *
 * Returns: Array of backup files with size and creation time
 */
async function listBackups() {
  try {
    const response = await fetch(
      `/api/superadmin/backups`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return {
      backups: data.data,
      count: data.count
    };
  } catch (error) {
    console.error('Failed to fetch backups:', error);
    throw error;
  }
}

/**
 * Delete Backup
 *
 * Endpoint: DELETE /api/superadmin/backups/{filename}
 * Auth: Required (superadmin only)
 *
 * Parameters:
 *   - filename: string - Name of the backup file
 */
async function deleteBackup(filename) {
  try {
    const response = await fetch(
      `/api/superadmin/backups/${filename}`,
      {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      }
    );

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Failed to delete backup:', error);
    throw error;
  }
}

// ============================================================================
// REACT COMPONENT EXAMPLES
// ============================================================================

/**
 * Example React Hook for Notifications
 */
import React, { useState, useEffect } from 'react';

function NotificationsComponent() {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadNotifications();
    loadUnreadCount();
    // Poll for new notifications every 30 seconds
    const interval = setInterval(() => {
      loadNotifications();
      loadUnreadCount();
    }, 30000);
    return () => clearInterval(interval);
  }, []);

  const loadNotifications = async () => {
    setLoading(true);
    try {
      const data = await getNotifications(20);
      setNotifications(data);
    } catch (error) {
      console.error('Error loading notifications:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadUnreadCount = async () => {
    try {
      const count = await getUnreadCount();
      setUnreadCount(count);
    } catch (error) {
      console.error('Error loading unread count:', error);
    }
  };

  const handleMarkAsRead = async (notificationId) => {
    try {
      await markNotificationAsRead(notificationId);
      loadNotifications();
      loadUnreadCount();
    } catch (error) {
      console.error('Error marking as read:', error);
    }
  };

  const handleDelete = async (notificationId) => {
    try {
      await deleteNotification(notificationId);
      loadNotifications();
      loadUnreadCount();
    } catch (error) {
      console.error('Error deleting notification:', error);
    }
  };

  return (
    <div className="notifications">
      <h2>Notifications ({unreadCount} unread)</h2>
      {loading && <p>Loading...</p>}
      <ul>
        {notifications.map(notif => (
          <li key={notif.notifikasi_id} className={notif.is_read ? 'read' : 'unread'}>
            <h3>{notif.judul}</h3>
            <p>{notif.pesan}</p>
            <small>{new Date(notif.created_at).toLocaleString()}</small>
            <div className="actions">
              {!notif.is_read && (
                <button onClick={() => handleMarkAsRead(notif.notifikasi_id)}>
                  Mark as Read
                </button>
              )}
              <button onClick={() => handleDelete(notif.notifikasi_id)}>
                Delete
              </button>
            </div>
          </li>
        ))}
      </ul>
    </div>
  );
}

export default NotificationsComponent;

/**
 * Example React Hook for Activity Logs (Admin Dashboard)
 */
function ActivityLogsComponent() {
  const [logs, setLogs] = useState([]);
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(false);
  const [filters, setFilters] = useState({
    userId: '',
    dateFrom: '',
    dateTo: ''
  });

  const loadActivityLogs = async () => {
    setLoading(true);
    try {
      const data = await getAllActivityLogs({
        userId: filters.userId || undefined,
        dateFrom: filters.dateFrom || undefined,
        dateTo: filters.dateTo || undefined
      });
      setLogs(data);
    } catch (error) {
      console.error('Error loading activity logs:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadStats = async () => {
    try {
      const data = await getActivityStats(filters.dateFrom, filters.dateTo);
      setStats(data);
    } catch (error) {
      console.error('Error loading stats:', error);
    }
  };

  useEffect(() => {
    loadActivityLogs();
    loadStats();
  }, []);

  const handleExport = async () => {
    try {
      await exportActivityLogsToCSV(filters);
    } catch (error) {
      console.error('Error exporting logs:', error);
    }
  };

  return (
    <div className="activity-logs">
      <h2>User Activity Logs</h2>

      {/* Filters */}
      <div className="filters">
        <input
          type="number"
          placeholder="User ID"
          value={filters.userId}
          onChange={(e) => setFilters({...filters, userId: e.target.value})}
        />
        <input
          type="date"
          value={filters.dateFrom}
          onChange={(e) => setFilters({...filters, dateFrom: e.target.value})}
        />
        <input
          type="date"
          value={filters.dateTo}
          onChange={(e) => setFilters({...filters, dateTo: e.target.value})}
        />
        <button onClick={loadActivityLogs}>Search</button>
        <button onClick={handleExport}>Export CSV</button>
      </div>

      {/* Statistics */}
      {stats && (
        <div className="stats">
          <p>Total Activities: {stats.total_activities}</p>
          <p>Total Points Changed: {stats.total_points_changed}</p>
        </div>
      )}

      {/* Logs Table */}
      {loading && <p>Loading...</p>}
      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Activity Type</th>
            <th>Description</th>
            <th>Points</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          {logs.map(log => (
            <tr key={log.log_user_activity_id}>
              <td>{log.user?.nama}</td>
              <td>{log.tipe_aktivitas}</td>
              <td>{log.deskripsi}</td>
              <td>{log.poin_perubahan}</td>
              <td>{new Date(log.tanggal).toLocaleString()}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default ActivityLogsComponent;

// ============================================================================
// USAGE IN FETCH WITH ERROR HANDLING
// ============================================================================

/**
 * Example: Complete Error Handling Pattern
 */
async function apiCall(endpoint, method = 'GET', body = null) {
  try {
    const options = {
      method,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    };

    if (body) {
      options.body = JSON.stringify(body);
    }

    const response = await fetch(`/api${endpoint}`, options);

    // Handle different status codes
    if (response.status === 401) {
      // Token expired or invalid
      localStorage.removeItem('token');
      window.location.href = '/login';
      return null;
    }

    if (response.status === 403) {
      // Forbidden - insufficient permissions
      throw new Error('You do not have permission to access this resource');
    }

    if (!response.ok) {
      const data = await response.json();
      throw new Error(data.message || `HTTP ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    console.error(`API Error [${method} ${endpoint}]:`, error);
    throw error;
  }
}

// Usage:
// const data = await apiCall('/notifications', 'GET');
// const created = await apiCall('/notifications/create', 'POST', { user_id: 1, judul: 'Test', pesan: 'Test message' });

// ============================================================================
// ENVIRONMENT CONFIGURATION
// ============================================================================

/**
 * Create a .env file with:
 */
// VITE_API_BASE_URL=http://localhost:8000/api
// VITE_API_TIMEOUT=30000

/**
 * Or for production:
 */
// VITE_API_BASE_URL=https://api.mendaur.com/api
// VITE_API_TIMEOUT=30000

/**
 * Use in your code:
 */
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api';

// ============================================================================
// IMPLEMENTATION CHECKLIST
// ============================================================================

/**
 * ✅ FRONTEND IMPLEMENTATION CHECKLIST
 *
 * Notifications:
 * [ ] Add notification bell icon to header
 * [ ] Show unread count badge
 * [ ] List notifications in dropdown/modal
 * [ ] Implement real-time updates (polling or WebSocket)
 * [ ] Add mark as read functionality
 * [ ] Add delete functionality
 * [ ] Add notification settings (notification preferences)
 *
 * Activity Logs (Admin):
 * [ ] Create admin dashboard page
 * [ ] Show activity logs table
 * [ ] Add filtering by user, date, type
 * [ ] Add statistics display
 * [ ] Add CSV export button
 * [ ] Add pagination controls
 *
 * Badge Management (Admin):
 * [ ] Create badges management page
 * [ ] Show badges list/grid
 * [ ] Add create badge form
 * [ ] Add edit badge functionality
 * [ ] Add delete confirmation
 * [ ] Add assign/revoke to user functionality
 * [ ] Show users with each badge
 *
 * Database Backup (Superadmin):
 * [ ] Create system settings page
 * [ ] Show backup section
 * [ ] Add create backup button with confirmation
 * [ ] Show backup history/list
 * [ ] Add delete backup functionality
 * [ ] Show backup file sizes and creation dates
 * [ ] Add progress indicator for backup creation
 *
 * Authentication & Error Handling:
 * [ ] Ensure all API calls include Authorization header
 * [ ] Handle 401 (unauthorized) - redirect to login
 * [ ] Handle 403 (forbidden) - show permission denied message
 * [ ] Handle 404 (not found) - show not found message
 * [ ] Handle network errors gracefully
 * [ ] Show loading states
 * [ ] Show success/error toast notifications
 *
 * Testing:
 * [ ] Test all endpoints with different user roles
 * [ ] Test error scenarios
 * [ ] Test pagination
 * [ ] Test filtering
 * [ ] Test CSV export
 * [ ] Test real-time updates
 */

// ============================================================================
// SUPPORT & TROUBLESHOOTING
// ============================================================================

/**
 * Common Issues & Solutions:
 *
 * 1. "Unauthorized" Error (401)
 *    - Token expired: User needs to login again
 *    - Invalid token: Clear localStorage and login
 *
 * 2. "Forbidden" Error (403)
 *    - Insufficient permissions: User role doesn't have access
 *    - Check user role: Admin routes need level 2+, Superadmin need level 3
 *
 * 3. CORS Errors
 *    - Ensure API server has CORS enabled
 *    - Check API_BASE_URL is correct
 *
 * 4. Notifications not updating
 *    - Check polling interval (default 30 seconds)
 *    - Check if token is valid
 *    - Check browser console for errors
 *
 * 5. CSV Export fails
 *    - Check file size (large exports may fail)
 *    - Check browser download settings
 *    - Try with different date range
 *
 * Contact: Your API support team
 * API Documentation: /api/docs (if available)
 * API Status: Check /api/health endpoint
 */

module.exports = {
  // Notification Functions
  getNotifications,
  getUnreadCount,
  getUnreadNotifications,
  getNotification,
  markNotificationAsRead,
  markAllNotificationsAsRead,
  deleteNotification,
  createNotification,

  // Activity Log Functions
  getUserActivityLogs,
  getAllActivityLogs,
  getActivityLog,
  getActivityStats,
  exportActivityLogsToCSV,

  // Badge Management Functions
  listBadges,
  getBadgeDetails,
  createBadge,
  updateBadge,
  deleteBadge,
  assignBadgeToUser,
  revokeBadgeFromUser,
  getUsersWithBadge,

  // Backup Functions
  createDatabaseBackup,
  listBackups,
  deleteBackup,

  // Utility
  apiCall
};
