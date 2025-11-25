# Test the Train Booking API
Write-Host "Testing Train Booking API..." -ForegroundColor Green

# 1. Test Signup
Write-Host "`n1. Testing Signup..." -ForegroundColor Yellow
$signupBody = @{
    name     = "Test User"
    email    = "test@example.com"
    password = "test123"
} | ConvertTo-Json

try {
    $signupResponse = Invoke-RestMethod -Uri "http://localhost:8080/signup" -Method POST -Body $signupBody -ContentType "application/json"
    $token = $signupResponse.token
    Write-Host "✓ Signup successful!" -ForegroundColor Green
    Write-Host "Token: $token" -ForegroundColor Cyan
}
catch {
    Write-Host "✗ Signup failed: $_" -ForegroundColor Red
    exit
}

# 2. Test Login
Write-Host "`n2. Testing Login..." -ForegroundColor Yellow
$loginBody = @{
    email    = "test@example.com"
    password = "test123"
} | ConvertTo-Json

try {
    $loginResponse = Invoke-RestMethod -Uri "http://localhost:8080/login" -Method POST -Body $loginBody -ContentType "application/json"
    $token = $loginResponse.token
    Write-Host "✓ Login successful!" -ForegroundColor Green
    Write-Host "New Token: $token" -ForegroundColor Cyan
}
catch {
    Write-Host "✗ Login failed: $_" -ForegroundColor Red
}

# 3. Test Creating a Schedule (Protected)
Write-Host "`n3. Testing Create Schedule (Protected)..." -ForegroundColor Yellow
$scheduleBody = @{
    from_city          = "Cairo"
    to_city            = "Alexandria"
    departure_datetime = "2023-12-01 08:00:00"
    arrival_datetime   = "2023-12-01 12:00:00"
} | ConvertTo-Json

$headers = @{ Authorization = "Bearer $token" }

try {
    $scheduleResponse = Invoke-RestMethod -Uri "http://localhost:8080/schedules" -Method POST -Body $scheduleBody -Headers $headers -ContentType "application/json"
    Write-Host "✓ Schedule created!" -ForegroundColor Green
    Write-Host $scheduleResponse.message -ForegroundColor Cyan
}
catch {
    Write-Host "✗ Create schedule failed: $_" -ForegroundColor Red
}

# 4. Test Getting All Schedules (Protected)
Write-Host "`n4. Testing Get All Schedules (Protected)..." -ForegroundColor Yellow
try {
    $schedules = Invoke-RestMethod -Uri "http://localhost:8080/schedules" -Method GET -Headers $headers
    Write-Host "✓ Schedules retrieved!" -ForegroundColor Green
    Write-Host ($schedules | ConvertTo-Json -Depth 3) -ForegroundColor Cyan
}
catch {
    Write-Host "✗ Get schedules failed: $_" -ForegroundColor Red
}

# 5. Test Without Token (Should Fail)
Write-Host "`n5. Testing Without Token (Should Fail)..." -ForegroundColor Yellow
try {
    $schedules = Invoke-RestMethod -Uri "http://localhost:8080/schedules" -Method GET
    Write-Host "✗ This should have failed!" -ForegroundColor Red
}
catch {
    Write-Host "✓ Correctly rejected unauthorized request!" -ForegroundColor Green
}

Write-Host "`n✓ All tests completed!" -ForegroundColor Green
