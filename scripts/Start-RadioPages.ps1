param([string]$ProjectName = 'panorama-radio')
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
Push-Location $projectRoot
try {
    $nodeCommand = Get-Command node -ErrorAction SilentlyContinue
    $nodeExe = if ($nodeCommand) { $nodeCommand.Source } else { Join-Path $env:USERPROFILE '.cache/codex-runtimes/codex-primary-runtime/dependencies/node/bin/node.exe' }
    $tunnelExe = Join-Path $projectRoot '.tools/cloudflared.exe'
    $wrangler = Join-Path $projectRoot 'node_modules/wrangler/bin/wrangler.js'
    if (!(Test-Path $nodeExe) -or !(Test-Path $tunnelExe) -or !(Test-Path $wrangler)) { throw 'Faltan Node, cloudflared o Wrangler. Consulta README.md.' }
    Invoke-WebRequest 'http://127.0.0.1/miradio/ajax/status.php' -UseBasicParsing -TimeoutSec 10 | Out-Null
    New-Item -ItemType Directory -Force .runtime | Out-Null
    try { Invoke-WebRequest 'http://127.0.0.1:8787/ajax/status.php' -UseBasicParsing -TimeoutSec 10 | Out-Null }
    catch {
        Start-Process -FilePath $nodeExe -ArgumentList 'scripts/studio-gateway.cjs' -WorkingDirectory $projectRoot -WindowStyle Hidden -RedirectStandardOutput (Join-Path $projectRoot '.runtime/gateway.log') -RedirectStandardError (Join-Path $projectRoot '.runtime/gateway-error.log')
    }
    $radioOrigin = ''
    $tunnelLog = Join-Path $projectRoot '.runtime/tunnel.log'
    if (Test-Path $tunnelLog) {
        $radioOrigin = [regex]::Match((Get-Content $tunnelLog -Raw), 'https://[a-z0-9-]+\.trycloudflare\.com').Value
        if ($radioOrigin) {
            try { Invoke-WebRequest ($radioOrigin + '/ajax/status.php') -UseBasicParsing -TimeoutSec 10 | Out-Null }
            catch { $radioOrigin = '' }
        }
    }
    if (!$radioOrigin) {
        Start-Process -FilePath $tunnelExe -ArgumentList 'tunnel --url http://127.0.0.1:8787 --no-autoupdate' -WorkingDirectory $projectRoot -WindowStyle Hidden -RedirectStandardOutput (Join-Path $projectRoot '.runtime/tunnel-out.log') -RedirectStandardError $tunnelLog
        for ($attempt = 0; $attempt -lt 30; $attempt++) {
            Start-Sleep -Seconds 1
            if (Test-Path $tunnelLog) { $radioOrigin = [regex]::Match((Get-Content $tunnelLog -Raw), 'https://[a-z0-9-]+\.trycloudflare\.com').Value }
            if ($radioOrigin) { break }
        }
        if (!$radioOrigin) { throw 'No se obtuvo el enlace del estudio. Revisa .runtime/tunnel.log.' }
    }
    & $nodeExe scripts/build-pages.cjs
    if ($LASTEXITCODE -ne 0) { throw 'Error al generar la web.' }
    $radioOrigin | & $nodeExe $wrangler pages secret put RADIO_ORIGIN --project-name $ProjectName
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo actualizar el origen. Inicia sesion con Wrangler.' }
    & $nodeExe $wrangler pages deploy dist --project-name $ProjectName --branch main --commit-dirty=true
    if ($LASTEXITCODE -ne 0) { throw 'Error al publicar Pages.' }
    Write-Host "PANORAMA: https://$ProjectName.pages.dev"
    Write-Host 'Mantén encendidos Apache, RadioDJ, Icecast y el emisor. La conexión del estudio se ejecuta en segundo plano.'
} finally { Pop-Location }
