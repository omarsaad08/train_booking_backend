# 📱 Quick Mobile Access Guide

## Your Computer's IP Addresses

Based on your network configuration:

- **Primary IP**: `192.168.1.218` (Use this one for WiFi devices)
- **Secondary IP**: `172.29.16.11` (Docker/WSL network)

## Access the API from Your Phone

### 🌐 Base URL

```
http://192.168.1.218:8080
```

### 📖 View Documentation

Open in your phone's browser:

```
http://192.168.1.218:8080/
```

### 🔐 Quick Test Endpoints

#### Signup

```
POST http://192.168.1.218:8080/signup
Content-Type: application/json

{
  "name": "Phone User",
  "email": "phone@test.com",
  "password": "test123"
}
```

#### Login

```
POST http://192.168.1.218:8080/login
Content-Type: application/json

{
  "email": "phone@test.com",
  "password": "test123"
}
```

#### Get Schedules (requires token)

```
GET http://192.168.1.218:8080/schedules
Authorization: Bearer YOUR_TOKEN_HERE
```

## ⚙️ Setup Steps

1. **Restart Docker containers** to apply changes:

   ```powershell
   ./setup.ps1
   ```

2. **Allow through firewall** (if needed):

   ```powershell
   # Run as Administrator
   New-NetFirewallRule -DisplayName "Train Booking API" -Direction Inbound -LocalPort 8080 -Protocol TCP -Action Allow
   ```

3. **Make sure your phone is on the same WiFi network** as your computer

4. **Test from phone browser**: Open `http://192.168.1.218:8080/`

## 📱 Recommended Apps

- **Android**: HTTP Request Shortcuts, Postman
- **iOS**: Postman, API Tester

## ⚠️ Important Notes

- ✅ Works on local network only
- ✅ Both devices must be on same WiFi
- ⚠️ IP address may change if you reconnect to WiFi
- ⚠️ Not secure for internet exposure (local testing only)

## 🔧 Troubleshooting

Can't connect? Check:

1. ✓ Same WiFi network
2. ✓ Docker containers running (`docker ps`)
3. ✓ Windows Firewall allows port 8080
4. ✓ Try the IP in your phone's browser first
