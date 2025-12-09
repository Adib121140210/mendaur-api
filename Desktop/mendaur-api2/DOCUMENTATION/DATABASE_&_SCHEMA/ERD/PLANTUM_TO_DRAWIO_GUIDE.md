# 📊 PlantUML to Draw.io - Step-by-Step Guide

**Date**: November 29, 2025  
**Purpose**: Convert PlantUML code into Draw.io use case diagram

---

## ✅ Method 1: Using Draw.io's PlantUML Import (RECOMMENDED - EASIEST)

### Step 1: Open Draw.io
1. Go to **[draw.io](https://draw.io)** in your browser
   - Or use the desktop app (download from draw.io)
2. Click **"Create New Diagram"**
3. Choose **"Blank Diagram"**
4. Select size: **A3** or **A4 Landscape** (for ERD/Use Case)
5. Click **"Create"**

---

### Step 2: Import PlantUML Code

#### Option A: Using File Menu (Recommended)
```
1. Top Menu: File → Import from
   ↓
2. Select: "URL" 
   ↓
3. Paste the PlantUML server URL:
   https://www.plantuml.com/plantuml/svg/...
```

#### Option B: Using Paste Feature
```
1. Top Menu: Edit → Edit Data
   ↓
2. Paste your PlantUML code directly
```

#### Option C: Using External URL (BEST METHOD)
```
1. Generate PlantUML URL from your code:
   
   Go to: https://www.plantuml.com/plantuml/uml/
   
   Paste your PlantUML code in the text box
   
   Click "Submit"
   
   Copy the resulting URL from the address bar
   
2. In Draw.io: File → Import from → URL
   
3. Paste the URL you copied
   
4. Click "Import"
```

---

### Step 3: Configure Import Settings

When prompted:
```
☑ "Import as Edit Format"
   → Keeps diagram editable as PlantUML

OR

☐ "Import as Read-only SVG"
   → For final presentation (non-editable)
```

**Recommendation**: Import as **Edit Format** so you can customize colors, shapes, positions

---

### Step 4: Auto-Layout Generated Diagram

After import, Draw.io will auto-generate a diagram:

```
Steps to clean up layout:

1. Select All: Ctrl+A

2. Arrange → Organize Layout
   ↓
   Choose: "Vertical" or "Hierarchical" 
   (Hierarchical works best for use cases)

3. Adjust spacing:
   Right-click → Layout → Spacing
   Set: 30-50 pixels

4. Manual adjustments:
   - Drag actors to left side
   - Drag use cases to middle
   - Drag system to right side
```

---

### Step 5: Style & Format the Diagram

#### Color Actors:
```
1. Select actor element
2. Right-click → Style
3. Set FillColor: #ADD8E6 (light blue)
```

#### Color Use Cases:
```
1. Select use case element
2. Right-click → Style
3. Set FillColor: #90EE90 (light green)
```

#### Add Connectors:
```
1. Already imported with relationships
2. Adjust line thickness: Select connector → Format → Width: 2
3. Add arrows: Format → Connector → Arrows: To, From, or Both
```

#### Font Adjustments:
```
1. Select all text: Ctrl+A
2. Format → Text:
   - Font: Arial or Helvetica
   - Size: 11 or 12
   - Style: Bold for actors, Normal for use cases
```

---

### Step 6: Add Grouping & Sections

For organization:

```
1. Draw rectangles around related use cases:
   Shape → Flowchart → Rectangle
   
2. Set border color: #CCCCCC (light gray)
   Set fill: None (transparent)
   
3. Add text labels:
   - "User Features"
   - "Admin Features"
   - "System Features"
   - "Superadmin Features"
```

---

### Step 7: Export Your Diagram

#### Export as PNG (for report):
```
File → Export as → PNG
├─ Zoom: 300% (for print quality)
├─ Transparent background: ☑
└─ Click "Export"
    → Save to: "use_case_diagram.png"
```

#### Export as PDF (for report):
```
File → Export as → PDF
├─ Page size: A3 Landscape
└─ Click "Export"
    → Save to: "use_case_diagram.pdf"
```

#### Export as SVG (scalable):
```
File → Export as → SVG
└─ Click "Export"
    → Save to: "use_case_diagram.svg"
```

#### Save as Draw.io file:
```
File → Save
└─ Save to: "use_case_diagram.drawio"
   (Can edit later!)
```

---

## ✅ Method 2: Manual Creation in Draw.io (If Import Fails)

### Step 1: Create Actors
```
1. Toolbar: left side → Shapes
2. Search: "Actor"
3. Drag actor shape to canvas
4. Double-click to rename:
   ├─ Nasabah
   ├─ Admin
   ├─ Superadmin
   └─ System

5. Arrange on LEFT side of canvas
6. Space them 100px apart vertically
```

---

### Step 2: Create Use Cases
```
1. Toolbar: Shapes → Flowchart → Oval
   OR Use: Insert → Shape → Use Case (oval shape)

2. Create 40+ use cases:
   - Nasabah use cases: Register, Login, View Profile, Submit Deposit, etc.
   - Admin use cases: Approve Deposit, Reject Withdrawal, etc.
   - Superadmin use cases: Create Badge, Manage Roles, etc.

3. Color code by actor:
   Nasabah: Light Blue (#ADD8E6)
   Admin: Light Yellow (#FFFFE0)
   Superadmin: Light Purple (#DDA0DD)
   System: Light Gray (#D3D3D3)

4. Arrange in MIDDLE section of canvas
5. Group by actor (separate rows or sections)
```

---

### Step 3: Connect Actors to Use Cases
```
1. Select actor
2. Click on connection point (red dot)
3. Drag to use case
4. Release

Repeat for all actor-use case pairs

Arrow types:
├─ Solid line = "uses"
└─ Dashed line = "participates"
```

---

### Step 4: Add Include/Extend Relationships
```
1. Use dashed lines for these relationships:
   Select connector → Format → Connector → Line Style: Dashed

2. Label the connector:
   Double-click connector → Type: "<<include>>" or "<<extend>>"

Key include relationships:
├─ Submit Deposit → Calculate Points
├─ Redeem Product → Calculate Points
└─ Approve Withdrawal → Send Notifications

Key extend relationships:
├─ Approve Deposit → Send Notifications
└─ Approve Redemption → Send Notifications
```

---

### Step 5: Add System Boundary (Optional)
```
1. Draw rectangle around all use cases:
   Shape → Rectangle
   
2. Format:
   Border: Black, 2px
   Fill: None (transparent)
   
3. Send to back: Right-click → Arrange → Send to Back

4. Label: Double-click → Type "Mendaur System"
```

---

### Step 6: Export (same as Method 1)

---

## ⚡ Quick Checklist

### Before Export:
- [ ] All actors properly positioned (left side)
- [ ] All use cases properly positioned (middle)
- [ ] All 40+ use cases created
- [ ] All actor-use case relationships connected
- [ ] Include/extend relationships marked
- [ ] Color coding applied
- [ ] Font size readable (11-12pt)
- [ ] Diagram name/title added
- [ ] Grid/guides aligned

### File Naming Convention:
```
use_case_diagram_mendaur_[version].png
use_case_diagram_mendaur_[version].pdf
use_case_diagram_mendaur_[version].drawio

Example:
use_case_diagram_mendaur_v1.png
use_case_diagram_mendaur_v1.pdf
use_case_diagram_mendaur_v1.drawio
```

---

## 📋 PlantUML Code Location

Your PlantUML code is in:
```
File: DIAGRAM_TEMPLATES_SPECIFICATIONS.md
Section: "Use Case Diagram Template"
Format: PlantUML (@startuml ... @enduml)
```

### To Use The Code:

**Option 1: Copy-Paste Method**
```
1. Open: DIAGRAM_TEMPLATES_SPECIFICATIONS.md
2. Find: "Use Case Diagram Template"
3. Copy: Everything between @startuml and @enduml
4. Paste into: https://www.plantuml.com/plantuml/uml/
5. Get URL and import to Draw.io
```

**Option 2: Direct PlantUML Editor**
```
1. Go to: https://www.plantuml.com/plantuml/uml/
2. Paste code
3. Copy resulting URL
4. Use in Draw.io: File → Import from → URL
```

---

## 🎨 Color Codes for Use Cases

```
Nasabah (Regular User):
├─ FillColor: #ADD8E6 (Light Blue)
├─ StrokeColor: #4169E1 (Royal Blue)
└─ Font Color: #000000 (Black)

Admin (Operator):
├─ FillColor: #FFFFE0 (Light Yellow)
├─ StrokeColor: #FFD700 (Gold)
└─ Font Color: #000000 (Black)

Superadmin (System Admin):
├─ FillColor: #DDA0DD (Plum)
├─ StrokeColor: #9932CC (Dark Orchid)
└─ Font Color: #FFFFFF (White)

System (Automatic):
├─ FillColor: #D3D3D3 (Light Gray)
├─ StrokeColor: #696969 (Dim Gray)
└─ Font Color: #000000 (Black)

Relationships:
├─ Include: Dashed Line, #FF6B6B (Red)
├─ Extend: Dashed Line, #4ECDC4 (Teal)
└─ Association: Solid Line, #000000 (Black)
```

---

## 🚀 Alternative Tools (If Draw.io doesn't work)

### Option 1: Lucidchart
```
1. Go to: https://www.lucidchart.com
2. Create new UML diagram
3. Choose "Use Case Diagram"
4. Manually create using template
5. Export as PNG/PDF
```

### Option 2: Visual Paradigm Online
```
1. Go to: https://online.visual-paradigm.com
2. Create: UML Use Case Diagram
3. Drag & drop components
4. Export as PNG/PDF
```

### Option 3: PlantUML Online Editor
```
1. Go to: https://www.plantuml.com/plantuml/uml/
2. Paste your PlantUML code
3. Right-click diagram → Save as PNG
4. Use directly in report
```

### Option 4: PlantUML Renderer
```
For academic/professional use:
1. Install: Node.js + @mermaid-js/mermaid-cli
2. Command: mmdc -i diagram.mmd -o diagram.png
3. High quality SVG/PNG output
```

---

## 💡 Tips & Tricks

### Auto-arrange in Draw.io:
```
After import:
1. Select All: Ctrl+A
2. Menu: Arrange → Organize Layout
3. Choose: "Vertical Flow" or "Compact"
4. Adjust: Arrange → Layout Spacing (30-50px)
```

### Fix overlapping elements:
```
1. Select overlapping element
2. Right-click → Arrange
   ├─ Bring to Front
   ├─ Send to Back
   └─ Align
```

### Add title/legend:
```
1. Insert → Text
2. Position: Top of diagram
3. Format: Bold, 16pt, "Mendaur System - Use Case Diagram"
```

### Add date & version:
```
1. Insert → Text
2. Position: Bottom right
3. Content: "Version 1.0 | November 29, 2025"
```

---

## ❓ Troubleshooting

### Problem: PlantUML import fails
**Solution**: 
- Try using PlantUML Online Editor first
- Export as SVG/PNG from there
- Import into Draw.io as image

### Problem: Diagram too cluttered
**Solution**:
- Use "Organize Layout" multiple times
- Manually separate actors into columns
- Use zoom to work on sections

### Problem: Text too small
**Solution**:
- Select All: Ctrl+A
- Format → Text → Size: 12
- Increase zoom: Ctrl++ (multiple times)

### Problem: Export quality low
**Solution**:
- Export as PDF (vector format)
- For PNG: Use 300% zoom before export
- Use SVG for scalable graphics

---

## 📊 Expected Result

After following these steps, you should have:

```
✅ Professional Use Case Diagram with:
   ├─ 4 Actors (left side)
   ├─ 40+ Use Cases (middle)
   ├─ All relationships connected
   ├─ Color-coded by role
   ├─ Organized layout
   ├─ Clear labeling
   └─ Professional formatting

✅ Multiple export formats:
   ├─ PNG (for digital/print)
   ├─ PDF (for documents)
   ├─ SVG (for scalable graphics)
   └─ DRAWIO (for future editing)

✅ Ready for academic report/presentation
```

---

## 📞 Quick Links

- Draw.io: https://draw.io
- PlantUML: https://www.plantuml.com/plantuml/uml/
- Lucidchart: https://www.lucidchart.com
- Visual Paradigm: https://online.visual-paradigm.com

---

**Next Steps**:
1. Open Draw.io
2. Import PlantUML code using Method 1
3. Format and style the diagram
4. Export as PNG/PDF for your report
5. Repeat same process for Physical ERD using dbdiagram.io

Good luck! 🚀
