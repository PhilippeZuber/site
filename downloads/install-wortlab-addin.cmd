@echo off
setlocal EnableExtensions

title Wortlab Add-in Installer (Windows)

echo ==============================================
echo   Wortlab fuer Word - Schnellinstallation
echo ==============================================
echo.

set "MANIFEST_URL=https://www.wortlab.ch/downloads/wortlab-addin-manifest.xml"
set "TARGET_DIR=%USERPROFILE%\Documents\Wortlab\OfficeAddins"
set "TARGET_FILE=%TARGET_DIR%\wortlab-addin-manifest.xml"

if not exist "%TARGET_DIR%" (
    mkdir "%TARGET_DIR%"
)

echo [1/4] Manifest wird heruntergeladen...
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ProgressPreference='SilentlyContinue'; try { Invoke-WebRequest -UseBasicParsing -Uri '%MANIFEST_URL%' -OutFile '%TARGET_FILE%'; exit 0 } catch { Write-Host $_.Exception.Message; exit 1 }"
if errorlevel 1 (
    echo.
    echo FEHLER: Download fehlgeschlagen.
    echo Bitte pruefen Sie die Internetverbindung und versuchen Sie es erneut.
    echo.
    pause
    exit /b 1
)

echo [2/4] Pfad in Zwischenablage kopieren...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Set-Clipboard -Value '%TARGET_DIR%'"

echo [3/4] Add-in-Ordner wird geoeffnet...
start "" explorer "%TARGET_DIR%"

echo [4/4] Anleitung
echo.
echo Fast fertig. Fuehren Sie jetzt in Word einmalig aus:
echo.
echo   1. Word oeffnen
echo   2. Datei ^> Optionen ^> Trust Center ^> Einstellungen fuer das Trust Center
echo   3. Kataloge vertrauenswuerider Add-Ins
echo   4. Ordnerpfad bei Katalog-URL einfuegen (ist bereits in Zwischenablage) ^> Katalog hinzufuegen
echo   5. Word schliessen und wieder oeffnen
echo   6. In Word: Start ^> Add-Ins ^> Erweitert
echo.
echo Hinweis: Falls Word bereits offen war, nach dem Eintragen neu starten.
echo.
pause
