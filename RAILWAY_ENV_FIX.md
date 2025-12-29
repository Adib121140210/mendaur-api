# Railway Environment Variables - FIX REQUIRED

## CURRENT ISSUE
Your Railway deployment is failing because of circular/invalid environment variable references.

## REQUIRED CHANGES IN RAILWAY DASHBOARD

Go to Railway Dashboard → Your Service → Variables → Raw Editor

**REPLACE with this (update values from your MySQL service):**

```env
# Application Settings
APP_NAME=Mendaur
APP_ENV=production
APP_KEY=base64:x8kIKxS8FvWYh26qNSzpZA2TTb9EgBEoWiYBOrYdZNE=
APP_DEBUG=false
APP_URL=https://mendaur-api-production.up.railway.app
APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID
APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

# Database Configuration
# ⚠️ CRITICAL: Get these values from your MySQL service!
# Method 1: Click MySQL service → Variables tab → copy values
# Method 2: Use service reference (if MySQL service name is exactly "MySQL"):
DB_CONNECTION=mysql
DB_HOST=${{MySQL.RAILWAY_PRIVATE_DOMAIN}}
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=${{MySQL.MYSQL_ROOT_PASSWORD}}

# OR if service reference doesn't work, hardcode:
# DB_HOST=mysql.railway.internal  (get from MySQL service)
# DB_PASSWORD=LzAVgiPJiZJjGYwOlsgLHbSnrTOFRydA

# Cloudinary Configuration
CLOUDINARY_CLOUD_NAME=dqk8er1qp
CLOUDINARY_API_KEY=724816866574254
CLOUDINARY_API_SECRET=t7wflXyMnaZpSIyaGGT3gJXfOiE
CLOUDINARY_UPLOAD_FOLDER=mendaur

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=adibraihan123@gmail.com
MAIL_PASSWORD="vmlv nxka airt sypn"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=adibraihan123@gmail.com
MAIL_FROM_NAME="Mendaur Bank Sampah"

# Frontend URL (for CORS)
VITE_API_BASE_URL=https://mendaur-api-production.up.railway.app
```

## HOW TO GET MYSQL VALUES

### Option A: From MySQL Service
1. Go to Railway Dashboard
2. Click your **MySQL service** (not the Laravel API service)
3. Go to **Variables** tab
4. Copy these values:
   - `RAILWAY_PRIVATE_DOMAIN` → use as `DB_HOST`
   - `MYSQL_ROOT_PASSWORD` → use as `DB_PASSWORD`
   - `MYSQL_DATABASE` → use as `DB_DATABASE` (default: `railway`)

### Option B: Use Service Reference
If your MySQL service name is exactly `MySQL`, use:
```env
DB_HOST=${{MySQL.RAILWAY_PRIVATE_DOMAIN}}
DB_PASSWORD=${{MySQL.MYSQL_ROOT_PASSWORD}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
```

## AFTER UPDATING VARIABLES

1. Save the variables in Railway dashboard
2. Railway will **automatically redeploy**
3. Check deployment logs
4. Service should start successfully!

## WHY THIS FIXES THE ISSUE

**Problem:** Your current env uses:
```env
DB_HOST="${{MySQL.MYSQLHOST}}"  # ❌ Circular reference
```

**Solution:** Direct reference or hardcoded values:
```env
DB_HOST=${{MySQL.RAILWAY_PRIVATE_DOMAIN}}  # ✅ Direct reference
# OR
DB_HOST=mysql.railway.internal  # ✅ Hardcoded
```

## VERIFICATION

After deployment, check logs for:
```
✅ Database connected successfully!
✅ Running database migrations...
✅ Starting Laravel server on port 8000...
```

If you see this, the fix worked!
