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
set "SHARE_NAME=WortlabOfficeAddins"
set "UNC_PATH=\\%COMPUTERNAME%\%SHARE_NAME%"
set "COPY_VALUE=%UNC_PATH%"

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

echo [2/4] Freigabe wird vorbereitet...
powershell -NoProfile -ExecutionPolicy Bypass -Command "try { if (-not (Get-SmbShare -Name '%SHARE_NAME%' -ErrorAction SilentlyContinue)) { New-SmbShare -Name '%SHARE_NAME%' -Path '%TARGET_DIR%' -ReadAccess '%USERNAME%' | Out-Null }; exit 0 } catch { Write-Host $_.Exception.Message; exit 1 }"
if errorlevel 1 (
    echo.
    echo WARNUNG: Die Freigabe konnte nicht automatisch erstellt werden.
    echo Bitte starten Sie das Script als Administrator oder geben Sie den Ordner manuell frei:
    echo   %TARGET_DIR%
    echo Danach in Word den UNC-Pfad eintragen:
    echo   %UNC_PATH%
    echo.
    echo Der UNC-Pfad wurde trotzdem in die Zwischenablage kopiert.
)

echo [3/4] Pfad in Zwischenablage kopieren...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Set-Clipboard -Value '%COPY_VALUE%'"

echo [4/4] Add-in-Ordner wird geoeffnet...
start "" explorer "%TARGET_DIR%"

echo.
echo Wichtig: In Word muss als Katalog-URL ein freigegebener Ordner verwendet werden.
echo Empfohlen ist der UNC-Pfad:
echo   %UNC_PATH%
echo.
echo Falls die Freigabe oben nicht automatisch erstellt werden konnte, den Ordner
echo bitte zuerst in Windows freigeben und danach den UNC-Pfad in Word eintragen.
echo.
echo Anleitung
echo.
echo Fast fertig. Fuehren Sie jetzt in Word einmalig aus:
echo.
echo   1. Word oeffnen
echo   2. Datei ^> Optionen ^> Trust Center ^> Einstellungen fuer das Trust Center
echo   3. Kataloge vertrauenswuerider Add-Ins
echo   4. UNC-Pfad bei Katalog-URL einfuegen (ist bereits in Zwischenablage) ^> Katalog hinzufuegen
echo   5. Word schliessen und wieder oeffnen
echo   6. In Word: Start ^> Add-Ins ^> Erweitert
echo.
echo Hinweis: Falls Word bereits offen war, nach dem Eintragen neu starten.
echo.
pause
