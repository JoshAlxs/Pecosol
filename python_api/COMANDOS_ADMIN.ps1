# Instrucciones para ejecutar como Administrador en PowerShell
# 1. Abre PowerShell como Administrador (busca "PowerShell" en el menú inicio, clic derecho, "Ejecutar como administrador")
# 2. Copia y pega estos comandos uno por uno:

# Comando 1: Desactivar PUA Protection
Set-MpPreference -PUAProtection Disabled

# Comando 2: Agregar exclusión para Python
Add-MpPreference -ExclusionPath "C:\Users\ALEXIS\AppData\Local\Programs\Python\Python311"

# Comando 3: Agregar exclusión para el proyecto
Add-MpPreference -ExclusionPath "C:\xampp\htdocs\bodeshop\python_api"

# Comando 4: Desactivar análisis en tiempo real TEMPORALMENTE (10 minutos)
Set-MpPreference -DisableRealtimeMonitoring $true

# Comando 5: Verificar configuración
Get-MpPreference | Select-Object DisableRealtimeMonitoring, PUAProtection

Write-Host ""
Write-Host "✅ Configuración completada. Vuelve a VS Code y prueba el servidor." -ForegroundColor Green
Write-Host ""
Write-Host "IMPORTANTE: Para reactivar la protección después, ejecuta:" -ForegroundColor Yellow
Write-Host "Set-MpPreference -DisableRealtimeMonitoring `$false" -ForegroundColor Cyan
