# MPWA WhatsApp Gateway - AI Coding Agent Instructions

## Project Overview
MPWA is a WhatsApp Gateway application enabling multi-device, multi-instance WhatsApp API access. It's a **dual-stack architecture** combining Laravel (PHP) backend with a Node.js WebSocket server for real-time WhatsApp communication via Baileys library.

**Version**: 9.6.1  
**License**: CC BY-NC-ND 4.0 (non-commercial, no derivatives)  
**Author**: Magd Almuntaser, OneXGen Technology

## Architecture

### Dual Server Setup
1. **Laravel Backend** (PHP/Apache/XAMPP)
   - Web UI, authentication, REST API endpoints
   - Database operations (MySQL)
   - File management, user management, campaigns
   - Runs on Apache (port 80/443)

2. **Node.js WebSocket Server** (`server.js`)
   - WhatsApp connection management via Baileys
   - Socket.IO for real-time QR code/pairing updates
   - Device session persistence in `credentials/` directory
   - Runs on `PORT_NODE` (default: 3100)
   - **Critical**: Both servers must run simultaneously

### Inter-Process Communication
- Laravel → Node.js: HTTP requests to `WA_URL_SERVER` (e.g., `http://localhost:3100`)
- Node.js → Laravel: Webhook callbacks to device-specific URLs
- Shared MySQL database (Node uses `server/database/index.js`)

### Key Directories
- `app/Services/` - Laravel service layer for WhatsApp operations
- `app/Http/Controllers/Api/` - REST API endpoints with `checkApiKey` middleware
- `server/whatsapp.js` - Core WhatsApp connection logic (obfuscated)
- `credentials/{device_number}/` - Device authentication sessions (git-ignored)
- `database/migrations/` - Schema definitions for devices, campaigns, autoreplies

## Development Workflows

### Starting the Application
```bash
# Terminal 1: PHP/Laravel (via XAMPP or artisan)
php artisan serve

# Terminal 2: Node.js Server (REQUIRED)
node server.js
```

### Common Artisan Commands
```bash
php artisan migrate              # Run migrations
php artisan optimize:clear       # Clear all caches (config, views, routes)
php artisan storage:link         # Link public storage
php artisan schedule:run         # Run scheduled tasks (blasts, subscriptions)
php artisan start:blast          # Start campaign/blast queue
```

### Testing API Endpoints
API routes (`routes/api.php`) require `api_key` parameter for authentication. Example:
```bash
POST /api/send-message
Body: {
  "device": "628123456789",
  "number": "628987654321",
  "message": "Hello",
  "api_key": "user_api_key"
}
```

## Critical Patterns

### Device Management
- **Device**: WhatsApp instance identified by phone number (stored in `devices` table)
- Status flow: `Disconnect` → `Connected` (managed by `setStatus()` in Node.js)
- Always check device status before sending messages
- Device sessions persist in `credentials/{number}/` - cleared on logout

### Message Sending Pattern
All message operations follow this flow:
1. Laravel validates request and checks device status
2. Laravel sends HTTP request to Node.js server (`WhatsappService` → Node endpoint)
3. Node.js executes WhatsApp operation via `sock[device]` socket
4. Result logged in `message_histories` table
5. Webhook callback to device's `webhook` URL (if configured)

Example from `ApiController.php`:
```php
protected $extendedDataNeeded = [
    'text' => ['message', 'number'],
    'media' => ['number', 'media_type', 'url'],
    // ... validates required fields per message type
];
```

### Auto-Reply System
Located in `autoreplies` table, supports:
- Exact match (`type = 'Equal'`) and contains (`type = 'Contains'`)
- Reply types: text, media, button, template, list
- Triggers based on incoming messages (handled in Node.js `IncomingMessage()`)
- Can be enabled/disabled per device

### Helper Functions
`app/helpers.php` contains critical utilities:
```php
clearCacheNode($device)     // Clears Node.js cache for device
setEnv($key, $value)        // Updates .env variables
backWithFlash()             // Flash messages pattern
```

## Configuration Files

### Environment Variables
- `DB_*` - MySQL credentials (shared between PHP and Node)
- `WA_URL_SERVER` - Node.js server URL (e.g., `http://localhost`)
- `PORT_NODE` - Node.js server port (default: 3100)
- Payment gateways: `STRIPE_*`, `PAYPAL_*`, `MIDTRANS_*`

### Important Config Files
- `config/database.php` - Laravel database config
- `server/database/index.js` - Node.js MySQL pool (mirrors Laravel config)
- `.env` - Primary configuration (NEVER commit this file)

## Code Conventions

### Laravel Standards
- Controllers: `CamelCaseController` in `app/Http/Controllers/`
- Models: Eloquent with `$fillable` properties (see `app/Models/Device.php`)
- Routes: Localization-wrapped via `LaravelLocalization::setLocale()`
- Views: Located in `resources/themes/{theme_name}/`

### API Responses
Consistent JSON structure:
```php
return response()->json([
    'status' => true|false,
    'msg' => __("Translated message"),
    'data' => $result  // optional
], $httpCode);
```

### Node.js Patterns
- All WhatsApp functions return promises
- Device sockets stored in `sock[deviceNumber]` array
- QR codes stored in `qrcode[deviceNumber]` array
- Use `formatReceipt()` for number formatting before sending

## Payment Integration
Supports multiple gateways (Stripe, PayPal, Midtrans):
- Controllers: `app/Http/Controllers/Payments/`
- Order management: `orders` table with `payment_gateway` field
- Plan subscriptions: `plans` table with device limits

## Localization
- Uses `mcamara/laravel-localization` package
- Language files: `resources/lang/{locale}/`
- Export translations: `php artisan translatable:export {lang}`
- All user-facing strings wrapped in `__()` function

## Testing & Debugging
- Laravel logs: `storage/logs/laravel.log`
- Node.js logs: Console output (uses Pino logger)
- Enable debug mode: `APP_DEBUG=true` in `.env`
- Clear Node cache: Call `clearCacheNode($device)` after device updates

## Migration & Database
- Schema changes: Create migrations in `database/migrations/`
- Run via web: Visit `/migrate` route (defined in `custom-route.php`)
- Key tables: `devices`, `users`, `message_histories`, `autoreplies`, `campaigns`, `blasts`

## Security Notes
- API authentication via `checkApiKey` middleware
- 2FA support (Google2FA package)
- Webhook URLs stored per device - validate incoming webhook requests
- Device credentials in `credentials/` must be protected

## Common Gotchas
1. **Node server must run**: Laravel cannot send messages without it
2. **Obfuscated code**: `server/whatsapp.js` is heavily obfuscated - avoid editing
3. **Number format**: Always use international format without `+` (e.g., `628123456789`)
4. **File uploads**: Uses `unisharp/laravel-filemanager` - files in `public/` directory
5. **Scheduled tasks**: Require cron job calling `/schedule-run` or `php artisan schedule:run`

## External Dependencies
- **Baileys**: WhatsApp Web API library (GitHub fork in `package.json`)
- **Socket.IO**: Real-time communication for QR codes
- **Sanctum**: Laravel API authentication
- **Intervention Image**: Image processing (avatars, media)
- **Maatwebsite Excel**: Contact import/export
