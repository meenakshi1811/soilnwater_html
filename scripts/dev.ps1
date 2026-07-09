#Requires -Version 5.1
<#
.SYNOPSIS
    Start all SoilNWater development services.

.DESCRIPTION
    Runs the Laravel dev server, queue worker, log tail, Vite, and Reverb websocket
    server concurrently. Press Ctrl+C to stop all processes.

.EXAMPLE
    .\scripts\dev.ps1
#>

$ErrorActionPreference = 'Stop'
$ProjectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $ProjectRoot

if (-not (Test-Path 'vendor')) {
    throw 'Dependencies not installed. Run .\scripts\setup-dev.ps1 first.'
}

if (-not (Test-Path 'node_modules')) {
    throw 'Node modules not installed. Run .\scripts\setup-dev.ps1 first.'
}

Write-Host 'Starting development services...' -ForegroundColor Cyan
Write-Host '  App:     http://127.0.0.1:8000' -ForegroundColor Gray
Write-Host '  Vite:    http://localhost:5173' -ForegroundColor Gray
Write-Host '  Reverb:  ws://localhost:8080' -ForegroundColor Gray
Write-Host '  Press Ctrl+C to stop all services.' -ForegroundColor Gray
Write-Host ''

composer dev
