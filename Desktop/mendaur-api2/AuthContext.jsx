import React, { createContext, useContext, useState, useEffect } from 'react';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [role, setRole] = useState(null);
  const [loading, setLoading] = useState(true);

  // Load user from localStorage on mount
  useEffect(() => {
    const storedUser = localStorage.getItem('user');
    const storedToken = localStorage.getItem('token');
    const storedRole = localStorage.getItem('role');
    
    if (storedUser && storedToken) {
      try {
        setUser(JSON.parse(storedUser));
        setRole(storedRole || 'user');
      } catch (error) {
        console.error('Failed to parse user data:', error);
        localStorage.removeItem('user');
        localStorage.removeItem('token');
        localStorage.removeItem('role');
      }
    }
    setLoading(false);
  }, []);

  const login = (userData) => {
    const user_data = userData.user || userData;
    const userRole = user_data.role || 'user';
    
    setUser(user_data);
    setRole(userRole);
    localStorage.setItem('user', JSON.stringify(user_data));
    localStorage.setItem('token', userData.token);
    localStorage.setItem('role', userRole);
    localStorage.setItem('id_user', user_data.id); // For backward compatibility
  };

  const logout = () => {
    setUser(null);
    setRole(null);
    localStorage.removeItem('user');
    localStorage.removeItem('token');
    localStorage.removeItem('role');
    localStorage.removeItem('id_user');
  };

  const updateUser = (updatedUserData) => {
    setUser(updatedUserData);
    localStorage.setItem('user', JSON.stringify(updatedUserData));
  };

  const isAdmin = role === 'admin' || role === 'superadmin';

  const value = {
    user,
    role,
    login,
    logout,
    updateUser,
    isAuthenticated: !!user,
    isAdmin,
    loading,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

// Custom hook to use auth context
// eslint-disable-next-line react-refresh/only-export-components
export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
