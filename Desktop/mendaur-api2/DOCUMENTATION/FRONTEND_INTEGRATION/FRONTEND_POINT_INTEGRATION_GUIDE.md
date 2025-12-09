# 💻 FRONTEND POINT SYSTEM INTEGRATION GUIDE

**For React Frontend Development**

---

## 🎯 Overview

After the backend is complete, implement these React components to display points to users.

---

## 📋 Components to Build

1. **UserPointSummary** - Show total points
2. **PointHistory** - List recent transactions
3. **RedeemForm** - Redeem points for products
4. **RedeemHistory** - Show past redemptions
5. **PointDashboard** - Combine all above

---

## 1️⃣ Component: UserPointSummary

**Purpose:** Display user's current total points

**File:** `src/components/UserPointSummary.jsx`

```jsx
import React, { useEffect, useState } from 'react';
import './UserPointSummary.css';

export default function UserPointSummary({ userId }) {
  const [pointData, setPointData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchPoints();
  }, [userId]);

  const fetchPoints = async () => {
    try {
      setLoading(true);
      const response = await fetch(`http://127.0.0.1:8000/api/user/${userId}/poin`);
      if (!response.ok) throw new Error('Gagal mengambil data poin');
      const data = await response.json();
      setPointData(data.data);
    } catch (err) {
      setError(err.message);
      console.error('Error fetching points:', err);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div className="loading">Memuat...</div>;
  if (error) return <div className="error">Error: {error}</div>;
  if (!pointData) return <div>Tidak ada data</div>;

  const { user, statistics } = pointData;

  return (
    <div className="point-summary">
      <div className="summary-card">
        <div className="point-large">
          <span className="label">Total Poin Saya</span>
          <span className="value">{user.total_poin}</span>
          <span className="label-small">poin</span>
        </div>
      </div>

      <div className="stats-grid">
        <div className="stat-card">
          <span className="stat-label">Total Diperoleh</span>
          <span className="stat-value">{statistics.total_earned}</span>
        </div>
        <div className="stat-card">
          <span className="stat-label">Total Digunakan</span>
          <span className="stat-value">{statistics.total_spent}</span>
        </div>
        <div className="stat-card">
          <span className="stat-label">Transaksi</span>
          <span className="stat-value">{statistics.transaction_count}</span>
        </div>
      </div>

      <button onClick={fetchPoints} className="btn-refresh">
        🔄 Perbarui
      </button>
    </div>
  );
}
```

**CSS:** `src/components/UserPointSummary.css`

```css
.point-summary {
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  color: white;
}

.summary-card {
  text-align: center;
  margin-bottom: 20px;
}

.point-large {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.point-large .value {
  font-size: 48px;
  font-weight: bold;
}

.point-large .label-small {
  font-size: 14px;
  opacity: 0.9;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 15px;
  margin-bottom: 20px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.1);
  padding: 15px;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-label {
  font-size: 12px;
  opacity: 0.8;
  margin-bottom: 8px;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
}

.btn-refresh {
  width: 100%;
  padding: 10px;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-refresh:hover {
  background: rgba(255, 255, 255, 0.3);
}

.loading, .error {
  padding: 20px;
  text-align: center;
  border-radius: 8px;
}

.error {
  background: #ffebee;
  color: #c62828;
}
```

---

## 2️⃣ Component: PointHistory

**Purpose:** Show recent point transactions

**File:** `src/components/PointHistory.jsx`

```jsx
import React, { useEffect, useState } from 'react';
import './PointHistory.css';

export default function PointHistory({ userId }) {
  const [transactions, setTransactions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('all');

  useEffect(() => {
    fetchHistory();
  }, [userId, filter]);

  const fetchHistory = async () => {
    try {
      setLoading(true);
      const url = filter === 'all'
        ? `http://127.0.0.1:8000/api/user/${userId}/poin`
        : `http://127.0.0.1:8000/api/poin/history?sumber=${filter}`;
      
      const response = await fetch(url);
      if (!response.ok) throw new Error('Gagal mengambil riwayat');
      const data = await response.json();
      setTransactions(data.data.recent_transactions || data.data);
    } catch (err) {
      console.error('Error fetching history:', err);
    } finally {
      setLoading(false);
    }
  };

  const getSourceIcon = (sumber) => {
    const icons = {
      'setor_sampah': '♻️',
      'bonus': '✨',
      'badge': '🏆',
      'event': '🎉',
      'redemption': '🎁',
      'manual': '⭐',
    };
    return icons[sumber] || '📝';
  };

  const getPointClass = (points) => points > 0 ? 'positive' : 'negative';
  const getPointSign = (points) => points > 0 ? '+' : '';

  return (
    <div className="point-history">
      <h3>Riwayat Poin</h3>

      <div className="filter-buttons">
        <button
          className={`btn ${filter === 'all' ? 'active' : ''}`}
          onClick={() => setFilter('all')}
        >
          Semua
        </button>
        <button
          className={`btn ${filter === 'setor_sampah' ? 'active' : ''}`}
          onClick={() => setFilter('setor_sampah')}
        >
          Setor
        </button>
        <button
          className={`btn ${filter === 'bonus' ? 'active' : ''}`}
          onClick={() => setFilter('bonus')}
        >
          Bonus
        </button>
        <button
          className={`btn ${filter === 'redemption' ? 'active' : ''}`}
          onClick={() => setFilter('redemption')}
        >
          Penukaran
        </button>
      </div>

      {loading && <div className="loading">Memuat...</div>}

      {!loading && transactions.length === 0 && (
        <div className="empty">Belum ada transaksi</div>
      )}

      {!loading && transactions.length > 0 && (
        <div className="transaction-list">
          {transactions.map((tx) => (
            <div key={tx.id} className="transaction-item">
              <div className="tx-icon">
                {getSourceIcon(tx.sumber)}
              </div>
              <div className="tx-info">
                <div className="tx-source">{tx.sumber_label}</div>
                <div className="tx-description">{tx.keterangan}</div>
                <div className="tx-date">{tx.tanggal}</div>
              </div>
              <div className={`tx-points ${getPointClass(tx.poin_didapat)}`}>
                {getPointSign(tx.poin_didapat)}{Math.abs(tx.poin_didapat)}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
```

**CSS:** `src/components/PointHistory.css`

```css
.point-history {
  padding: 20px;
  border-radius: 12px;
  background: white;
  border: 1px solid #e0e0e0;
}

.point-history h3 {
  margin-top: 0;
  margin-bottom: 15px;
  color: #333;
}

.filter-buttons {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.filter-buttons .btn {
  padding: 8px 16px;
  background: #f0f0f0;
  border: 1px solid #ddd;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
}

.filter-buttons .btn:hover {
  background: #e0e0e0;
}

.filter-buttons .btn.active {
  background: #4CAF50;
  color: white;
  border-color: #4CAF50;
}

.transaction-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.transaction-item {
  display: flex;
  align-items: center;
  padding: 12px;
  background: #f9f9f9;
  border-radius: 8px;
  border-left: 4px solid #4CAF50;
}

.tx-icon {
  font-size: 24px;
  margin-right: 12px;
  min-width: 30px;
  text-align: center;
}

.tx-info {
  flex: 1;
}

.tx-source {
  font-weight: 600;
  color: #333;
  margin-bottom: 4px;
}

.tx-description {
  font-size: 13px;
  color: #666;
  margin-bottom: 4px;
}

.tx-date {
  font-size: 12px;
  color: #999;
}

.tx-points {
  font-weight: bold;
  font-size: 16px;
  min-width: 60px;
  text-align: right;
}

.tx-points.positive {
  color: #4CAF50;
}

.tx-points.negative {
  color: #f44336;
}

.empty {
  text-align: center;
  padding: 40px 20px;
  color: #999;
}

.loading {
  text-align: center;
  padding: 20px;
  color: #666;
}
```

---

## 3️⃣ Component: RedeemForm

**Purpose:** Redeem points for products

**File:** `src/components/RedeemForm.jsx`

```jsx
import React, { useState, useEffect } from 'react';
import './RedeemForm.css';

export default function RedeemForm({ userId, onSuccess }) {
  const [products, setProducts] = useState([]);
  const [formData, setFormData] = useState({
    produk_id: '',
    poin_digunakan: '',
    metode_ambil: 'pickup',
  });
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');
  const [userPoints, setUserPoints] = useState(0);

  useEffect(() => {
    fetchProducts();
    fetchUserPoints();
  }, [userId]);

  const fetchProducts = async () => {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/produk');
      const data = await response.json();
      setProducts(data.data || []);
    } catch (err) {
      console.error('Error fetching products:', err);
    }
  };

  const fetchUserPoints = async () => {
    try {
      const response = await fetch(`http://127.0.0.1:8000/api/user/${userId}/poin`);
      const data = await response.json();
      setUserPoints(data.data.user.total_poin);
    } catch (err) {
      console.error('Error fetching user points:', err);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSelectProduct = (product) => {
    setFormData(prev => ({
      ...prev,
      produk_id: product.id,
      poin_digunakan: product.poin,
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!formData.produk_id || !formData.poin_digunakan) {
      setMessage('⚠️ Pilih produk terlebih dahulu');
      return;
    }

    if (userPoints < formData.poin_digunakan) {
      setMessage('❌ Poin Anda tidak cukup');
      return;
    }

    try {
      setLoading(true);
      const response = await fetch('http://127.0.0.1:8000/api/penukaran-produk', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify({
          produk_id: parseInt(formData.produk_id),
          poin_digunakan: parseInt(formData.poin_digunakan),
          metode_ambil: formData.metode_ambil,
        }),
      });

      const data = await response.json();
      
      if (response.ok) {
        setMessage(`✅ ${data.message}`);
        setFormData({ produk_id: '', poin_digunakan: '', metode_ambil: 'pickup' });
        setUserPoints(data.data.remaining_poin);
        if (onSuccess) onSuccess();
      } else {
        setMessage(`❌ ${data.message}`);
      }
    } catch (err) {
      setMessage(`❌ Terjadi kesalahan: ${err.message}`);
    } finally {
      setLoading(false);
    }
  };

  const selectedProduct = products.find(p => p.id == formData.produk_id);

  return (
    <div className="redeem-form">
      <h3>Tukar Poin Anda</h3>
      
      <div className="point-balance">
        Poin Tersedia: <strong>{userPoints}</strong>
      </div>

      {message && <div className={`message ${message.includes('✅') ? 'success' : 'error'}`}>
        {message}
      </div>}

      <form onSubmit={handleSubmit}>
        <div className="form-section">
          <h4>Pilih Produk</h4>
          <div className="product-grid">
            {products.map(product => (
              <div
                key={product.id}
                className={`product-card ${selectedProduct?.id === product.id ? 'selected' : ''}`}
                onClick={() => handleSelectProduct(product)}
              >
                <img src={product.foto} alt={product.nama} />
                <div className="product-name">{product.nama}</div>
                <div className="product-points">{product.poin} poin</div>
              </div>
            ))}
          </div>
        </div>

        {selectedProduct && (
          <div className="form-section">
            <h4>Detail Penukaran</h4>
            <div className="detail-row">
              <span>Produk:</span>
              <strong>{selectedProduct.nama}</strong>
            </div>
            <div className="detail-row">
              <span>Poin yang digunakan:</span>
              <strong>{formData.poin_digunakan} poin</strong>
            </div>
            <div className="detail-row">
              <span>Sisa poin:</span>
              <strong className={userPoints - formData.poin_digunakan < 0 ? 'error' : ''}>
                {userPoints - formData.poin_digunakan} poin
              </strong>
            </div>
          </div>
        )}

        <div className="form-section">
          <h4>Metode Pengambilan</h4>
          <div className="radio-group">
            <label>
              <input
                type="radio"
                name="metode_ambil"
                value="pickup"
                checked={formData.metode_ambil === 'pickup'}
                onChange={handleChange}
              />
              Ambil di Lokasi
            </label>
            <label>
              <input
                type="radio"
                name="metode_ambil"
                value="delivery"
                checked={formData.metode_ambil === 'delivery'}
                onChange={handleChange}
              />
              Pengiriman ke Rumah
            </label>
          </div>
        </div>

        <button
          type="submit"
          disabled={loading || !selectedProduct}
          className="btn-submit"
        >
          {loading ? 'Memproses...' : 'Tukar Poin'}
        </button>
      </form>
    </div>
  );
}
```

**CSS:** `src/components/RedeemForm.css`

```css
.redeem-form {
  padding: 20px;
  border-radius: 12px;
  background: white;
  border: 1px solid #e0e0e0;
}

.redeem-form h3 {
  margin-top: 0;
  color: #333;
}

.point-balance {
  background: #e8f5e9;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
  color: #2e7d32;
}

.message {
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 15px;
}

.message.success {
  background: #c8e6c9;
  color: #2e7d32;
  border-left: 4px solid #4CAF50;
}

.message.error {
  background: #ffcdd2;
  color: #c62828;
  border-left: 4px solid #f44336;
}

.form-section {
  margin-bottom: 25px;
}

.form-section h4 {
  margin-top: 0;
  margin-bottom: 15px;
  color: #555;
  font-size: 16px;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 15px;
}

.product-card {
  border: 2px solid #ddd;
  border-radius: 8px;
  padding: 12px;
  cursor: pointer;
  transition: all 0.3s;
  text-align: center;
}

.product-card:hover {
  border-color: #4CAF50;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.product-card.selected {
  border-color: #4CAF50;
  background: #f1f8e9;
  box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
}

.product-card img {
  width: 100%;
  height: 100px;
  object-fit: cover;
  border-radius: 4px;
  margin-bottom: 8px;
}

.product-name {
  font-weight: 600;
  margin-bottom: 6px;
  color: #333;
  font-size: 14px;
}

.product-points {
  color: #4CAF50;
  font-weight: bold;
  font-size: 14px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #f0f0f0;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row span {
  color: #666;
}

.detail-row strong {
  color: #333;
}

.detail-row strong.error {
  color: #f44336;
}

.radio-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.radio-group label {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.radio-group input[type="radio"] {
  margin-right: 10px;
  cursor: pointer;
}

.btn-submit {
  width: 100%;
  padding: 12px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-submit:hover:not(:disabled) {
  background: #45a049;
}

.btn-submit:disabled {
  background: #ccc;
  cursor: not-allowed;
}
```

---

## 4️⃣ Component: RedeemHistory

**Purpose:** Show past redemptions

**File:** `src/components/RedeemHistory.jsx`

```jsx
import React, { useEffect, useState } from 'react';

export default function RedeemHistory({ userId }) {
  const [history, setHistory] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchHistory();
  }, [userId]);

  const fetchHistory = async () => {
    try {
      const response = await fetch(`http://127.0.0.1:8000/api/user/${userId}/redeem-history`);
      const data = await response.json();
      setHistory(data.data || []);
    } catch (err) {
      console.error('Error:', err);
    } finally {
      setLoading(false);
    }
  };

  const getStatusBadge = (status) => {
    const badges = {
      'pending': { bg: '#fff3e0', color: '#f57c00', text: 'Menunggu' },
      'approved': { bg: '#e8f5e9', color: '#2e7d32', text: 'Disetujui' },
      'rejected': { bg: '#ffebee', color: '#c62828', text: 'Ditolak' },
    };
    return badges[status] || badges.pending;
  };

  return (
    <div style={{ padding: '20px', borderRadius: '12px', background: 'white' }}>
      <h3>Riwayat Penukaran</h3>
      
      {loading && <div>Memuat...</div>}
      
      {!loading && history.length === 0 && <div>Belum ada penukaran</div>}
      
      {!loading && history.map(item => {
        const badge = getStatusBadge(item.status);
        return (
          <div key={item.id} style={{
            padding: '15px',
            border: '1px solid #e0e0e0',
            borderRadius: '8px',
            marginBottom: '10px',
          }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '10px' }}>
              <strong>{item.produk}</strong>
              <span style={{
                background: badge.bg,
                color: badge.color,
                padding: '4px 8px',
                borderRadius: '4px',
                fontSize: '12px',
                fontWeight: '600',
              }}>
                {badge.text}
              </span>
            </div>
            <div style={{ fontSize: '14px', color: '#666' }}>
              Poin: {item.poin_digunakan} | Tanggal: {item.tanggal_penukaran}
            </div>
          </div>
        );
      })}
    </div>
  );
}
```

---

## 5️⃣ Master Component: PointDashboard

**Purpose:** Combine all components

**File:** `src/pages/PointDashboard.jsx`

```jsx
import React from 'react';
import UserPointSummary from '../components/UserPointSummary';
import PointHistory from '../components/PointHistory';
import RedeemForm from '../components/RedeemForm';
import RedeemHistory from '../components/RedeemHistory';

export default function PointDashboard({ userId }) {
  return (
    <div style={{ maxWidth: '1000px', margin: '0 auto', padding: '20px' }}>
      <h1>💰 Sistem Poin Saya</h1>

      <div style={{ marginBottom: '30px' }}>
        <UserPointSummary userId={userId} />
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', gap: '20px' }}>
        <div>
          <PointHistory userId={userId} />
        </div>
        
        <div>
          <RedeemForm userId={userId} />
        </div>
      </div>

      <div style={{ marginTop: '30px' }}>
        <RedeemHistory userId={userId} />
      </div>
    </div>
  );
}
```

---

## 🔌 API Integration Checklist

```
✅ GET /api/user/{id}/poin
   Returns: total_poin, recent_transactions, statistics

✅ GET /api/poin/history
   Returns: paginated point transactions

✅ GET /api/user/{id}/redeem-history
   Returns: redemption history

✅ POST /api/penukaran-produk
   Body: produk_id, poin_digunakan, metode_ambil
   Returns: redemption_id, remaining_poin

✅ GET /api/produk
   Returns: available products for redemption

✅ GET /api/user/{id}/poin/statistics
   Returns: earned, spent, breakdown by source
```

---

## 🧪 Testing

### **Test 1: Display Points**
```jsx
<UserPointSummary userId={1} />
// Should show: Total 320 poin
```

### **Test 2: Show History**
```jsx
<PointHistory userId={1} />
// Should list: Recent transactions with icons
```

### **Test 3: Redeem Product**
```jsx
<RedeemForm userId={1} onSuccess={() => alert('Redeemed!')} />
// Should: Show products, allow selection, submit redemption
```

---

## 🎨 Styling Recommendations

- Use your app's primary color for highlights
- Green (#4CAF50) for positive points
- Red (#f44336) for negative points
- Purple gradient for dashboard header
- Clean, spacious layout

---

## ✅ Frontend Checklist

```
Components:
  ☐ UserPointSummary created
  ☐ PointHistory created
  ☐ RedeemForm created
  ☐ RedeemHistory created
  ☐ PointDashboard created

Integration:
  ☐ Fetch API endpoints correctly
  ☐ Display total points
  ☐ Show transaction history
  ☐ Allow product redemption
  ☐ Show redemption history

Features:
  ☐ Filter history by source
  ☐ Refresh button works
  ☐ Error handling
  ☐ Loading states
  ☐ Success messages

Styling:
  ☐ Responsive layout
  ☐ Consistent colors
  ☐ Good typography
  ☐ Icons for visual clarity
```

---

**After backend is ready, use this guide to build a beautiful frontend point system! 🚀**
