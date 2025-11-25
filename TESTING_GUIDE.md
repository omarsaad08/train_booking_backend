# Train Booking API - Testing Guide

## Access Points

- **API Base URL**: `http://localhost:8080`
- **Documentation**: `http://localhost:8080/` (view in browser)
- **phpMyAdmin**: `http://localhost:8081/`

## Testing with PowerShell

Since you're on Windows, use these PowerShell commands to test the API:

### 1. Signup

```powershell
$body = @{
    name = "John Doe"
    email = "john@example.com"
    password = "password123"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8080/signup" -Method POST -Body $body -ContentType "application/json"
```

### 2. Login

```powershell
$body = @{
    email = "john@example.com"
    password = "password123"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8080/login" -Method POST -Body $body -ContentType "application/json"
$token = $response.token
Write-Host "Token: $token"
```

### 3. Create a Train Schedule

```powershell
$body = @{
    from_city = "Cairo"
    to_city = "Alexandria"
    departure_datetime = "2023-12-01 08:00:00"
    arrival_datetime = "2023-12-01 12:00:00"
} | ConvertTo-Json

$headers = @{
    Authorization = "Bearer $token"
}

Invoke-RestMethod -Uri "http://localhost:8080/schedules" -Method POST -Body $body -Headers $headers -ContentType "application/json"
```

### 4. Get All Schedules

```powershell
Invoke-RestMethod -Uri "http://localhost:8080/schedules" -Method GET -Headers $headers
```

### 5. Create a Booking

```powershell
$body = @{
    user_id = 1
    schedule_id = 1
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8080/bookings" -Method POST -Body $body -Headers $headers -ContentType "application/json"
```

## API Endpoints Summary

### Authentication (No Auth Required)

- `POST /signup` - Create account → Returns `{"token": "..."}`
- `POST /login` - Login → Returns `{"token": "..."}`

### Protected Endpoints (Require Authorization Header)

- `GET /users?id={id}` - Get user by ID
- `GET /users?email={email}` - Get user by email
- `GET /schedules` - Get all train schedules
- `POST /schedules` - Create train schedule (with from_city, to_city, departure_datetime, arrival_datetime)
- `PUT /schedules` - Update train schedule
- `DELETE /schedules` - Delete train schedule
- `GET /bookings` - Get all bookings
- `POST /bookings` - Create booking
- `PUT /bookings` - Update booking
- `DELETE /bookings` - Delete booking

## Complete Test Flow

```powershell
# 1. Signup
$signupBody = @{
    name = "Test User"
    email = "test@example.com"
    password = "test123"
} | ConvertTo-Json

$signupResponse = Invoke-RestMethod -Uri "http://localhost:8080/signup" -Method POST -Body $signupBody -ContentType "application/json"
$token = $signupResponse.token
Write-Host "Signup successful! Token: $token"

# 2. Create a train schedule
$scheduleBody = @{
    from_city = "Cairo"
    to_city = "Alexandria"
    departure_datetime = "2023-12-01 08:00:00"
    arrival_datetime = "2023-12-01 12:00:00"
} | ConvertTo-Json

$headers = @{ Authorization = "Bearer $token" }
$schedule = Invoke-RestMethod -Uri "http://localhost:8080/schedules" -Method POST -Body $scheduleBody -Headers $headers -ContentType "application/json"
Write-Host "Schedule created: $($schedule.message)"

# 3. Get all schedules
$schedules = Invoke-RestMethod -Uri "http://localhost:8080/schedules" -Method GET -Headers $headers
Write-Host "Schedules: $($schedules | ConvertTo-Json)"

# 4. Create a booking
$bookingBody = @{
    user_id = 1
    schedule_id = 1
} | ConvertTo-Json

$booking = Invoke-RestMethod -Uri "http://localhost:8080/bookings" -Method POST -Body $bookingBody -Headers $headers -ContentType "application/json"
Write-Host "Booking created: $($booking.message)"
```

## Troubleshooting

If you get errors:

1. Make sure Docker containers are running: `docker ps`
2. Check logs: `docker logs train_booking_backend-php-1`
3. Restart containers: `./setup.ps1`
