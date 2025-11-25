# Aquarium Monitoring System 🐠

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Chart.js](https://img.shields.io/badge/Chart.js-FF6384?style=for-the-badge&logo=chart.js&logoColor=white)

Sistem monitoring kualitas air akuarium berbasis web yang memungkinkan pengguna untuk memantau parameter kualitas air secara real-time dan melihat data historis dengan antarmuka yang modern dan responsif.

## ✨ Features

### 🔐 Authentication System
- **Secure Login** - Session-based authentication
- **Password Protection** - MD5 hashing for security
- **Access Control** - Protected routes and session management

### 📊 Real-time Dashboard
- **Live Monitoring** - Auto-refresh every 5 seconds
- **Interactive Charts** - Beautiful charts using Chart.js
- **Water Quality Status** - Visual indicators with color coding
- **Sensor Metrics** - TDS, Temperature, and Water Quality scores

### 📈 Historical Data
- **Data Table** - Complete sensor reading history
- **Date Filtering** - Filter data by date range
- **Smart Pagination** - Efficient data navigation
- **CSV Export** - Export functionality for data analysis

### 🎨 Modern UI/UX
- **Responsive Design** - Works perfectly on desktop and mobile
- **Professional Design** - Clean and modern interface
- **Intuitive Navigation** - Easy-to-use sidebar navigation
- **Consistent Theme** - Beautiful blue color scheme

## 🚀 Quick Start

### Prerequisites
- Web server (Apache/Nginx)
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Modern web browser

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/SafrullahHidayat/Monitoring-web.git
   cd Monitoring-web
   ```

2. **Database Setup**
   ```sql
   CREATE DATABASE aquarium_monitor;
   USE aquarium_monitor;
   
   -- Users table for authentication
   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(50) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   
   -- Sensor data table
   CREATE TABLE data_sensor (
       id INT AUTO_INCREMENT PRIMARY KEY,
       timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
       tds FLOAT NOT NULL,
       suhu FLOAT NOT NULL,
       kualitas FLOAT NOT NULL
   );
   ```

3. **Configuration**
   Edit `koneksi.php` with your database credentials:
   ```php
   <?php
   $servername = "localhost";
   $username = "your_db_username";
   $password = "your_db_password";
   $dbname = "aquarium_monitor";
   
   $conn = new mysqli($servername, $username, $password, $dbname);
   ?>
   ```

4. **Create Admin User**
   ```sql
   INSERT INTO users (username, password) VALUES ('admin', MD5('your_password'));
   ```

5. **Access the Application**
   - Upload files to your web server
   - Navigate to `http://yourdomain.com`
   - Login with your credentials

## 📁 Project Structure

```
Monitoring-web/
│
├── 📄 index.php              # Login page
├── 📄 dashboard.php          # Main dashboard
├── 📄 data_historis.php      # Historical data page
├── 📄 logout.php             # Logout handler
├── 📄 koneksi.php            # Database configuration
│
├── 🔧 get-latest.php         # API for latest sensor data
├── 🔧 get-history.php        # API for historical data
├── 🔧 post-data.php          # API to post new sensor data
│
└── 📁 assets/                # Static assets (if any)
```

## 🎯 Usage Guide

### Login
1. Open the application in your browser
2. Enter username and password
3. You'll be redirected to the dashboard upon successful login

### Dashboard Features
- **Real-time Data**: Live sensor readings with auto-refresh
- **Quality Indicators**: Color-coded water quality status
- **Trend Analysis**: Interactive charts showing data trends
- **Auto Updates**: Data refreshes every 5 seconds automatically

### Historical Data Management
- **Date Range Filter**: Filter data by specific date ranges
- **Data Export**: Download data as CSV for external analysis
- **Pagination**: Navigate through large datasets efficiently
- **Sorting**: Data sorted by timestamp (newest first)

## 🔌 API Endpoints

### Get Latest Sensor Data
**Endpoint:** `get-latest.php`  
**Method:** GET  
**Response:**
```json
{
  "timestamp": "2024-01-01 12:00:00",
  "tds": 350.5,
  "suhu": 26.8,
  "kualitas": 85.2
}
```

### Get Historical Data
**Endpoint:** `get-history.php`  
**Method:** GET  
**Parameters:** `?limit=10` (optional)  
**Response:**
```json
[
  {
    "timestamp": "2024-01-01 12:00:00",
    "tds": 350.5,
    "suhu": 26.8,
    "kualitas": 85.2
  }
]
```

### Post Sensor Data
**Endpoint:** `post-data.php`  
**Method:** POST  
**Parameters:** `tds`, `suhu`, `kualitas`  
**Response:** Success/Error message

## 🛠️ Customization

### Theme Colors
Modify CSS variables in the style sections:
```css
:root {
    --primary-color: #2c7be5;      /* Main blue color */
    --success-color: #00d97e;      /* Success green */
    --warning-color: #f6c343;      /* Warning yellow */
    --danger-color: #e63757;       /* Danger red */
}
```

### Refresh Intervals
Adjust update frequencies in JavaScript:
```javascript
// In dashboard.php
setInterval(updateSensorCards, 5000);     // Update cards every 5s
setInterval(updateChartData, 15000);      // Update chart every 15s
```

### Quality Thresholds
Modify water quality standards in PHP code:
```php
if ($kualitas >= 80) {
    $status = 'SANGAT BAIK';
} elseif ($kualitas >= 60) {
    $status = 'BAIK';
}
// ... etc
```

## 🔒 Security Features

- ✅ Prepared Statements (SQL Injection protection)
- ✅ Input Validation & Sanitization
- ✅ Session Management & Protection
- ✅ Password Hashing (MD5)
- ✅ XSS Prevention
- ✅ Access Control

## 🌐 Browser Support

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

## 🤝 Contributing

We welcome contributions! Please feel free to submit pull requests, report bugs, or suggest new features.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

If you need help or have questions:

- 📧 Email: [Your Email]
- 🐛 Issues: [GitHub Issues](https://github.com/SafrullahHidayat/Monitoring-web/issues)
- 💬 Discussions: [GitHub Discussions]

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🏆 Acknowledgments

- Bootstrap for the responsive framework
- Chart.js for beautiful data visualizations
- Font Awesome for the icon set
- All contributors who help improve this project

---

**⭐ Don't forget to star this repository if you find it useful!**

---

*Built with ❤️ by [Safrullah Hidayat](https://github.com/SafrullahHidayat)*