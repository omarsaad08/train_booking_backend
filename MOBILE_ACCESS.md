# Accessing the API from Your Phone

## Your Computer's Local IP Address

To find your computer's IP address on the local network:

```powershell
ipconfig | Select-String -Pattern "IPv4"
```

Look for the IPv4 address that starts with `192.168.x.x` or `10.x.x.x` (not `127.0.0.1`).

## Configuration

The API is now configured to accept connections from any device on your local network.

**Port**: `8080`

## Accessing from Your Phone

Once you have your computer's IP address (e.g., `192.168.1.100`), you can access the API from your phone:

### Base URL

```
http://YOUR_COMPUTER_IP:8080
```

For example, if your IP is `192.168.1.100`:

```
http://192.168.1.100:8080
```

### Example Requests from Phone

#### View Documentation

Open in your phone's browser:

```
http://192.168.1.100:8080/
```

#### Signup (using a REST client app)

```
POST http://192.168.1.100:8080/signup
Content-Type: application/json

{
  "name": "Mobile User",
  "email": "mobile@example.com",
  "password": "password123"
}
```

#### Login

```
POST http://192.168.1.100:8080/login
Content-Type: application/json

{
  "email": "mobile@example.com",
  "password": "password123"
}
```

#### Get Schedules (with token)

```
GET http://192.168.1.100:8080/schedules
Authorization: Bearer YOUR_TOKEN_HERE
```

## Recommended Mobile Apps for Testing

### Android

- **HTTP Request Shortcuts** - Free, easy to use
- **Postman** - Full-featured API testing
- **REST API Client** - Simple and lightweight

### iOS

- **Postman** - Full-featured API testing
- **API Tester** - Simple REST client
- **Paw** - Advanced API tool

## Firewall Settings

If you can't connect from your phone, you may need to allow the port through Windows Firewall:

```powershell
# Run as Administrator
New-NetFirewallRule -DisplayName "Train Booking API" -Direction Inbound -LocalPort 8080 -Protocol TCP -Action Allow
```

## Troubleshooting

### Can't Connect from Phone?

1. **Check both devices are on the same WiFi network**
2. **Verify your computer's IP address** (it may change)
3. **Check Windows Firewall** - Allow port 8080
4. **Restart Docker containers** after making changes:
   ```powershell
   ./setup.ps1
   ```

### Test Connection

From your phone's browser, try accessing:

```
http://YOUR_COMPUTER_IP:8080/
```

You should see the API documentation page.

## Security Note

⚠️ This setup is for **local network testing only**. The API is accessible to anyone on your local network. Do not expose this to the internet without proper security measures (HTTPS, rate limiting, etc.).
