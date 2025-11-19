Param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$ComposeArgs
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Write-Log {
    param([string]$Message)
    Write-Host "[docker-build] $Message"
}

$ProjectRoot = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$EnvFile = Join-Path $ProjectRoot '.env'
$TemplateFile = Join-Path $ProjectRoot '.env.example'

if (-not (Test-Path $EnvFile)) {
    if (-not (Test-Path $TemplateFile)) {
        throw "Missing .env and .env.example files"
    }
    Copy-Item $TemplateFile $EnvFile
    Write-Log 'Created .env from .env.example'
}

function Get-RandomPassword {
    param([int]$Length = 40)
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+'
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
    $bytes = New-Object byte[] 1
    $password = New-Object System.Text.StringBuilder
    for ($i = 0; $i -lt $Length; $i++) {
        $rng.GetBytes($bytes)
        $idx = $bytes[0] % $chars.Length
        [void]$password.Append($chars[$idx])
    }
    $rng.Dispose()
    return $password.ToString()
}

function Get-EnvValue {
    param([string]$Key)
    if (-not (Test-Path $EnvFile)) { return $null }
    $line = Select-String -Path $EnvFile -Pattern "^$Key=" | Select-Object -First 1
    if ($line) {
        return ($line.Line -split '=', 2)[1]
    }
    return $null
}

function Set-EnvValue {
    param([string]$Key, [string]$Value)
    $content = if (Test-Path $EnvFile) { Get-Content $EnvFile } else { @() }
    $updated = $false
    for ($i = 0; $i -lt $content.Length; $i++) {
        if ($content[$i] -like "$Key=*") {
            $content[$i] = "$Key=$Value"
            $updated = $true
            break
        }
    }
    if (-not $updated) {
        $content += "$Key=$Value"
    }
    if ($content.Length -eq 0) {
        $content = @("$Key=$Value")
    }
    Set-Content -Path $EnvFile -Value $content -Encoding UTF8
}

function Ensure-Password {
    param([string]$Key)
    $value = Get-EnvValue -Key $Key
    if ([string]::IsNullOrWhiteSpace($value) -or $value.Contains('CHANGE_THIS')) {
        $newValue = Get-RandomPassword
        Set-EnvValue -Key $Key -Value $newValue
        Write-Log "Generated secure value for $Key"
    }
}

Ensure-Password -Key 'DB_PASSWORD'
Ensure-Password -Key 'DB_ROOT_PASSWORD'

# Ensure .env is readable (Windows doesn't need chmod)

if (-not $ComposeArgs) {
    $ComposeArgs = @('-d')
}

Write-Log "Running docker compose up --build $($ComposeArgs -join ' ')"
Push-Location $ProjectRoot
try {
    docker compose up --build @ComposeArgs
}
finally {
    Pop-Location
}
