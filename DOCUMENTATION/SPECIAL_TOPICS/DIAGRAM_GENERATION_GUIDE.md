# 🎯 QUICK REFERENCE - DIAGRAM GENERATION GUIDE

**Use this card when you're ready to generate diagrams and write your report**

---

## 📋 THE 6 DIAGRAMS YOU NEED

### 1. OVERVIEW DIAGRAM (Easiest)
**Time**: 5 min | **Complexity**: Low  
**Location**: DIAGRAM_TEMPLATES_SPECIFICATIONS.md, line ~35  
**Content**: 8 main processes, 4 actors  
**Output filename**: `UC_01_Overview.png`  
**Use in report**: Page 1 (introduction)

---

### 2. NASABAH (USER) DETAILED (Easy)
**Time**: 10 min | **Complexity**: Low  
**Location**: DIAGRAM_TEMPLATES_SPECIFICATIONS.md, line ~100  
**Content**: 18 user features  
**Output filename**: `UC_02_Nasabah_Detailed.png`  
**Use in report**: Page 2

---

### 3. ADMIN (OPERATOR) DETAILED (Medium) ⭐ UPDATED TODAY
**Time**: 15 min | **Complexity**: Medium  
**Location**: DIAGRAM_TEMPLATES_SPECIFICATIONS.md, line ~155  
**Content**: 35 features (8 packages)  
**Output filename**: `UC_03_Admin_Detailed.png`  
**Use in report**: Page 3  
**Print size**: A4 Landscape

**Packages** (8):
1. Waste Management (10 UC)
2. Product Redemption (9 UC)
3. Cash Withdrawal (4 UC)
4. Point Management (4 UC)
5. Badge Management (5 UC)
6. User Management (6 UC)
7. Content Management (7 UC)
8. Analytics & Reporting (4 UC)

---

### 4. SUPERADMIN (GOVERNOR) DETAILED (Medium) ⭐ UPDATED TODAY
**Time**: 10 min | **Complexity**: Medium  
**Location**: DIAGRAM_TEMPLATES_SPECIFICATIONS.md, line ~240  
**Content**: 15 features (3 packages) - GOVERNANCE ONLY  
**Output filename**: `UC_04_Superadmin_Detailed.png`  
**Use in report**: Page 4  
**Print size**: A4 Landscape

**Packages** (3):
1. Admin Account Management (6 UC)
2. System Audit & Monitoring (5 UC)
3. System Configuration (4 UC)

**KEY**: Superadmin does NOT manage features

---

### 5. SYSTEM PROCESSES (Easy)
**Time**: 5 min | **Complexity**: Low  
**Location**: DIAGRAM_TEMPLATES_SPECIFICATIONS.md, line ~310  
**Content**: 5 background processes  
**Output filename**: `UC_05_System_Processes.png`  
**Use in report**: Page 5

---

### 6. COMPLETE DETAILED (Hard)
**Time**: 30 min | **Complexity**: High  
**Location**: DIAGRAM_TEMPLATES_SPECIFICATIONS.md, line ~345  
**Content**: ALL 73 features combined  
**Output filename**: `UC_06_Complete_Reference.png`  
**Use in report**: Appendix or page 6  
**Print size**: A3 Landscape (or A4 if fitting)

---

## ⚡ QUICK START (5 MINUTES)

1. **Go to**: https://www.plantuml.com/plantuml/uml/

2. **Open file**: `DIAGRAM_TEMPLATES_SPECIFICATIONS.md`

3. **Find section**: "### Overview Diagram (Main Business Processes)"

4. **Copy code**: From `@startuml` to `@enduml` (about 20 lines)

5. **Paste** into PlantUML text area

6. **Click** "Submit" button

7. **Right-click** the image → "Save As" → `UC_01_Overview.png`

8. **Repeat** for other 5 diagrams

**Total time**: 30-45 minutes for all 6 diagrams

---

## 📊 ROLE BREAKDOWN (MEMORIZE THIS)

### NASABAH (User) - 18 UC
```
I can:
✅ Manage my profile
✅ Submit waste deposits
✅ View my points & badges
✅ Redeem products
✅ Request cash withdrawals
✅ See my history & leaderboard

I CANNOT:
❌ Approve anything
❌ Manage other users
❌ Manage products or articles
❌ View system configuration
```

### ADMIN (Operator) - 35 UC ⭐ NEW
```
I can:
✅ Approve/Reject all user requests
✅ Create/Edit/Delete all system entities
   (Products, Badges, Articles, Categories)
✅ Manage all users
✅ View all analytics & reports
✅ Process withdrawals & transfers

I CANNOT:
❌ Manage other admin accounts
❌ View system audit logs
❌ Change system configuration
❌ Manage roles/permissions
```

### SUPERADMIN (Governor) - 15 UC ⭐ NEW
```
I can:
✅ Create/Edit/Delete admin accounts
✅ View complete audit logs
✅ Monitor system performance
✅ Manage system configuration
✅ Manage roles & permissions
✅ View ALL transactions (audit)

I CANNOT:
❌ Approve user transactions
❌ Manage products/badges/articles
❌ Delete user data
❌ Edit user profiles
```

---

## 📁 IMPORTANT FILES (BOOKMARK THESE)

| File | Purpose | Use For |
|------|---------|---------|
| DIAGRAM_TEMPLATES_SPECIFICATIONS.md | Main UCD templates | Diagram code |
| FEATURE_MATRIX_FOR_DIAGRAMS.md | Feature descriptions | Feature list |
| NEXT_STEPS_ACTION_PLAN.md | Your action plan | This week's tasks |
| DATABASE_ERD_VISUAL_DETAILED.md | Database info | ERD creation |
| FITUR_SISTEM_LENGKAP_BAHASA_INDONESIA.md | Indonesian features | Bahasa descriptions |

---

## 🔄 DIAGRAM GENERATION WORKFLOW

```
1. OVERVIEW (5 min)
   ↓
2. NASABAH (10 min)
   ↓
3. ADMIN ⭐ (15 min)
   ↓
4. SUPERADMIN ⭐ (10 min)
   ↓
5. SYSTEM (5 min)
   ↓
6. COMPLETE (30 min)
   ↓
DONE! 75 minutes total
```

---

## ✅ BEFORE WRITING YOUR REPORT

- [ ] All 6 UCD diagrams generated & saved
- [ ] Physical ERD diagram created
- [ ] Feature permission matrix prepared
- [ ] Role descriptions printed/noted
- [ ] Report outline ready

---

## 📝 REPORT STRUCTURE (Quick outline)

```
PAGES 1-2: Introduction & Overview
→ INSERT: UC_01_Overview.png

PAGES 3-4: Nasabah Role (18 UC)
→ INSERT: UC_02_Nasabah_Detailed.png

PAGES 5-8: Admin Role ⭐ (35 UC)
→ INSERT: UC_03_Admin_Detailed.png

PAGES 9-10: Superadmin Role ⭐ (15 UC)
→ INSERT: UC_04_Superadmin_Detailed.png

PAGE 11: System Processes (5 UC)
→ INSERT: UC_05_System_Processes.png

PAGE 12: Database Design
→ INSERT: ERD_Physical_Diagram.png

PAGE 13: Permission Matrix
→ INSERT: Feature_Permission_Matrix

PAGES 14+: Implementation & Conclusion

APPENDIX: Complete Reference
→ INSERT: UC_06_Complete_Reference.png
```

---

## 🎯 WHAT'S DIFFERENT TODAY

### Changes Made:
✅ **Admin**: 26 UC → **35 UC** (added 9 features)
✅ **Superadmin**: 19 UC → **15 UC** (removed 4)
✅ **Total**: 68 UC → **73 UC**

### Why It Matters:
- **Clear authority**: Admin operates features, Superadmin governs system
- **No conflicts**: Each role has distinct scope
- **Professional**: Proper separation of concerns
- **Audit-friendly**: Clear accountability

---

## 💡 KEY TALKING POINTS FOR REPORT

**When discussing roles in your thesis:**

1. **Admin as Feature Operator**
   - "The Admin role is responsible for operating ALL application features"
   - "Admin manages 35 use cases across 8 functional areas"

2. **Superadmin as System Governor**
   - "The Superadmin role focuses exclusively on system governance"
   - "Superadmin manages 15 use cases for admin accounts and system config"

3. **Role Hierarchy**
   - "3-level RBAC: User (18 UC) → Admin (35 UC) → Superadmin (15 UC)"
   - "Clear escalation path ensures proper authorization"

---

## 🚀 NEXT 24 HOURS

```
Hour 1:  Generate UC_01 + UC_02
Hour 2:  Generate UC_03 + UC_04
Hour 3:  Generate UC_05 + UC_06
Hour 4:  Create ERD diagram
By EOD:  All diagrams ready for report
```

---

## ✨ YOU'RE READY!

**Everything prepared:**
✅ Clear role definitions  
✅ 73 documented features  
✅ 6 UCD diagrams ready  
✅ Step-by-step guide  
✅ Report structure  

**Next action**: Use PlantUML to create diagrams → Write your report!

---

**Last Updated**: November 29, 2025  
**Status**: Ready to generate diagrams  
**Estimated Report Time**: 4-5 hours writing
