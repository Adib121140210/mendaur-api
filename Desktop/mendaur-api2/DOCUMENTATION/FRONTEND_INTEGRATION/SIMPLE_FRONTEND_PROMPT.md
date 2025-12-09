# 📋 FRONTEND AGENT - COPY & PASTE PROMPT

**You can copy this entire section and send it to your Frontend Agent via chat/email**

---

## 🎯 TASK: Build Admin Dashboard for Mendaur System

I need you to build a **React Admin Dashboard** UI component for our Mendaur waste management system.

### 📊 Dashboard Features to Build:

**1. Overview Cards**
- Total Users
- Total Waste Collected (kg)
- Total Points Distributed
- Active Users (last 30 days)

**2. User Management Table**
- Display all users with pagination
- Columns: Name, Email, Phone, Total Points, Level, Total Deposits
- Search functionality
- Sortable columns
- Pagination controls

**3. Waste Analytics Section**
- Toggle between Daily/Monthly/Yearly view
- Line chart showing waste trends
- Pie chart showing waste by type (Kertas, Plastik, Logam, Kaca, Organik)
- Date pickers to select time period
- Display total kg and deposit count

**4. Points Distribution Section**
- Show points given per period
- Breakdown by source (setor_sampah, bonus, tukar_poin, badge, manual)
- Chart showing points trend
- Summary cards with key metrics

**5. Waste by User Table**
- Show each user's waste contribution
- Columns: User Name, Waste Type, Total kg, Points Earned, # of Deposits
- Filter by user and date range
- Export to CSV option

**6. Reports Section**
- Daily Report generator
- Monthly Report generator
- Show report in collapsible/modal format
- Export to PDF or Excel
- Print functionality

---

## 🔌 API Endpoints Available:

All endpoints require Bearer token in header:
```
Authorization: Bearer {token}
```

**Endpoint List:**
1. `GET /admin/dashboard/overview` - Get dashboard overview stats
2. `GET /admin/dashboard/users` - Get all users list (paginated)
3. `GET /admin/dashboard/waste-summary` - Get waste data by period
4. `GET /admin/dashboard/point-summary` - Get points data by period
5. `GET /admin/dashboard/waste-by-user` - Get waste breakdown by user
6. `GET /admin/dashboard/report` - Generate daily/monthly reports

**Base URL:** `http://127.0.0.1:8000/api`

### Complete API Documentation:
📄 See file: `API_ENDPOINTS_QUICK_REFERENCE.md`

---

## 🎨 UI/UX Requirements:

- **Responsive Design**: Works on desktop, tablet, mobile
- **Color Scheme**: Green (#023d2d) primary, Blue (#facc15) secondary
- **Charts**: Use Chart.js, Recharts, or D3.js
- **Layout**: Dashboard with cards, charts, tables, and filters
- **Loading States**: Show spinners while loading data
- **Error Handling**: Display error messages if API calls fail
- **Date Filtering**: Period selector, date pickers
- **Export**: PDF/Excel export for reports

---

## 📝 Deliverables Expected:

1. ✅ Main Dashboard component
2. ✅ Sub-components (Overview, Users, Waste, Points, Reports)
3. ✅ API service/hooks for data fetching
4. ✅ State management (Redux/Context/Vuex)
5. ✅ Styling (responsive CSS)
6. ✅ Error handling & loading states
7. ✅ README documentation
8. ✅ Integration ready with backend

---

## 🚀 Quick Start:

1. **Get the full prompt with detailed specs:**
   📄 See file: `FRONTEND_AGENT_ADMIN_DASHBOARD_PROMPT.md`

2. **API Quick Reference:**
   📄 See file: `API_ENDPOINTS_QUICK_REFERENCE.md`

3. **Data Tables Guide:**
   📄 See file: `ADMIN_DASHBOARD_DATA_TABLES_GUIDE.md`

---

## 💡 Important Notes:

- Backend API is fully implemented and ready ✅
- Database has real data to work with ✅
- All endpoints tested and working ✅
- Use pagination for user list (default 10 per page)
- Cache data to reduce API calls
- Handle network errors gracefully
- Timestamps are in ISO 8601 format

---

## 📞 Support & Questions:

- Backend API running on: http://127.0.0.1:8000
- Documentation files available in project root
- All API endpoints are authenticated (Sanctum bearer token)
- Admin role required for all dashboard endpoints

---

**Ready to build? Let me know if you need any clarifications! 🎉**

