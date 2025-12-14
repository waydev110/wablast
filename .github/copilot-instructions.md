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

2. **Node.js HTTP Server** (`server.js`)
   - WhatsApp connection management via Baileys (`@whiskeysockets/baileys` from `github:TTMTT/Baileys-new`)
   - **HTTP Polling Mode** (no WebSocket) for shared hosting compatibility
   - QR codes and connection status stored in database and polled via HTTP
   - Device session persistence in `credentials/` directory
   - Runs on `PORT_NODE` (default: 3100)
   - **Critical**: Both servers must run simultaneously for message sending

### Inter-Process Communication
- **Laravel → Node.js**: HTTP POST requests to `WA_URL_SERVER:PORT_NODE` endpoints (e.g., `/backend-send-text`)
  - Implemented via `WhatsappService` interface → `WhatsappServiceImpl` (IonCube encoded)
  - Uses Laravel's `Http::withOptions(['verify' => false])->asForm()->post()`
- **Node.js → Laravel**: Webhook callbacks to device-specific URLs (stored in `devices.webhook` column)
- **Shared MySQL database**: Node uses `mysql2` pool in `server/database/index.js` that mirrors Laravel's `.env` DB config
- **QR Code/Connection Polling**: Frontend polls Laravel every 2 seconds via `/poll-connection/{device}`, Laravel reads from database (no WebSocket)

### Key Directories & Files
- `app/Services/WhatsappService.php` - Interface defining WhatsApp operations
- `app/Services/Impl/WhatsappServiceImpl.php` - **IonCube encoded** implementation (DO NOT EDIT)
- `app/Http/Controllers/Api/ApiController.php` - REST API with `$extendedDataNeeded` validation
- `app/Repositories/DeviceRepository.php` - Device data access layer
- `server/whatsapp.js` - Core WhatsApp connection logic (**heavily obfuscated**, DO NOT EDIT)
- `server/router/index.js` - Node.js Express routes (`/backend-*` endpoints)
- `server/controllers/` - Node.js request handlers for WhatsApp operations
- `credentials/{device_number}/` - Baileys auth sessions (git-ignored, cleared on logout)
- `database/migrations/` - Schema definitions for devices, campaigns, autoreplies, blasts, message_histories

## Development Workflows

### Starting the Application
**BOTH servers are required** - messages cannot be sent if Node.js server is not running:
```bash
# Terminal 1: PHP/Laravel (via XAMPP or artisan)
php artisan serve

# Terminal 2: Node.js Server (REQUIRED - reconnects all "Connected" devices on startup)
node server.js
```

On startup, `server.js` queries `devices` table for `status = 'Connected'` and auto-connects them via `wa.connectToWhatsApp()`.

### Common Artisan Commands
```bash
php artisan migrate              # Run migrations
php artisan optimize:clear       # Clear all caches (config, views, routes)
php artisan storage:link         # Link public storage
php artisan schedule:run         # Run scheduled tasks (blasts, subscriptions)
php artisan start:blast          # Start campaign/blast queue (processes campaigns via StartBlast.php)
```

Web-accessible alternatives in `routes/custom-route.php`:
- `/migrate` - Run migrations
- `/clear-cache` - Clear all caches
- `/blast-start` - Start blast queue
- `/schedule-run` - Run scheduler
- `/translatable-export/{lang}` - Export language files

### Testing API Endpoints
All API routes in `routes/api.php` require `api_key` and `sender` parameters:
```bash
POST /api/send-message
Body: {
  "sender": "628123456789",      # Device phone number (validates ownership)
  "number": "628987654321",      # Recipient number
  "message": "Hello",
  "api_key": "user_api_key"      # From users.api_key column
}
```

**API Authentication Flow** (`app/Http/Middleware/CheckApiKey.php`):
1. Validates `api_key` against `users` table
2. Validates `sender` device belongs to the user
3. Checks plan limits for non-admin users (if `ENABLE_INDEX=yes`)
4. Attaches `$request->user` and `$request->device` for controllers

## Critical Patterns

### Device Management
- **Device**: WhatsApp instance identified by phone number (stored in `devices` table), `qr_code`, `connection_data`, `qr_generated_at`, `pairing_code`
- Status flow: `Disconnect` → `Connected` (managed by `setStatus()` in `server/database/index.js`)
- Device sessions persist in `credentials/{number}/` - **cleared on logout**, git-ignored
- **QR Code Storage**: QR codes stored as base64 in `qr_code` column, connection data as JSON in `connection_data`
- Model: `app/Models/Device.php` with extended `$fillable` including polling columns
- Model: `app/Models/Device.php` with `$fillable = ['user_id', 'body', 'webhook', 'status', 'message_sent']`

### Message Sending Pattern
All message operations follow this flow:
1. **Laravel validates request** and checks device status
2. **Laravel sends HTTP request to Node.js** server (`WhatsappService` → Node endpoint like `/backend-send-text`)
3. **Node.js executes WhatsApp operation** via `sock[device]` socket (Baileys connection)
4. **Result logged** in `message_histories` table with status `success` or `failed`
5. **Webhook callback** to device's `webhook` URL (if configured)

Example from `ApiController.php`:
```php
protected $extendedDataNeeded = [
    'text' => ['message', 'number'],
    'media' => ['number', 'media_type', 'url'],
    'sticker' => ['number', 'url'],
    'button' => ['number', 'button', 'message'],
    'template' => ['number', 'template', 'message'],
    'list' => ['number', 'name', 'title', 'buttontext', 'message', 'sections'],
    'poll' => ['number', 'name', 'option', 'countable'],
    'location' => ['number', 'latitude', 'longitude'],
    'vcard' => ['number', 'name', 'phone'],
];
```

**Node.js Routing** (`server/router/index.js`):
- All Laravel endpoints prefixed with `/backend-*`
- Use `checkDestination` middleware to validate message destination
- Device socket management: `sock[deviceNumber]` array stores active Baileys connections
- QR codes: `qrcode[deviceNumber]` array for real-time Socket.IO updates

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
- **Controllers**: `CamelCaseController extends Controller` in `app/Http/Controllers/`
  - Base controller: `app/Http/Controllers/Controller.php` extends `BaseController`
  - API controllers: `app/Http/Controllers/Api/ApiController.php` (845 lines, handles all message types)
- **Models**: Eloquent with `$fillable` properties and relationships
  - Example: `Device` model has `user()`, `autoreplies()`, `campaigns()` relationships
- **Repositories**: Data access layer pattern (e.g., `DeviceRepository`)
- **Routes**: Localization-wrapped via `LaravelLocalization::setLocale()` in all route groups
- **Views**: Located in `resources/themes/{theme_name}/`, multi-theme support

### API Response Pattern
**Always use this consistent JSON structure:**
```php
return response()->json([
    'status' => true|false,                    // Boolean status
    'msg' => __("Translated message"),        // Localized message using __() helper
    'data' => $result                         // Optional result data
], $httpCode);                                // HTTP status code (200, 400, 500, etc.)
```

### Node.js Patterns
- **Promise-based**: All WhatsApp functions return promises
- **Device sockets**: `sock[deviceNumber]` array stores active Baileys connections
- **QR codes**: `qrcode[deviceNumber]` array for real-time Socket.IO updates
- **Database queries**: Use promisified `dbQuery()` from `server/database/index.js`
- **Status updates**: Call `setStatus(device, status)` to update MySQL device status
- **Number formatting**: Use `formatReceipt()` for international format before sending
- **Express routing**: All routes in `server/router/index.js` with `/backend-*` prefix

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
1. **Node server must run**: Laravel cannot send messages without it - messages will fail silently
2. **Obfuscated/encoded code**: 
   - `server/whatsapp.js` is **heavily obfuscated** - DO NOT EDIT
   - `app/Services/Impl/WhatsappServiceImpl.php` is **IonCube encoded** - DO NOT EDIT
3. **Number format**: Always use international format without `+` (e.g., `628123456789`)
4. **File uploads**: Uses `unisharp/laravel-filemanager` - files stored in `public/` directory
5. **Scheduled tasks**: Require cron job calling `/schedule-run` or `php artisan schedule:run`
6. **Campaign processing**: Use `php artisan start:blast` or `/blast-start` to process queued campaigns
7. **HTTP Polling Mode**: Uses polling every 2 seconds instead of WebSocket for shared hosting compatibility
9. **QR Code Storage**: QR codes stored in database (not real-time), may have 2-second delayeconnect
8. **Socket.IO events**: `StartConnection`, `ConnectViaCode`, `LogoutDevice` handled in `server.js`

## EExpress** (v4.18.3): Node.js web framework for REST endpoints (HTTP polling mode)
- **mysql2**: Node.js MySQL client (connection pool in `server/database/index.js`)
- **Sanctum**: Laravel API authentication (personal access tokens)
- **Intervention Image**: Image processing (avatars, media)
- **Maatwebsite Excel**: Contact import/export functionality
- **Laravel Localization** (`mcamara/laravel-localization`): Multi-language support
- **jQuery**: Frontend AJAX for HTTP polling (no Socket.IO client needed)

## Shared Hosting Compatibility
Application now uses **HTTP Polling** instead of WebSocket:
- QR codes stored in database (`devices.qr_code` column)
- Frontend polls `/poll-connection/{device}` every 2 seconds
- No WebSocket or Socket.IO dependencies for client-server communication
- Works on standard shared hosting without special port configuration
- See `MIGRATION_HTTP_POLLING.md` for complete migration guide
- **Maatwebsite Excel**: Contact import/export functionality
- **Laravel Localization** (`mcamara/laravel-localization`): Multi-language support
