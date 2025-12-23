// ============================================================================
// QUICK START GUIDE FOR FRONTEND DEVELOPMENT
// ============================================================================

/**
 * MENDAUR API - QUICK START
 *
 * This file contains copy-paste ready code snippets for integrating
 * the 4 new features into your frontend application.
 *
 * Updated: December 22, 2025
 */

// ============================================================================
// STEP 1: SETUP API BASE CONFIGURATION
// ============================================================================

// Create a new file: src/api/client.js (or api.js)

export const API_CONFIG = {
  baseURL: process.env.REACT_APP_API_URL || 'http://localhost:8000/api',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};

export class APIClient {
  constructor() {
    this.baseURL = API_CONFIG.baseURL;
  }

  async request(endpoint, options = {}) {
    const token = localStorage.getItem('token');
    const headers = {
      ...API_CONFIG.headers,
      ...(token && { 'Authorization': `Bearer ${token}` }),
      ...options.headers
    };

    const response = await fetch(`${this.baseURL}${endpoint}`, {
      ...options,
      headers
    });

    // Handle authentication errors
    if (response.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || `HTTP ${response.status}`);
    }

    return response.json();
  }

  get(endpoint, options = {}) {
    return this.request(endpoint, { ...options, method: 'GET' });
  }

  post(endpoint, data, options = {}) {
    return this.request(endpoint, {
      ...options,
      method: 'POST',
      body: JSON.stringify(data)
    });
  }

  patch(endpoint, data, options = {}) {
    return this.request(endpoint, {
      ...options,
      method: 'PATCH',
      body: JSON.stringify(data)
    });
  }

  delete(endpoint, options = {}) {
    return this.request(endpoint, { ...options, method: 'DELETE' });
  }
}

export const apiClient = new APIClient();

// ============================================================================
// STEP 2: NOTIFICATION SERVICE
// ============================================================================

// Create: src/services/notificationService.js

import { apiClient } from '../api/client';

export const notificationService = {
  // Get all notifications
  async getAll(perPage = 20) {
    return apiClient.get(`/notifications?per_page=${perPage}`);
  },

  // Get unread count
  async getUnreadCount() {
    const response = await apiClient.get('/notifications/unread-count');
    return response.data.unread_count;
  },

  // Get unread notifications
  async getUnread() {
    return apiClient.get('/notifications/unread');
  },

  // Get single notification
  async getOne(id) {
    return apiClient.get(`/notifications/${id}`);
  },

  // Mark as read
  async markAsRead(id) {
    return apiClient.patch(`/notifications/${id}/read`, {});
  },

  // Mark all as read
  async markAllAsRead() {
    return apiClient.patch('/notifications/mark-all-read', {});
  },

  // Delete notification
  async delete(id) {
    return apiClient.delete(`/notifications/${id}`);
  },

  // Create notification (admin only)
  async create(data) {
    return apiClient.post('/notifications/create', data);
  }
};

// ============================================================================
// STEP 3: ACTIVITY LOG SERVICE
// ============================================================================

// Create: src/services/activityLogService.js

import { apiClient } from '../api/client';

export const activityLogService = {
  // Get user activity logs
  async getUserLogs(userId, perPage = 20) {
    return apiClient.get(`/admin/users/${userId}/activity-logs?per_page=${perPage}`);
  },

  // Get all activity logs with filters
  async getAll(filters = {}) {
    const params = new URLSearchParams({
      per_page: filters.perPage || 50,
      ...(filters.userId && { user_id: filters.userId }),
      ...(filters.activityType && { activity_type: filters.activityType }),
      ...(filters.dateFrom && { date_from: filters.dateFrom }),
      ...(filters.dateTo && { date_to: filters.dateTo })
    });
    return apiClient.get(`/admin/activity-logs?${params}`);
  },

  // Get single log
  async getOne(id) {
    return apiClient.get(`/admin/activity-logs/${id}`);
  },

  // Get statistics
  async getStats(dateFrom, dateTo) {
    const params = new URLSearchParams();
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    return apiClient.get(`/admin/activity-logs/stats/overview?${params}`);
  },

  // Export to CSV
  async exportCSV(filters = {}) {
    const params = new URLSearchParams({
      ...(filters.userId && { user_id: filters.userId }),
      ...(filters.dateFrom && { date_from: filters.dateFrom }),
      ...(filters.dateTo && { date_to: filters.dateTo })
    });

    const response = await fetch(
      `${apiClient.baseURL}/admin/activity-logs/export/csv?${params}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      }
    );

    const blob = await response.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `activity_logs_${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    URL.revokeObjectURL(url);
  }
};

// ============================================================================
// STEP 4: BADGE SERVICE
// ============================================================================

// Create: src/services/badgeService.js

import { apiClient } from '../api/client';

export const badgeService = {
  // List all badges
  async getAll() {
    return apiClient.get('/admin/badges');
  },

  // Get badge details
  async getOne(id) {
    return apiClient.get(`/admin/badges/${id}`);
  },

  // Create badge
  async create(data) {
    const formData = new FormData();
    formData.append('nama', data.nama);
    if (data.deskripsi) formData.append('deskripsi', data.deskripsi);
    if (data.gambar) formData.append('gambar', data.gambar);
    formData.append('poin_diperlukan', data.poin_diperlukan);

    return apiClient.request('/admin/badges', {
      method: 'POST',
      body: formData,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
  },

  // Update badge
  async update(id, data) {
    return apiClient.patch(`/admin/badges/${id}`, data);
  },

  // Delete badge
  async delete(id) {
    return apiClient.delete(`/admin/badges/${id}`);
  },

  // Assign to user
  async assignToUser(badgeId, userId) {
    return apiClient.post(`/admin/badges/${badgeId}/assign`, { user_id: userId });
  },

  // Revoke from user
  async revokeFromUser(badgeId, userId) {
    return apiClient.post(`/admin/badges/${badgeId}/revoke`, { user_id: userId });
  },

  // Get users with badge
  async getUsersWithBadge(badgeId) {
    return apiClient.get(`/admin/badges/${badgeId}/users`);
  }
};

// ============================================================================
// STEP 5: BACKUP SERVICE
// ============================================================================

// Create: src/services/backupService.js

import { apiClient } from '../api/client';

export const backupService = {
  // Create backup
  async create() {
    return apiClient.post('/superadmin/backup', {});
  },

  // List backups
  async getAll() {
    return apiClient.get('/superadmin/backups');
  },

  // Delete backup
  async delete(filename) {
    return apiClient.delete(`/superadmin/backups/${filename}`);
  }
};

// ============================================================================
// STEP 6: REACT HOOKS
// ============================================================================

// Create: src/hooks/useNotifications.js

import { useState, useEffect, useCallback } from 'react';
import { notificationService } from '../services/notificationService';

export function useNotifications() {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const loadNotifications = useCallback(async () => {
    setLoading(true);
    try {
      const response = await notificationService.getAll();
      setNotifications(response.data.data);

      const count = await notificationService.getUnreadCount();
      setUnreadCount(count);

      setError(null);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, []);

  const markAsRead = useCallback(async (id) => {
    try {
      await notificationService.markAsRead(id);
      loadNotifications();
    } catch (err) {
      setError(err.message);
    }
  }, [loadNotifications]);

  const deleteNotification = useCallback(async (id) => {
    try {
      await notificationService.delete(id);
      loadNotifications();
    } catch (err) {
      setError(err.message);
    }
  }, [loadNotifications]);

  useEffect(() => {
    loadNotifications();
    const interval = setInterval(loadNotifications, 30000); // Poll every 30s
    return () => clearInterval(interval);
  }, [loadNotifications]);

  return {
    notifications,
    unreadCount,
    loading,
    error,
    markAsRead,
    deleteNotification,
    reload: loadNotifications
  };
}

// Create: src/hooks/useActivityLogs.js

import { useState, useCallback } from 'react';
import { activityLogService } from '../services/activityLogService';

export function useActivityLogs() {
  const [logs, setLogs] = useState([]);
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const loadLogs = useCallback(async (filters = {}) => {
    setLoading(true);
    try {
      const response = await activityLogService.getAll(filters);
      setLogs(response.data.data);
      setError(null);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, []);

  const loadStats = useCallback(async (dateFrom, dateTo) => {
    try {
      const response = await activityLogService.getStats(dateFrom, dateTo);
      setStats(response.data);
    } catch (err) {
      setError(err.message);
    }
  }, []);

  const exportToCSV = useCallback(async (filters = {}) => {
    try {
      await activityLogService.exportCSV(filters);
    } catch (err) {
      setError(err.message);
    }
  }, []);

  return {
    logs,
    stats,
    loading,
    error,
    loadLogs,
    loadStats,
    exportToCSV
  };
}

// ============================================================================
// STEP 7: REACT COMPONENTS
// ============================================================================

// Create: src/components/NotificationBell.jsx

import React from 'react';
import { useNotifications } from '../hooks/useNotifications';
import './NotificationBell.css';

export function NotificationBell() {
  const { notifications, unreadCount, markAsRead, deleteNotification } = useNotifications();
  const [isOpen, setIsOpen] = React.useState(false);

  return (
    <div className="notification-bell">
      <button
        className="bell-button"
        onClick={() => setIsOpen(!isOpen)}
      >
        🔔
        {unreadCount > 0 && <span className="badge">{unreadCount}</span>}
      </button>

      {isOpen && (
        <div className="dropdown">
          <h3>Notifications ({unreadCount} unread)</h3>
          <ul>
            {notifications.map(notif => (
              <li key={notif.notifikasi_id} className={notif.is_read ? 'read' : 'unread'}>
                <div className="content">
                  <h4>{notif.judul}</h4>
                  <p>{notif.pesan}</p>
                  <small>{new Date(notif.created_at).toLocaleString()}</small>
                </div>
                <div className="actions">
                  {!notif.is_read && (
                    <button onClick={() => markAsRead(notif.notifikasi_id)}>
                      ✓
                    </button>
                  )}
                  <button onClick={() => deleteNotification(notif.notifikasi_id)}>
                    ✕
                  </button>
                </div>
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}

// Create: src/components/ActivityLogsTable.jsx

import React, { useEffect, useState } from 'react';
import { useActivityLogs } from '../hooks/useActivityLogs';
import './ActivityLogsTable.css';

export function ActivityLogsTable() {
  const { logs, stats, loading, loadLogs, loadStats, exportToCSV } = useActivityLogs();
  const [filters, setFilters] = useState({
    userId: '',
    dateFrom: '',
    dateTo: ''
  });

  useEffect(() => {
    loadLogs(filters);
    loadStats(filters.dateFrom, filters.dateTo);
  }, [filters]);

  return (
    <div className="activity-logs">
      <h2>Activity Logs</h2>

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
        <button onClick={() => exportToCSV(filters)}>Export CSV</button>
      </div>

      {stats && (
        <div className="stats">
          <p>Total: {stats.total_activities}</p>
          <p>Points: {stats.total_points_changed}</p>
        </div>
      )}

      {loading && <p>Loading...</p>}

      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Activity</th>
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

// ============================================================================
// STEP 8: ENVIRONMENT SETUP
// ============================================================================

// Create: .env.local

REACT_APP_API_URL=http://localhost:8000/api
REACT_APP_ENV=development

// For production:
// REACT_APP_API_URL=https://api.mendaur.com/api
// REACT_APP_ENV=production

// ============================================================================
// STEP 9: USAGE IN MAIN APP
// ============================================================================

// In src/App.jsx or src/App.js

import { NotificationBell } from './components/NotificationBell';
import { ActivityLogsTable } from './components/ActivityLogsTable';

function App() {
  return (
    <div className="app">
      <header>
        <h1>Mendaur Admin Dashboard</h1>
        <NotificationBell />
      </header>

      <main>
        <ActivityLogsTable />
        {/* Other components */}
      </main>
    </div>
  );
}

export default App;

// ============================================================================
// STEP 10: ERROR HANDLING
// ============================================================================

// Create a custom error handler/logger
export class APIError extends Error {
  constructor(status, message) {
    super(message);
    this.status = status;
    this.name = 'APIError';
  }
}

export function handleAPIError(error) {
  if (error instanceof APIError) {
    switch (error.status) {
      case 401:
        console.error('Unauthorized - redirecting to login');
        localStorage.removeItem('token');
        window.location.href = '/login';
        break;
      case 403:
        console.error('Forbidden - insufficient permissions');
        alert('You do not have permission to perform this action');
        break;
      case 404:
        console.error('Not found');
        alert('Resource not found');
        break;
      case 422:
        console.error('Validation error');
        alert('Please check your input and try again');
        break;
      default:
        console.error('API Error:', error.message);
    }
  } else {
    console.error('Unknown error:', error);
  }
}

// ============================================================================
// CHECKLIST FOR IMPLEMENTATION
// ============================================================================

/**
 * ✅ Frontend Implementation Checklist
 *
 * Setup:
 * [ ] Create API client (src/api/client.js)
 * [ ] Create .env.local with API URL
 * [ ] Install axios or use fetch (no additional libraries needed)
 *
 * Services:
 * [ ] Create notificationService
 * [ ] Create activityLogService
 * [ ] Create badgeService
 * [ ] Create backupService
 *
 * Hooks:
 * [ ] Create useNotifications hook
 * [ ] Create useActivityLogs hook
 * [ ] Create useBadges hook
 * [ ] Create useBackup hook
 *
 * Components:
 * [ ] Create NotificationBell component
 * [ ] Create ActivityLogsTable component
 * [ ] Create BadgeManager component
 * [ ] Create BackupManager component
 *
 * Integration:
 * [ ] Add NotificationBell to header/layout
 * [ ] Add ActivityLogsTable to admin dashboard
 * [ ] Add BadgeManager to admin panel
 * [ ] Add BackupManager to system settings
 *
 * Testing:
 * [ ] Test notification polling
 * [ ] Test activity log filters
 * [ ] Test CSV export
 * [ ] Test badge assignment
 * [ ] Test backup creation
 * [ ] Test error handling
 * [ ] Test with different user roles
 *
 * Deployment:
 * [ ] Update .env with production API URL
 * [ ] Test in staging environment
 * [ ] Deploy to production
 * [ ] Monitor for errors
 */

// ============================================================================
// SUPPORT & DEBUGGING
// ============================================================================

/**
 * Common Issues:
 *
 * 1. Token not found
 *    - Check localStorage for 'token' key
 *    - Verify user is logged in
 *    - Check browser DevTools Storage tab
 *
 * 2. CORS errors
 *    - Ensure API URL is correct
 *    - Check browser console for exact error
 *    - Verify backend CORS configuration
 *
 * 3. API returns 401
 *    - Token expired: user needs to login again
 *    - Invalid token: clear localStorage and login
 *
 * 4. API returns 403
 *    - Insufficient permissions
 *    - Check user role (admin vs superadmin)
 *
 * Debug Tip: Check browser DevTools Network tab
 * - See request/response details
 * - Check headers
 * - View response status and body
 */
