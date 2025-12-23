/**
 * Admin Dashboard API Service
 * Handles all API calls untuk admin dashboard features
 */

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'

/**
 * Get authorization header dengan token
 */
const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  
  if (!token) {
    console.warn('⚠️ No token found in localStorage')
    console.warn('Available localStorage keys:', Object.keys(localStorage))
  } else {
    console.log('🔐 Token found in localStorage:', {
      length: token.length,
      preview: token.substring(0, 30) + '...' + token.substring(token.length - 20),
      type: typeof token
    })
  }
  
  return {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
}

/**
 * Handle API response error
 */
const handleError = (error, defaultMessage = 'An error occurred') => {
  console.error('API Error:', error)
  return {
    success: false,
    message: error.message || defaultMessage,
    data: null
  }
}

/**
 * Dashboard Overview - GET /api/admin/dashboard/overview
 */
export const adminApi = {
  // Dashboard
  getOverview: async () => {
    try {
      const token = localStorage.getItem('token')
      if (!token) {
        console.warn('⚠️ No authentication token available')
        return {
          success: false,
          message: 'Not authenticated. Please login again.',
          data: null
        }
      }

      const response = await fetch(`${API_BASE_URL}/admin/dashboard/overview`, {
        method: 'GET',
        headers: getAuthHeader()
      })

      if (response.status === 401) {
        console.warn('⚠️ Authentication failed (401). Token may be expired.')
        return {
          success: false,
          message: 'Authentication failed. Please login again.',
          data: null
        }
      }

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }

      const data = await response.json()
      console.info('✅ Overview data loaded successfully')
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to fetch overview stats')
    }
  },

  // Users
  getAllUsers: async (page = 1, limit = 10, filters = {}) => {
    try {
      const params = new URLSearchParams({
        page,
        limit,
        ...filters
      })

      const response = await fetch(`${API_BASE_URL}/admin/users?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to fetch users')
    }
  },

  updateUserStatus: async (userId, status) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/users/${userId}/status`, {
        method: 'PATCH',
        headers: getAuthHeader(),
        body: JSON.stringify({ status })
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to update user status')
    }
  },

  deleteUser: async (userId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/users/${userId}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      return {
        success: true,
        message: 'User deleted successfully'
      }
    } catch (error) {
      return handleError(error, 'Failed to delete user')
    }
  },

  // Analytics - Waste
  getWasteAnalytics: async (period = 'monthly', filters = {}) => {
    try {
      const params = new URLSearchParams({
        period,
        ...filters
      })

      const response = await fetch(`${API_BASE_URL}/admin/analytics/waste?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to fetch waste analytics')
    }
  },

  getWasteByUser: async (page = 1, limit = 10, filters = {}) => {
    try {
      const params = new URLSearchParams({
        page,
        limit,
        ...filters
      })

      const response = await fetch(`${API_BASE_URL}/admin/analytics/waste-by-user?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to fetch waste by user')
    }
  },

  // Analytics - Points
  getPointsAnalytics: async (period = 'monthly', filters = {}) => {
    try {
      const params = new URLSearchParams({
        period,
        ...filters
      })

      const response = await fetch(`${API_BASE_URL}/admin/analytics/points?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to fetch points analytics')
    }
  },

  awardPoints: async (userId, points, reason = '') => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/points/award`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify({
          user_id: userId,
          points,
          reason
        })
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to award points')
    }
  },

  getPointsHistory: async (page = 1, limit = 20, filters = {}) => {
    try {
      const params = new URLSearchParams({
        page,
        limit,
        ...filters
      })

      const response = await fetch(`${API_BASE_URL}/admin/points/history?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to fetch points history')
    }
  },

  // Leaderboard
  getLeaderboard: async (period = 'monthly', limit = 100) => {
    try {
      const params = new URLSearchParams({ period, limit })

      const response = await fetch(`${API_BASE_URL}/admin/leaderboard?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to fetch leaderboard')
    }
  },

  // Reports
  generateReport: async (type, period, startDate, endDate, format = 'pdf') => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/reports/generate`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify({
          type,
          period,
          startDate,
          endDate,
          format
        })
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to generate report')
    }
  },

  exportData: async (type = 'users', format = 'csv') => {
    try {
      const params = new URLSearchParams({ type, format })

      const response = await fetch(`${API_BASE_URL}/admin/export?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      // For file downloads, return the response directly
      return {
        success: true,
        data: response
      }
    } catch (error) {
      return handleError(error, 'Failed to export data')
    }
  },

  // ============================================
  // PHASE 1 ENDPOINTS - WASTE DEPOSIT MANAGEMENT
  // ============================================

  /**
   * List all waste deposits with filtering and pagination
   * GET /api/admin/penyetoran-sampah
   */
  listWasteDeposits: async (page = 1, limit = 10, filters = {}) => {
    try {
      const params = new URLSearchParams({
        page,
        limit,
        ...filters
      })

      const headers = getAuthHeader()
      console.log('📤 REQUEST DETAILS:', {
        url: `${API_BASE_URL}/admin/penyetoran-sampah?${params}`,
        method: 'GET',
        authHeader: headers.Authorization ? headers.Authorization.substring(0, 30) + '...' : 'MISSING',
        tokenInStorage: localStorage.getItem('token') ? 'YES' : 'NO'
      })

      const response = await fetch(`${API_BASE_URL}/admin/penyetoran-sampah?${params}`, {
        method: 'GET',
        headers: headers
      })

      console.log('📥 RESPONSE:', {
        status: response.status,
        ok: response.ok,
        statusText: response.statusText
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      console.info('✅ Waste deposits loaded successfully')
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to fetch waste deposits')
    }
  },

  /**
   * Get details of a specific waste deposit
   * GET /api/admin/penyetoran-sampah/{id}
   */
  getWasteDepositDetail: async (depositId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penyetoran-sampah/${depositId}`, {
        method: 'GET',
        headers: getAuthHeader()
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      console.info(`✅ Deposit #${depositId} loaded successfully`)
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to fetch deposit details')
    }
  },

  /**
   * Approve a waste deposit and assign poin
   * PATCH /api/admin/penyetoran-sampah/{id}/approve
   */
  approveWasteDeposit: async (depositId, poinDiberikan) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penyetoran-sampah/${depositId}/approve`, {
        method: 'PATCH',
        headers: getAuthHeader(),
        body: JSON.stringify({
          poin_diberikan: parseInt(poinDiberikan)
        })
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || `HTTP ${response.status}`)
      }

      const data = await response.json()
      console.info(`✅ Deposit #${depositId} approved with ${poinDiberikan} poin`)
      return {
        success: true,
        message: 'Deposit approved successfully',
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to approve waste deposit')
    }
  },

  /**
   * Reject a waste deposit with reason
   * PATCH /api/admin/penyetoran-sampah/{id}/reject
   */
  rejectWasteDeposit: async (depositId, alasanPenolakan) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penyetoran-sampah/${depositId}/reject`, {
        method: 'PATCH',
        headers: getAuthHeader(),
        body: JSON.stringify({
          alasan_penolakan: alasanPenolakan
        })
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || `HTTP ${response.status}`)
      }

      const data = await response.json()
      console.info(`✅ Deposit #${depositId} rejected`)
      return {
        success: true,
        message: 'Deposit rejected successfully',
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to reject waste deposit')
    }
  },

  /**
   * Delete a waste deposit (Superadmin only)
   * DELETE /api/admin/penyetoran-sampah/{id}
   */
  deleteWasteDeposit: async (depositId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penyetoran-sampah/${depositId}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || `HTTP ${response.status}`)
      }

      const data = await response.json()
      console.info(`✅ Deposit #${depositId} deleted`)
      return {
        success: true,
        message: 'Deposit deleted successfully',
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to delete waste deposit')
    }
  },

  /**
   * Get waste deposit statistics
   * GET /api/admin/penyetoran-sampah/stats/overview
   */
  getWasteStats: async () => {
    try {
      const headers = getAuthHeader()
      console.log('📤 Sending stats request with headers:', {
        Authorization: headers.Authorization ? headers.Authorization.substring(0, 20) + '...' : 'MISSING',
        token: localStorage.getItem('token') ? 'Present' : 'MISSING'
      })

      const response = await fetch(`${API_BASE_URL}/admin/penyetoran-sampah/stats/overview`, {
        method: 'GET',
        headers: headers
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const data = await response.json()
      console.info('✅ Waste statistics loaded successfully')
      return {
        success: true,
        data: data.data || data
      }
    } catch (error) {
      return handleError(error, 'Failed to fetch waste statistics')
    }
  },

  // ============================================
  // ISSUE 2: MISSING ENDPOINTS (19 total)
  // ============================================

  // ADMIN MANAGEMENT (6 endpoints)
  /**
   * Get all admin users
   * GET /api/admin/admins
   */
  getAllAdmins: async (page = 1, limit = 10) => {
    try {
      const params = new URLSearchParams({ page, limit })
      const response = await fetch(`${API_BASE_URL}/admin/admins?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ All admins loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch admins')
    }
  },

  /**
   * Get admin user by ID
   * GET /api/admin/admins/{adminId}
   */
  getAdminById: async (adminId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/admins/${adminId}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Admin #${adminId} loaded`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch admin')
    }
  },

  /**
   * Create new admin user
   * POST /api/admin/admins
   */
  createAdmin: async (adminData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/admins`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify(adminData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ Admin created successfully')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to create admin')
    }
  },

  /**
   * Update admin user
   * PUT /api/admin/admins/{adminId}
   */
  updateAdmin: async (adminId, adminData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/admins/${adminId}`, {
        method: 'PUT',
        headers: getAuthHeader(),
        body: JSON.stringify(adminData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Admin #${adminId} updated`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to update admin')
    }
  },

  /**
   * Delete admin user
   * DELETE /api/admin/admins/{adminId}
   */
  deleteAdmin: async (adminId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/admins/${adminId}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Admin #${adminId} deleted`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to delete admin')
    }
  },

  /**
   * Get admin activity logs
   * GET /api/admin/admins/{adminId}/activity-logs
   */
  getAdminActivityLogs: async (adminId, page = 1, limit = 20) => {
    try {
      const params = new URLSearchParams({ page, limit })
      const response = await fetch(`${API_BASE_URL}/admin/admins/${adminId}/activity-logs?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Activity logs for admin #${adminId} loaded`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch activity logs')
    }
  },

  // ROLE MANAGEMENT (5 endpoints)
  /**
   * Get all roles
   * GET /api/admin/roles
   */
  getAllRoles: async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/roles`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ All roles loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch roles')
    }
  },

  /**
   * Get role by ID
   * GET /api/admin/roles/{roleId}
   */
  getRoleById: async (roleId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/roles/${roleId}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Role #${roleId} loaded`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch role')
    }
  },

  /**
   * Create new role
   * POST /api/admin/roles
   */
  createRole: async (roleData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/roles`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify(roleData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ Role created successfully')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to create role')
    }
  },

  /**
   * Update role
   * PUT /api/admin/roles/{roleId}
   */
  updateRole: async (roleId, roleData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/roles/${roleId}`, {
        method: 'PUT',
        headers: getAuthHeader(),
        body: JSON.stringify(roleData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Role #${roleId} updated`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to update role')
    }
  },

  /**
   * Delete role
   * DELETE /api/admin/roles/{roleId}
   */
  deleteRole: async (roleId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/roles/${roleId}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Role #${roleId} deleted`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to delete role')
    }
  },

  // PERMISSION ASSIGNMENT (3 endpoints)
  /**
   * Assign permissions to role
   * POST /api/admin/roles/{roleId}/permissions
   */
  assignPermissionsToRole: async (roleId, permissionIds) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/roles/${roleId}/permissions`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify({ permission_ids: permissionIds })
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Permissions assigned to role #${roleId}`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to assign permissions')
    }
  },

  /**
   * Get role permissions
   * GET /api/admin/roles/{roleId}/permissions
   */
  getRolePermissions: async (roleId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/roles/${roleId}/permissions`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Permissions for role #${roleId} loaded`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch role permissions')
    }
  },

  /**
   * Get all available permissions
   * GET /api/admin/permissions
   */
  getAllPermissions: async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/permissions`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ All permissions loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch permissions')
    }
  },

  // BADGE MANAGEMENT (5 endpoints)
  /**
   * Get all badges
   * GET /api/admin/badges
   */
  getAllBadges: async (page = 1, limit = 20) => {
    try {
      const params = new URLSearchParams({ page, limit })
      const response = await fetch(`${API_BASE_URL}/admin/badges?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ All badges loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch badges')
    }
  },

  /**
   * Create badge
   * POST /api/admin/badges
   */
  createBadge: async (badgeData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/badges`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify(badgeData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ Badge created successfully')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to create badge')
    }
  },

  /**
   * Update badge
   * PUT /api/admin/badges/{badgeId}
   */
  updateBadge: async (badgeId, badgeData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/badges/${badgeId}`, {
        method: 'PUT',
        headers: getAuthHeader(),
        body: JSON.stringify(badgeData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Badge #${badgeId} updated`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to update badge')
    }
  },

  /**
   * Delete badge
   * DELETE /api/admin/badges/{badgeId}
   */
  deleteBadge: async (badgeId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/badges/${badgeId}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Badge #${badgeId} deleted`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to delete badge')
    }
  },

  /**
   * Assign badge to user
   * POST /api/admin/badges/{badgeId}/assign
   */
  assignBadgeToUser: async (badgeId, userId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/badges/${badgeId}/assign`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify({ user_id: userId })
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Badge #${badgeId} assigned to user #${userId}`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to assign badge')
    }
  },

  // ============================================
  // PRODUCT MANAGEMENT (4 endpoints)
  // ============================================

  /**
   * Get all products with pagination and filtering
   * GET /api/admin/produk
   */
  getAllProducts: async (page = 1, limit = 10, filters = {}) => {
    try {
      const params = new URLSearchParams({
        page,
        limit,
        ...filters
      })
      const response = await fetch(`${API_BASE_URL}/admin/produk?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ All products loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch products')
    }
  },

  /**
   * Create new product
   * POST /api/admin/produk
   */
  createProduct: async (productData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/produk`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify(productData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ Product created successfully')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to create product')
    }
  },

  /**
   * Update product
   * PUT /api/admin/produk/{produkId}
   */
  updateProduct: async (produkId, productData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/produk/${produkId}`, {
        method: 'PUT',
        headers: getAuthHeader(),
        body: JSON.stringify(productData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Product #${produkId} updated`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to update product')
    }
  },

  /**
   * Delete product
   * DELETE /api/admin/produk/{produkId}
   */
  deleteProduct: async (produkId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/produk/${produkId}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Product #${produkId} deleted`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to delete product')
    }
  },

  // ============================================
  // PRODUCT REDEMPTION MANAGEMENT (4 endpoints)
  // ============================================

  /**
   * Get all product redemptions
   * GET /api/admin/penukar-produk
   */
  getProductRedemptions: async (page = 1, limit = 20, filters = {}) => {
    try {
      const params = new URLSearchParams({ page, limit, ...filters })
      const response = await fetch(`${API_BASE_URL}/admin/penukar-produk?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ Product redemptions loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch product redemptions')
    }
  },

  /**
   * Approve product redemption
   * PATCH /api/admin/penukar-produk/{id}/approve
   */
  approveRedemption: async (redemptionId, approvalData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penukar-produk/${redemptionId}/approve`, {
        method: 'PATCH',
        headers: getAuthHeader(),
        body: JSON.stringify(approvalData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Redemption #${redemptionId} approved`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to approve redemption')
    }
  },

  /**
   * Reject product redemption
   * PATCH /api/admin/penukar-produk/{id}/reject
   */
  rejectRedemption: async (redemptionId, rejectionData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penukar-produk/${redemptionId}/reject`, {
        method: 'PATCH',
        headers: getAuthHeader(),
        body: JSON.stringify(rejectionData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Redemption #${redemptionId} rejected`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to reject redemption')
    }
  },

  // ============================================
  // WASTE ITEM & CATEGORY MANAGEMENT (5 endpoints)
  // ============================================

  /**
   * Get all waste categories
   * GET /api/admin/waste-categories
   */
  getAllWasteCategories: async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/waste-categories`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ All waste categories loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch waste categories')
    }
  },

  /**
   * Get all waste items (jenis_sampah)
   * GET /api/admin/jenis-sampah
   */
  getAllWasteItems: async (page = 1, limit = 20, filters = {}) => {
    try {
      const params = new URLSearchParams({
        page,
        limit,
        ...filters
      })
      const response = await fetch(`${API_BASE_URL}/admin/jenis-sampah?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ All waste items loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch waste items')
    }
  },

  /**
   * Create waste item
   * POST /api/admin/jenis-sampah
   */
  createWasteItem: async (wasteItemData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/jenis-sampah`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify(wasteItemData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ Waste item created successfully')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to create waste item')
    }
  },

  /**
   * Update waste item
   * PUT /api/admin/jenis-sampah/{jenisSampahId}
   */
  updateWasteItem: async (jenisSampahId, wasteItemData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/jenis-sampah/${jenisSampahId}`, {
        method: 'PUT',
        headers: getAuthHeader(),
        body: JSON.stringify(wasteItemData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Waste item #${jenisSampahId} updated`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to update waste item')
    }
  },

  /**
   * Delete waste item
   * DELETE /api/admin/jenis-sampah/{jenisSampahId}
   */
  deleteWasteItem: async (jenisSampahId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/jenis-sampah/${jenisSampahId}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Waste item #${jenisSampahId} deleted`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to delete waste item')
    }
  },

  // ============================================
  // SCHEDULE MANAGEMENT (6 endpoints)
  // ============================================

  /**
   * Get all waste deposit schedules (jadwal_penyetoran)
   * GET /api/admin/jadwal-penyetoran
   */
  getAllSchedules: async (page = 1, limit = 20, filters = {}) => {
    try {
      const params = new URLSearchParams({
        page,
        limit,
        ...filters
      })
      const response = await fetch(`${API_BASE_URL}/admin/jadwal-penyetoran?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ All schedules loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch schedules')
    }
  },

  /**
   * Get schedule details
   * GET /api/admin/jadwal-penyetoran/{jadwalId}
   */
  getScheduleDetail: async (jadwalId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/jadwal-penyetoran/${jadwalId}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Schedule #${jadwalId} loaded`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch schedule details')
    }
  },

  /**
   * Create new schedule
   * POST /api/admin/jadwal-penyetoran
   */
  createSchedule: async (scheduleData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/jadwal-penyetoran`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify(scheduleData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ Schedule created successfully')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to create schedule')
    }
  },

  /**
   * Update schedule
   * PUT /api/admin/jadwal-penyetoran/{jadwalId}
   */
  updateSchedule: async (jadwalId, scheduleData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/jadwal-penyetoran/${jadwalId}`, {
        method: 'PUT',
        headers: getAuthHeader(),
        body: JSON.stringify(scheduleData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Schedule #${jadwalId} updated`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to update schedule')
    }
  },

  /**
   * Delete schedule
   * DELETE /api/admin/jadwal-penyetoran/{jadwalId}
   */
  deleteSchedule: async (jadwalId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/jadwal-penyetoran/${jadwalId}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Schedule #${jadwalId} deleted`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to delete schedule')
    }
  },

  /**
   * Register user to schedule
   * POST /api/admin/jadwal-penyetoran/{jadwalId}/register
   */
  registerUserToSchedule: async (jadwalId, userId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/jadwal-penyetoran/${jadwalId}/register`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify({ user_id: userId })
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ User registered to schedule #${jadwalId}`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to register user to schedule')
    }
  },

  // ============================================
  // NOTIFICATION MANAGEMENT (4 endpoints)
  // ============================================

  /**
   * Get all notifications
   * GET /api/admin/notifications
   */
  getNotifications: async (page = 1, limit = 20, filters = {}) => {
    try {
      const params = new URLSearchParams({ page, limit, ...filters })
      const response = await fetch(`${API_BASE_URL}/admin/notifications?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ Notifications loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch notifications')
    }
  },

  /**
   * Get notification templates
   * GET /api/admin/notifications/templates
   */
  getNotificationTemplates: async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/notifications/templates`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ Notification templates loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch templates')
    }
  },

  /**
   * Create notification
   * POST /api/admin/notifications
   */
  createNotification: async (notificationData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/notifications`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify(notificationData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ Notification created')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to create notification')
    }
  },

  /**
   * Delete notification
   * DELETE /api/admin/notifications/{notificationId}
   */
  deleteNotification: async (notificationId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Notification #${notificationId} deleted`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to delete notification')
    }
  },

  // ============================================
  // ARTICLE MANAGEMENT (5 endpoints)
  // ============================================

  /**
   * Get all articles
   * GET /api/admin/artikel
   */
  getAllArticles: async (page = 1, limit = 20, filters = {}) => {
    try {
      const params = new URLSearchParams({
        page,
        limit,
        ...filters
      })
      const response = await fetch(`${API_BASE_URL}/admin/artikel?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ All articles loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch articles')
    }
  },

  /**
   * Get article details
   * GET /api/admin/artikel/{artikelId}
   */
  getArticleDetail: async (artikelId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/artikel/${artikelId}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Article #${artikelId} loaded`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch article details')
    }
  },

  /**
   * Create new article
   * POST /api/admin/artikel
   */
  createArticle: async (articleData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/artikel`, {
        method: 'POST',
        headers: getAuthHeader(),
        body: JSON.stringify(articleData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ Article created successfully')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to create article')
    }
  },

  /**
   * Update article
   * PUT /api/admin/artikel/{artikelId}
   */
  updateArticle: async (artikelId, articleData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/artikel/${artikelId}`, {
        method: 'PUT',
        headers: getAuthHeader(),
        body: JSON.stringify(articleData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Article #${artikelId} updated`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to update article')
    }
  },

  /**
   * Delete article
   * DELETE /api/admin/artikel/{artikelId}
   */
  deleteArticle: async (artikelId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/artikel/${artikelId}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info(`✅ Article #${artikelId} deleted`)
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to delete article')
    }
  },

  // ============================================
  // TRANSACTION HISTORY (2 endpoints)
  // ============================================

  /**
   * Get all transactions (combined view)
   * GET /api/admin/transactions
   */
  getAllTransactions: async (page = 1, limit = 20, filters = {}) => {
    try {
      const params = new URLSearchParams({ page, limit, ...filters })
      const response = await fetch(`${API_BASE_URL}/admin/transactions?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      console.info('✅ All transactions loaded')
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch transactions')
    }
  },

  /**
   * Export transactions to CSV/Excel
   * GET /api/admin/transactions/export
   */
  exportTransactions: async (format = 'csv', filters = {}) => {
    try {
      const params = new URLSearchParams({ format, ...filters })
      const response = await fetch(`${API_BASE_URL}/admin/transactions/export?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      return { success: true, data: response }
    } catch (error) {
      return handleError(error, 'Failed to export transactions')
    }
  },

  /**
   * Get cash withdrawals list
   * GET /api/admin/penarikan-tunai
   */
  getCashWithdrawals: async (page = 1, limit = 20, filters = {}) => {
    try {
      const params = new URLSearchParams({ page, limit, ...filters })
      const response = await fetch(`${API_BASE_URL}/admin/penarikan-tunai?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch cash withdrawals')
    }
  },

  /**
   * Approve cash withdrawal
   * PATCH /api/admin/penarikan-tunai/{id}/approve
   */
  approveCashWithdrawal: async (id, notes = '') => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penarikan-tunai/${id}/approve`, {
        method: 'PATCH',
        headers: getAuthHeader(),
        body: JSON.stringify({ catatan_admin: notes })
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to approve cash withdrawal')
    }
  },

  /**
   * Reject cash withdrawal
   * PATCH /api/admin/penarikan-tunai/{id}/reject
   */
  rejectCashWithdrawal: async (id, reason = '', notes = '') => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penarikan-tunai/${id}/reject`, {
        method: 'PATCH',
        headers: getAuthHeader(),
        body: JSON.stringify({ alasan_penolakan: reason, catatan_admin: notes })
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to reject cash withdrawal')
    }
  },

  // ============ ADDITIONAL USER MANAGEMENT ENDPOINTS ============
  getAdminUserById: async (userId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/users/${userId}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch user')
    }
  },

  updateAdminUser: async (userId, userData) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/users/${userId}`, {
        method: 'PUT',
        headers: getAuthHeader(),
        body: JSON.stringify(userData)
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to update user')
    }
  },

  updateUserRole: async (userId, roleId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/users/${userId}/role`, {
        method: 'PATCH',
        headers: getAuthHeader(),
        body: JSON.stringify({ role_id: roleId })
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to update user role')
    }
  },

  deleteAdminUser: async (userId) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/users/${userId}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to delete user')
    }
  },

  // ============ WASTE DEPOSITS ADDITIONAL METHODS ============
  getPenyetoranSampahById: async (id) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penyetoran-sampah/${id}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch waste deposit')
    }
  },

  approvePenyetoranSampah: async (id, catatan = '') => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penyetoran-sampah/${id}/approve`, {
        method: 'PATCH',
        headers: getAuthHeader(),
        body: JSON.stringify({ catatan_admin: catatan })
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to approve waste deposit')
    }
  },

  rejectPenyetoranSampah: async (id, catatan = '') => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penyetoran-sampah/${id}/reject`, {
        method: 'PATCH',
        headers: getAuthHeader(),
        body: JSON.stringify({ catatan_admin: catatan })
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to reject waste deposit')
    }
  },

  getPenyetoranStats: async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penyetoran-sampah/stats/overview`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch waste deposit stats')
    }
  },

  deletePenyetoranSampah: async (id) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/penyetoran-sampah/${id}`, {
        method: 'DELETE',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to delete waste deposit')
    }
  },

  // ============ BADGE ADDITIONAL METHODS ============
  getBadgeAdminById: async (id) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/badges/${id}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch badge')
    }
  },

  getUsersWithBadge: async (badgeId, page = 1, perPage = 50) => {
    try {
      const params = new URLSearchParams({ page, per_page: perPage })
      const response = await fetch(`${API_BASE_URL}/admin/badges/${badgeId}/users?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch users with badge')
    }
  },

  // ============ PRODUCT ADDITIONAL METHODS ============
  getProdukById: async (id) => {
    try {
      const response = await fetch(`${API_BASE_URL}/admin/produk/${id}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch product')
    }
  },

  // ============ ANALYTICS ADDITIONAL METHODS ============
  getWasteByUserAnalytics: async (page = 1, perPage = 50) => {
    try {
      const params = new URLSearchParams({ page, per_page: perPage })
      const response = await fetch(`${API_BASE_URL}/admin/analytics/waste-by-user?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch waste by user analytics')
    }
  },

  // ============ ACTIVITY LOGS ENDPOINTS ============
  getAllActivityLogs: async (page = 1, perPage = 50, filters = {}) => {
    try {
      const params = new URLSearchParams({ page, per_page: perPage })
      if (filters.user_id) params.append('user_id', filters.user_id)
      if (filters.activity_type) params.append('activity_type', filters.activity_type)
      if (filters.date_from) params.append('date_from', filters.date_from)
      if (filters.date_to) params.append('date_to', filters.date_to)
      
      const response = await fetch(`${API_BASE_URL}/admin/activity-logs?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch activity logs')
    }
  },

  getActivityLogsStats: async (dateFrom = null, dateTo = null) => {
    try {
      const params = new URLSearchParams()
      if (dateFrom) params.append('date_from', dateFrom)
      if (dateTo) params.append('date_to', dateTo)
      
      const response = await fetch(`${API_BASE_URL}/admin/activity-logs/stats/overview?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch activity logs stats')
    }
  },

  exportActivityLogsCSV: async (filters = {}) => {
    try {
      const params = new URLSearchParams()
      if (filters.user_id) params.append('user_id', filters.user_id)
      if (filters.date_from) params.append('date_from', filters.date_from)
      if (filters.date_to) params.append('date_to', filters.date_to)
      
      const response = await fetch(`${API_BASE_URL}/admin/activity-logs/export/csv?${params}`, {
        method: 'GET',
        headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
      })
      
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      
      const blob = await response.blob()
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `activity_logs_${new Date().toISOString().split('T')[0]}.csv`
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      URL.revokeObjectURL(url)
      
      return { success: true }
    } catch (error) {
      return handleError(error, 'Failed to export activity logs')
    }
  },

  getUserActivityLogs: async (userId, page = 1, perPage = 50) => {
    try {
      const params = new URLSearchParams({ page, per_page: perPage })
      const response = await fetch(`${API_BASE_URL}/admin/users/${userId}/activity-logs?${params}`, {
        method: 'GET',
        headers: getAuthHeader()
      })
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      const data = await response.json()
      return { success: true, data: data.data || data }
    } catch (error) {
      return handleError(error, 'Failed to fetch user activity logs')
    }
  }
}

export default adminApi