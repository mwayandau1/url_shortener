# URL Shortener Service - DOCUMENTATION

## Overview

ShortLink is a URL shortening service built with raw PHP and MySQL. It converts long URLs like `https://sommalife.com/impact/` into short URLs like `http://shrt.est/ZeAK`.

## Technical Stack

- **Language**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Framework**: None (Raw PHP)
- **Web Server**: Apache with mod_rewrite

## Installation Instructions

### 1. Prerequisites
- XAMPP or similar web server with PHP and MySQL
- PDO MySQL extension enabled

### 2. Setup Project
```bash
# Clone or download project to web server directory
# For XAMPP: C:\xampp\htdocs\url-shortener
```

### 3. Database Setup
```sql
# Import the database schema
mysql -u root -p < database/schema.sql
```

### 4. Configuration
```bash
# Copy environment configuration
copy .env.example .env

# Edit .env file with your database credentials:
DB_HOST=localhost
DB_NAME=url_shortener
DB_USER=root
DB_PASS=your_password
```

### 5. Web Server Configuration
- Ensure Apache mod_rewrite is enabled
- Point document root to project directory
- The `.htaccess` file handles URL routing automatically

## Running the Application

### Access URLs
- **API Base**: Auto-detected based on your server setup
- **Encode Endpoint**: `http://your-server/path-to-project/public/encode`
- **Decode Endpoint**: `http://your-server/path-to-project/public/decode`

### Testing
Run the test suite by visiting:
```
http://your-server/path-to-project/public/test.php
```

## API Endpoints

### POST /encode
Encodes a URL to a shortened URL.

**Request:**
```json
{
    "url": "https://sommalife.com/impact/"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "short_url": "http://localhost/url-shortener/public/V3AG",
    "original_url": "https://sommalife.com/impact/"
  }
}
```

### POST /decode
Decodes a shortened URL to its original URL.

**Request:**
```json
{
  "original_url": "https://sommalife.com/impact/"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "original_url": "https://sommalife.com/impact/",
    "short_url": "http://localhost/url-shortener/public/V3AG"
  }
}
```

## Architecture & Object-Oriented Design

### Core Classes

1. **URLShortener** (`src/URLShortener.php`)
   - Custom hashing class for encoding/decoding
   - Implements Base62 algorithm
   - Handles ID to short string conversion

2. **Database** (`src/Database.php`)
   - Custom MySQL database access class
   - PDO wrapper with prepared statements
   - No third-party libraries used

3. **URLService** (`src/URLService.php`)
   - Business logic layer
   - Orchestrates encoding/decoding operations
   - Handles validation and duplicate detection

### Project Structure
```
url-shortener/
├── config/
│   └── database.php          # Database configuration
├── database/
│   └── schema.sql           # MySQL schema
├── public/
│   ├── .htaccess           # URL rewriting
│   ├── index.php           # API endpoints
│   └── test.php            # Test runner
├── src/
│   ├── Database.php        # Custom DB class
│   ├── URLService.php      # Business logic
│   └── URLShortener.php    # Hashing algorithm
├── tests/
│   ├── run_tests.php       # Test suite
│   ├── URLServiceTest.php  # Integration tests
│   └── URLShortenerTest.php # Unit tests
├── .env.example            # Environment template
└── DOCUMENTATION.md        # This file
```

## Algorithm Implementation

The hashing algorithm uses Base62 encoding:
- Character set: `0-9a-zA-Z` (62 characters)
- Converts numeric database IDs to short alphanumeric strings
- Ensures bidirectional encoding/decoding
- Collision-free through unique database IDs

## Error Handling

All endpoints return JSON responses with appropriate HTTP status codes:

- **400 Bad Request**: Invalid input parameters
- **404 Not Found**: Short URL not found
- **405 Method Not Allowed**: Wrong HTTP method
- **500 Internal Server Error**: Database or server errors

**Error Response Format:**
```json
{
    "success": false,
    "error": "Error message description"
}
```

## Testing

The application includes comprehensive tests:
- **Unit Tests**: URLShortener algorithm testing
- **Integration Tests**: Full workflow with database
- **Error Handling**: Invalid inputs and edge cases

Run tests via web interface for best compatibility with XAMPP environments.

## Server Compatibility

The system automatically detects and works with:

- **XAMPP**: `http://localhost/project-folder/public/ABC4`
- **WAMP**: `http://localhost/project-folder/public/ABC4`
- **LAMP**: `http://server/project-folder/public/ABC4`
- **Document Root**: `http://domain.com/ABC4`
- **Subdirectories**: `http://domain.com/any/path/ABC4`
- **Custom Domains**: `https://short.ly/ABC4`

**Auto-Detection Features:**
- Detects HTTP/HTTPS automatically
- Handles any directory structure
- Works with virtual hosts
- No manual configuration required

## Production Considerations

- Input validation and sanitization
- SQL injection protection via prepared statements
- CORS headers for cross-origin requests
- Proper error handling and logging
- Database indexing for performance
- Access tracking and analytics
- Automatic server configuration detection