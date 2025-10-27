# URL Shortener Service


## 🚀 Quick Start

1. **Setup Database**
   ```sql
   mysql -u root -p < database/schema.sql
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

3. **Enable PDO MySQL**
   - Edit `C:\xampp\php\php.ini`
   - Uncomment `extension=pdo_mysql`
   - Restart Apache

4. **Access Application**
   - API: `http://localhost/url-shortener/public/`
   - Tests: `http://localhost/url-shortener/public/test.php`

## 📡 API Usage

### Shorten URL
```bash
curl -X POST http://localhost/url-shortener/public/encode \
  -H "Content-Type: application/json" \
  -d '{"url":"https://example.com"}'
```

### Visit Short URL
```
http://localhost/url-shortener/public/ABC4
# Automatically redirects to original URL
```

### Decode URL
```bash
curl -X POST http://localhost/url-shortener/public/decode \
  -H "Content-Type: application/json" \
  -d '{"short_url":"http://localhost/url-shortener/public/ABC4"}'
```

## 🏗️ Architecture

- **Raw PHP** - No frameworks
- **Custom Database Class** - PDO wrapper
- **Base62 Encoding** - Random 4-character codes
- **Object-Oriented Design** - Clean separation of concerns

## 📁 Project Structure

```
url-shortener/
├── public/           # Web accessible files
├── src/             # Core classes
├── tests/           # Test suites
├── config/          # Configuration
└── database/        # SQL schema
```

## 🧪 Testing

Run comprehensive test suite:
```
http://localhost/url-shortener/public/test.php
```

## 📚 Documentation

See [DOCUMENTATION.md](DOCUMENTATION.md) for detailed technical documentation.

## ✨ Features

- ✅ URL encoding/decoding
- ✅ Automatic redirects
- ✅ Duplicate URL handling
- ✅ Access tracking
- ✅ Comprehensive tests
- ✅ Clean OOP design