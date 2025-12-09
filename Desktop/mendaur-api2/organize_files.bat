@echo off
REM ============================================================================
REM Documentation File Organization Batch Script
REM ============================================================================

setlocal enabledelayedexpansion

set "ROOT=c:\Users\Adib\OneDrive\Desktop\mendaur-api"
set "DOC=%ROOT%\DOCUMENTATION"
set "LOG=%DOC%\ORGANIZATION.log"

echo ============================================================================ > "%LOG%"
echo File Organization Log - %date% %time% >> "%LOG%"
echo ============================================================================ >> "%LOG%"

echo.
echo === STARTING FILE ORGANIZATION ===
echo Root: %ROOT%
echo Destination: %DOC%
echo.

REM Create FEATURE_IMPLEMENTATION subcategories
for %%D in (POINT_SYSTEM BADGES REDEMPTION AUTH_USER CASH CATEGORIES RBAC) do (
    if not exist "%DOC%\FEATURE_IMPLEMENTATION\%%D" (
        mkdir "%DOC%\FEATURE_IMPLEMENTATION\%%D"
        echo Created folder: FEATURE_IMPLEMENTATION\%%D
    )
)

REM Create main categories
for %%D in (QUICK_REFERENCE API_DOCUMENTATION FRONTEND_INTEGRATION FIXES_&_DEBUGGING VERIFICATION_&_TESTING PHASE_REPORTS MIGRATION_&_DEPLOYMENT QUERY_SCOPES SYSTEMS_&_FEATURES SPECIAL_TOPICS START_HERE_&_README DOCUMENTATION_ORGANIZATION) do (
    if not exist "%DOC%\%%D" (
        mkdir "%DOC%\%%D"
        echo Created folder: %%D
    )
)

REM Move point system files
echo.
echo [POINT SYSTEM]
for %%F in (*POINT_SYSTEM*.md *QUICK_START_POINT*.md *QUICK_TEST_POINTS*.md *POINTS_*.md *TABUNG_SAMPAH*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\FEATURE_IMPLEMENTATION\POINT_SYSTEM\" >nul 2>&1
        if errorlevel 0 (
            echo   Moved: %%F
            echo MOVED: %%F >> "%LOG%"
        )
    )
)

REM Move badge files
echo.
echo [BADGES ^& GAMIFICATION]
for %%F in (*BADGE*.md *GAMIFICATION*.md *LEADERBOARD*.md *NASABAH_BADGE*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\FEATURE_IMPLEMENTATION\BADGES\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move redemption files
echo.
echo [REDEMPTION]
for %%F in (*PENUKARAN_*.md *REDEMPTION*.md *EXCHANGE_*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\FEATURE_IMPLEMENTATION\REDEMPTION\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move auth/user files
echo.
echo [AUTHENTICATION ^& USER]
for %%F in (*REGISTER_*.md *USER_*.md *FIX_USER*.md *00_START_HERE_USER*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\FEATURE_IMPLEMENTATION\AUTH_USER\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move cash withdrawal files
echo.
echo [CASH WITHDRAWAL]
for %%F in (*CASH_WITHDRAWAL*.md *BACKEND_CASH*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\FEATURE_IMPLEMENTATION\CASH\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move category files
echo.
echo [CATEGORIES]
for %%F in (*JENIS_SAMPAH*.md *KATEGORI_SAMPAH*.md *ANALISIS_TABEL_SIAP_DROP*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\FEATURE_IMPLEMENTATION\CATEGORIES\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move RBAC files
echo.
echo [RBAC]
for %%F in (*ROLE_BASED*.md *RBAC_*.md *DUAL_NASABAH_RBAC*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\FEATURE_IMPLEMENTATION\RBAC\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move API docs
echo.
echo [API DOCUMENTATION]
for %%F in (*API_*.md *PROFILE_API*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\API_DOCUMENTATION\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move frontend integration
echo.
echo [FRONTEND INTEGRATION]
for %%F in (*FRONTEND_*.md *ARTIKEL_*.md *SIMPLE_FRONTEND*.md *EMAIL_TEMPLATE_FRONTEND*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\FRONTEND_INTEGRATION\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move fixes and debugging
echo.
echo [FIXES ^& DEBUGGING]
for %%F in (*FIX_*.md *DEBUG_*.md *ROUTE_LOGIN_ERROR*.md *CONTROLLER_FIXES*.md *POSTMAN*.md *CORRECTION*.md *REDEMPTION_BUGS*.md *START_HERE_DEBUGGING*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\FIXES_&_DEBUGGING\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move verification and testing
echo.
echo [VERIFICATION ^& TESTING]
for %%F in (*VERIFICATION*.md *MANUAL_API_TESTING*.md *INTEGRATION_TEST*.md *BACKEND_CHECKLIST*.md *NASABAH_UCD*.md *TEST_*.md *TESTING_*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\VERIFICATION_&_TESTING\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move phase reports
echo.
echo [PHASE REPORTS]
for %%F in (*PHASE_*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\PHASE_REPORTS\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move migration and deployment
echo.
echo [MIGRATION ^& DEPLOYMENT]
for %%F in (*MIGRATION_*.md *DEPLOYMENT_*.md *START_DEPLOYMENT*.md *SCHEMA_OPTIMIZATION*.md *PRIMARY_KEY*.md *COLUMN_STANDARDIZATION*.md *COLUMN_NAMING*.md *00_START_HERE_MIGRATION*.md *README_MIGRATION*.md *RENAME_MIGRATIONS*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\MIGRATION_&_DEPLOYMENT\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move quick reference
echo.
echo [QUICK REFERENCE]
for %%F in (*QUICK_*.md *CHEAT_SHEET*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\QUICK_REFERENCE\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move query scopes
echo.
echo [QUERY SCOPES]
for %%F in (*QUERY_SCOPES*.md *RELATIONSHIPS_UPDATE*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\QUERY_SCOPES\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move systems and features
echo.
echo [SYSTEMS ^& FEATURES]
for %%F in (*SESSIONS_TABLE*.md *SERVICE_WORKER*.md *PWA_*.md *FITUR_SISTEM*.md *ACTIVITY_LOG*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\SYSTEMS_&_FEATURES\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move special topics
echo.
echo [SPECIAL TOPICS]
for %%F in (*TABEL_PERBANDINGAN*.md *ARTIKEL_SISTEM*.md *NEXT_STEPS*.md *READY_FOR_ERD*.md *UCD_*.md *WHY_FRONTEND*.md *VISUAL_SUMMARY*.md *TIMEZONE*.md *TOKEN_VS*.md *STEP_BY_STEP_DROP*.md *FEATURE_MATRIX*.md *DIAGRAM_*.md *ARCHITECTURE*.md *OPSI_*.md *CONTROLLER_*.md *CORS_FIX*.md *VERIFICATION_PENUKARAN*.md *VERIFICATION_REPORT*.md *VERIFIED_*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\SPECIAL_TOPICS\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move documentation organization
echo.
echo [DOCUMENTATION ORGANIZATION]
for %%F in (*MASTER_INDEX*.md *DOCUMENTATION_*.md *DOKUMENTASI_*.md *COMPLETE_FILE*.md *COMPLETE_DOCUMENTATION*.md *INDEX_DOKUMENTASI_MASTER*.md *DOCUMENTATION_PACKAGE*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\DOCUMENTATION_ORGANIZATION\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move admin dashboard files
echo.
echo [ADMIN DASHBOARD]
for %%F in (*ADMIN_DASHBOARD*.md *DASHBOARD_COMPLETE*.md *APPLICATION_LOGS*.md *00_ADMIN*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\ADMIN_DASHBOARD\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

REM Move start here and readme
echo.
echo [START HERE ^& README]
for %%F in (*START_HERE*.md *README*.md *00_START_HERE*.md *00_README*.md *00_DROP*.md) do (
    if exist "%ROOT%\%%F" (
        move "%ROOT%\%%F" "%DOC%\START_HERE_&_README\" >nul 2>&1
        echo   Moved: %%F
        echo MOVED: %%F >> "%LOG%"
    )
)

echo.
echo === ORGANIZATION COMPLETE ===
echo.
echo Remaining files in root (if any):
dir "%ROOT%\*.md" /b 2>nul

echo.
echo Log file created: %LOG%
echo.
pause
