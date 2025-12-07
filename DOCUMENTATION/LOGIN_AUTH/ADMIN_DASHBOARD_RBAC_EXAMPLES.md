# 🔐 Admin Dashboard RBAC Integration Examples

## How to Update Your Admin Dashboard to Use RBAC

This guide shows practical examples of how to integrate the new role-based access control into your existing admin dashboard.

---

## Example 1: Admin Dashboard Header with Permission Indicators

```jsx
// File: src/Components/Pages/admin/AdminDashboardHeader.jsx

import React from 'react';
import { useAuth } from '../context/AuthContext';
import { PermissionGuard, SuperAdminGuard } from '../PermissionGuard';
import { Settings, Users, BarChart3, Shield } from 'lucide-react';

export default function AdminDashboardHeader() {
  const { user, role, permissions } = useAuth();

  return (
    <div className="admin-header">
      <div className="header-info">
        <h1>Admin Dashboard</h1>
        <div className="role-badge">
          <span>{role === 'superadmin' ? '👑 Super Admin' : '👤 Admin'}</span>
          <span className="perm-count">{permissions.length} permissions</span>
        </div>
      </div>

      <div className="header-features">
        {/* Users Management - Admin & Superadmin */}
        <PermissionGuard permission="view_all_users">
          <div className="feature-card">
            <Users size={24} />
            <span>Users</span>
          </div>
        </PermissionGuard>

        {/* Reports - Admin & Superadmin */}
        <PermissionGuard permission="export_reports">
          <div className="feature-card">
            <BarChart3 size={24} />
            <span>Reports</span>
          </div>
        </PermissionGuard>

        {/* System Settings - Superadmin Only */}
        <SuperAdminGuard>
          <div className="feature-card">
            <Settings size={24} />
            <span>Settings</span>
          </div>
        </SuperAdminGuard>

        {/* Role Management - Superadmin Only */}
        <PermissionGuard permission="manage_roles">
          <div className="feature-card">
            <Shield size={24} />
            <span>Roles</span>
          </div>
        </PermissionGuard>
      </div>

      <div className="header-debug">
        <p>Logged in as: {user?.nama} ({user?.email})</p>
        <p>Role: {role} (Level {user?.role?.level_akses})</p>
      </div>
    </div>
  );
}
```

---

## Example 2: Admin Tabs with Permission Gating

```jsx
// File: src/Components/Pages/admin/AdminDashboardTabs.jsx

import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { PermissionGuard, SuperAdminGuard } from '../PermissionGuard';

import OverviewTab from './tabs/OverviewTab';
import UserManagementTab from './tabs/UserManagementTab';
import WasteAnalyticsTab from './tabs/WasteAnalyticsTab';
import PointsDistributionTab from './tabs/PointsDistributionTab';
import WasteByUserTab from './tabs/WasteByUserTab';
import ReportsTab from './tabs/ReportsTab';
import SystemSettingsTab from './tabs/SystemSettingsTab';
import RoleManagementTab from './tabs/RoleManagementTab';

export default function AdminDashboardTabs() {
  const { hasPermission, isSuperAdmin } = useAuth();
  const [activeTab, setActiveTab] = useState('overview');

  // Define available tabs based on permissions
  const tabs = [
    {
      id: 'overview',
      label: 'Overview',
      permission: 'view_admin_dashboard',
      component: OverviewTab,
      icon: '📊'
    },
    {
      id: 'users',
      label: 'Users',
      permission: 'view_all_users',
      component: UserManagementTab,
      icon: '👥'
    },
    {
      id: 'waste-analytics',
      label: 'Waste Analytics',
      permission: 'view_all_deposits',
      component: WasteAnalyticsTab,
      icon: '♻️'
    },
    {
      id: 'points',
      label: 'Points Distribution',
      permission: 'manual_poin_adjust',
      component: PointsDistributionTab,
      icon: '⭐'
    },
    {
      id: 'waste-by-user',
      label: 'Waste by User',
      permission: 'view_all_deposits',
      component: WasteByUserTab,
      icon: '📈'
    },
    {
      id: 'reports',
      label: 'Reports',
      permission: 'export_reports',
      component: ReportsTab,
      icon: '📄'
    },
    {
      id: 'settings',
      label: 'System Settings',
      permission: 'manage_system_settings',
      superAdminOnly: true,
      component: SystemSettingsTab,
      icon: '⚙️'
    },
    {
      id: 'roles',
      label: 'Role Management',
      permission: 'manage_roles',
      superAdminOnly: true,
      component: RoleManagementTab,
      icon: '🔐'
    }
  ];

  // Filter tabs based on permissions
  const availableTabs = tabs.filter(tab => {
    if (tab.superAdminOnly && !isSuperAdmin) {
      return false;
    }
    return hasPermission(tab.permission);
  });

  // Find active tab component
  const activeTabObj = availableTabs.find(t => t.id === activeTab);
  const ActiveComponent = activeTabObj?.component || OverviewTab;

  return (
    <div className="admin-tabs-container">
      {/* Tab Navigation */}
      <div className="tabs-nav">
        {availableTabs.map(tab => (
          <button
            key={tab.id}
            className={`tab-button ${activeTab === tab.id ? 'active' : ''}`}
            onClick={() => setActiveTab(tab.id)}
            title={`Permission: ${tab.permission}`}
          >
            <span className="icon">{tab.icon}</span>
            <span className="label">{tab.label}</span>
          </button>
        ))}
      </div>

      {/* Tab Content */}
      <div className="tabs-content">
        <ActiveComponent />
      </div>

      {/* Debug Info */}
      <div className="tabs-debug">
        <p>Available tabs: {availableTabs.length} / {tabs.length}</p>
        <p>Hidden tabs: {tabs.length - availableTabs.length}</p>
      </div>
    </div>
  );
}
```

---

## Example 3: User Management Table with Permission-based Actions

```jsx
// File: src/Components/Pages/admin/UserManagementTable.jsx

import React from 'react';
import { useAuth } from '../context/AuthContext';
import { PermissionGuard } from '../PermissionGuard';

export default function UserManagementTable({ users }) {
  const { hasPermission } = useAuth();

  return (
    <table className="users-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Total Points</th>
          <th>Total Deposits</th>
          <th>Role</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        {users.map(user => (
          <tr key={user.id}>
            <td>{user.nama}</td>
            <td>{user.email}</td>
            <td>{user.no_hp}</td>
            <td>{user.total_poin}</td>
            <td>{user.total_setor_sampah}</td>
            <td>
              <span className={`role-badge role-${user.role?.nama_role}`}>
                {user.role?.nama_role}
              </span>
            </td>
            <td>
              <span className="status-badge active">Active</span>
            </td>
            <td className="actions-column">
              {/* View Details - Everyone can view */}
              <button 
                className="btn-small btn-info"
                onClick={() => viewUserDetails(user.id)}
              >
                View
              </button>

              {/* Adjust Points - If has permission */}
              <PermissionGuard permission="manual_poin_adjust">
                <button 
                  className="btn-small btn-warning"
                  onClick={() => adjustPoints(user.id)}
                >
                  Adjust Points
                </button>
              </PermissionGuard>

              {/* Send Notification - If has permission */}
              <PermissionGuard permission="send_notification">
                <button 
                  className="btn-small btn-primary"
                  onClick={() => sendNotification(user.id)}
                >
                  Notify
                </button>
              </PermissionGuard>

              {/* Disable User - Superadmin only */}
              <PermissionGuard 
                permission="disable_enable_users"
                fallback={null}
              >
                <button 
                  className="btn-small btn-danger"
                  onClick={() => disableUser(user.id)}
                >
                  Disable
                </button>
              </PermissionGuard>

              {/* Edit Profile - If has permission */}
              <PermissionGuard permission="manage_user_profile">
                <button 
                  className="btn-small btn-secondary"
                  onClick={() => editUser(user.id)}
                >
                  Edit
                </button>
              </PermissionGuard>
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}

// Helper functions
function viewUserDetails(userId) {
  console.log('View user:', userId);
  // Fetch user details
}

function adjustPoints(userId) {
  console.log('Adjust points for user:', userId);
  // Show modal to adjust points
}

function sendNotification(userId) {
  console.log('Send notification to user:', userId);
  // Show notification form
}

function disableUser(userId) {
  console.log('Disable user:', userId);
  // Disable user account
}

function editUser(userId) {
  console.log('Edit user:', userId);
  // Show edit form
}
```

---

## Example 4: Approval Actions with Permission Checks

```jsx
// File: src/Components/Pages/admin/DepositsApprovalTable.jsx

import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { PermissionGuard } from '../PermissionGuard';

export default function DepositsApprovalTable({ deposits }) {
  const { hasPermission } = useAuth();
  const [actionLoading, setActionLoading] = useState({});

  const handleApprove = async (depositId) => {
    setActionLoading(prev => ({ ...prev, [depositId]: 'approve' }));
    try {
      const response = await fetch(`http://127.0.0.1:8000/api/deposits/${depositId}/approve`, {
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      });

      if (!response.ok) throw new Error('Failed to approve');
      
      console.log('✅ Deposit approved');
      // Refresh deposits list
    } catch (error) {
      console.error('❌ Error approving deposit:', error);
    } finally {
      setActionLoading(prev => ({ ...prev, [depositId]: null }));
    }
  };

  const handleReject = async (depositId, reason) => {
    setActionLoading(prev => ({ ...prev, [depositId]: 'reject' }));
    try {
      const response = await fetch(`http://127.0.0.1:8000/api/deposits/${depositId}/reject`, {
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ reason })
      });

      if (!response.ok) throw new Error('Failed to reject');
      
      console.log('✅ Deposit rejected');
      // Refresh deposits list
    } catch (error) {
      console.error('❌ Error rejecting deposit:', error);
    } finally {
      setActionLoading(prev => ({ ...prev, [depositId]: null }));
    }
  };

  return (
    <table className="deposits-approval-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>User</th>
          <th>Waste Type</th>
          <th>Weight (kg)</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        {deposits.map(deposit => (
          <tr key={deposit.id}>
            <td>{new Date(deposit.created_at).toLocaleDateString()}</td>
            <td>{deposit.nama_lengkap}</td>
            <td>{deposit.jenis_sampah}</td>
            <td>{deposit.berat_kg}</td>
            <td>
              <span className={`status-badge status-${deposit.status}`}>
                {deposit.status}
              </span>
            </td>
            <td className="actions-column">
              {/* Show approval buttons only if status is pending AND user has permission */}
              {deposit.status === 'pending' && (
                <>
                  <PermissionGuard permission="approve_deposit">
                    <button
                      className="btn-small btn-success"
                      onClick={() => handleApprove(deposit.id)}
                      disabled={actionLoading[deposit.id] === 'approve'}
                    >
                      {actionLoading[deposit.id] === 'approve' ? 'Approving...' : 'Approve'}
                    </button>
                  </PermissionGuard>

                  <PermissionGuard permission="approve_deposit">
                    <button
                      className="btn-small btn-danger"
                      onClick={() => handleReject(deposit.id, 'Admin rejection')}
                      disabled={actionLoading[deposit.id] === 'reject'}
                    >
                      {actionLoading[deposit.id] === 'reject' ? 'Rejecting...' : 'Reject'}
                    </button>
                  </PermissionGuard>
                </>
              )}

              {/* Show view button for all users */}
              <button
                className="btn-small btn-info"
                onClick={() => viewDepositDetails(deposit.id)}
              >
                View
              </button>
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}

function viewDepositDetails(depositId) {
  console.log('View deposit:', depositId);
  // Show deposit details modal
}
```

---

## Example 5: Conditional Menu in Admin Sidebar

```jsx
// File: src/Components/Pages/admin/AdminSidebar.jsx

import React from 'react';
import { useAuth } from '../context/AuthContext';
import { PermissionGuard, SuperAdminGuard } from '../PermissionGuard';
import { 
  BarChart3, Users, SettingsIcon, ShieldAlert, 
  Inbox, FileText, Lock 
} from 'lucide-react';

export default function AdminSidebar() {
  const { isAdmin, isSuperAdmin } = useAuth();

  if (!isAdmin) return null;

  return (
    <aside className="admin-sidebar">
      <div className="sidebar-header">
        <h3>Admin Menu</h3>
      </div>

      <nav className="sidebar-nav">
        {/* Overview - Everyone */}
        <a href="/admin/dashboard" className="nav-item">
          <BarChart3 size={20} />
          <span>Overview</span>
        </a>

        {/* Users - If has permission */}
        <PermissionGuard permission="view_all_users">
          <a href="/admin/users" className="nav-item">
            <Users size={20} />
            <span>Users</span>
          </a>
        </PermissionGuard>

        {/* Approvals - If has permission */}
        <PermissionGuard 
          permission={['approve_deposit', 'approve_withdrawal']}
          require="any"
        >
          <a href="/admin/approvals" className="nav-item">
            <ShieldAlert size={20} />
            <span>Approvals</span>
          </a>
        </PermissionGuard>

        {/* Reports - If has permission */}
        <PermissionGuard permission="export_reports">
          <a href="/admin/reports" className="nav-item">
            <FileText size={20} />
            <span>Reports</span>
          </a>
        </PermissionGuard>

        {/* Notifications - If has permission */}
        <PermissionGuard permission="send_notification">
          <a href="/admin/notifications" className="nav-item">
            <Inbox size={20} />
            <span>Notifications</span>
          </a>
        </PermissionGuard>

        {/* Divider */}
        <div className="nav-divider" />

        {/* System Settings - Superadmin only */}
        <SuperAdminGuard>
          <a href="/admin/settings" className="nav-item">
            <SettingsIcon size={20} />
            <span>Settings</span>
          </a>
        </SuperAdminGuard>

        {/* Role Management - Superadmin only */}
        <PermissionGuard permission="manage_roles">
          <a href="/admin/roles" className="nav-item">
            <Lock size={20} />
            <span>Roles</span>
          </a>
        </PermissionGuard>

        {/* Admin Management - Superadmin only */}
        <PermissionGuard permission="manage_admins">
          <a href="/admin/admins" className="nav-item">
            <Users size={20} />
            <span>Manage Admins</span>
          </a>
        </PermissionGuard>
      </nav>

      <div className="sidebar-footer">
        <p>{isSuperAdmin ? '👑 Super Admin' : '👤 Admin'}</p>
      </div>
    </aside>
  );
}
```

---

## Example 6: Reports Export with Permission Check

```jsx
// File: src/Components/Pages/admin/ReportsExport.jsx

import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { PermissionGuard } from '../PermissionGuard';
import { Download, FileText, BarChart3 } from 'lucide-react';

export default function ReportsExport() {
  const { hasPermission } = useAuth();
  const [exporting, setExporting] = useState(false);

  const handleExport = async (type) => {
    if (!hasPermission('export_reports')) {
      alert('You do not have permission to export reports');
      return;
    }

    setExporting(true);
    try {
      const response = await fetch(
        `http://127.0.0.1:8000/api/admin/export/${type}`,
        {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        }
      );

      if (!response.ok) throw new Error('Export failed');

      // Download file
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `${type}-export.pdf`;
      a.click();

      console.log('✅ Report exported successfully');
    } catch (error) {
      console.error('❌ Export error:', error);
    } finally {
      setExporting(false);
    }
  };

  return (
    <div className="reports-export">
      <h2>Export Reports</h2>

      <div className="export-options">
        {/* Users Report */}
        <PermissionGuard 
          permission="export_reports"
          fallback={
            <div className="export-card disabled">
              <Users size={32} />
              <h3>Users Report</h3>
              <p>No permission</p>
            </div>
          }
        >
          <div className="export-card">
            <Users size={32} />
            <h3>Users Report</h3>
            <p>Export all user data</p>
            <button
              onClick={() => handleExport('users')}
              disabled={exporting}
            >
              <Download size={16} />
              Export
            </button>
          </div>
        </PermissionGuard>

        {/* Deposits Report */}
        <PermissionGuard 
          permission="export_deposits"
          fallback={
            <div className="export-card disabled">
              <FileText size={32} />
              <h3>Deposits Report</h3>
              <p>No permission</p>
            </div>
          }
        >
          <div className="export-card">
            <FileText size={32} />
            <h3>Deposits Report</h3>
            <p>Export all deposits</p>
            <button
              onClick={() => handleExport('deposits')}
              disabled={exporting}
            >
              <Download size={16} />
              Export
            </button>
          </div>
        </PermissionGuard>

        {/* Financial Report */}
        <PermissionGuard 
          permission="financial_reports"
          fallback={
            <div className="export-card disabled">
              <BarChart3 size={32} />
              <h3>Financial Report</h3>
              <p>No permission</p>
            </div>
          }
        >
          <div className="export-card">
            <BarChart3 size={32} />
            <h3>Financial Report</h3>
            <p>Export financial data</p>
            <button
              onClick={() => handleExport('financial')}
              disabled={exporting}
            >
              <Download size={16} />
              Export
            </button>
          </div>
        </PermissionGuard>
      </div>
    </div>
  );
}
```

---

## Summary

These examples show how to:

✅ **Show/hide UI elements** based on permissions
✅ **Disable buttons** for unauthorized actions
✅ **Filter menu items** based on role
✅ **Gate entire sections** with PermissionGuard
✅ **Include Bearer token** in API calls
✅ **Handle permission denied** scenarios
✅ **Show fallback UI** when no permission

Just copy these patterns into your existing admin dashboard components!
