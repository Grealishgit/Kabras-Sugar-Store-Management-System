# Kabras Sugar Store Management System

A comprehensive web-based management system for sugar stores, built with PHP and MySQL. This system provides complete inventory management, sales tracking, financial reporting, and audit compliance features for efficient store operations.

## 🌟 Features

### 🏪 Core Functionality
- **Multi-role User Management**: Admin, Manager, Accountant, Cashier, and StoreKeeper roles
- **Product Inventory**: Complete product catalog with categories, stock tracking, and batch management
- **Sales Management**: Point-of-sale system with real-time sales tracking
- **Payment Processing**: Multiple payment methods (Cash, M-Pesa, Card, Bank transfer)
- **Supplier Management**: Vendor and purchase order tracking
- **Customer Management**: Customer database with purchase history

### 📊 Financial Management
- **Revenue Tracking**: Daily, weekly, and monthly revenue reports
- **Expense Management**: Purchase and operational expense tracking
- **Profit/Loss Analysis**: Real-time financial performance metrics
- **CSV Export**: Export financial data for external analysis

### 🔍 Audit & Compliance
- **Audit Reports**: Comprehensive audit trail system
- **Compliance Monitoring**: Regulatory compliance tracking and reporting
- **Violation Management**: Track and resolve compliance issues
- **Audit Statistics**: Dashboard analytics for audit performance

### 📈 Analytics & Reporting
- **Interactive Dashboards**: Role-based dashboards with key metrics
- **Chart.js Integration**: Visual data representation
- **Real-time Statistics**: Live updates on system performance
- **Export Capabilities**: CSV export for all major data sets

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Charts**: Chart.js for data visualization
- **Architecture**: MVC pattern with modular handlers
- **Security**: PDO prepared statements, session management

## � Docker Deployment

### Quick Start with Docker Compose

1. **Clone the repository**
```bash
git clone https://github.com/Grealishgit/Kabras-Sugar-Store-Management-System.git
cd Kabras-Sugar-Store-Management-System
```

2. **Start the application**
```bash
docker-compose up -d
```

3. **Access the application**
- **Main Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

### Docker Services

- **app**: PHP 8.1 with Apache web server
- **db**: MySQL 8.0 database
- **phpmyadmin**: Database management interface

### Environment Configuration

Copy the example environment file:
```bash
cp .env.example .env
```

Edit `.env` to configure your settings:
```env
DB_HOST=db
DB_DATABASE=kabras_store
DB_USERNAME=root
DB_PASSWORD=Hunter42.
```

### Docker Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f app

# Rebuild and restart
docker-compose up -d --build

# Access database
docker-compose exec db mysql -u root -p kabras_store
```

### Production Deployment

For production deployment:

1. **Update environment variables** in `docker-compose.yml`
2. **Use external database** for data persistence
3. **Configure SSL/TLS** certificates
4. **Set up reverse proxy** (nginx) for load balancing
5. **Enable logging** and monitoring

## �📋 Prerequisites (Manual Installation)

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional, for dependency management)

## 🚀 Manual Installation

### 1. Clone the Repository
```bash
git clone https://github.com/Grealishgit/Kabras-Sugar-Store-Management-System.git
cd Kabras-Sugar-Store-Management-System
```

### 2. Database Setup
1. Create a MySQL database named `kabras_store`
2. Import the database schema:
```bash
mysql -u root -p kabras_store < Queries/main.sql
```

### 3. Configure Database Connection
Update the database credentials in `config/database.php`:
```php
private $host = 'localhost';
private $db   = 'kabras_store';
private $user = 'your_username';
private $pass = 'your_password';
```

### 4. Web Server Configuration
- Point your web server document root to the project directory
- Ensure PHP has proper permissions for file operations
- Configure URL rewriting if using clean URLs

### 5. Initial Setup
1. Access the application through your web browser
2. Create an admin user account
3. Configure system settings as needed

## 👥 User Roles & Permissions

### 👑 Admin
- Full system access
- User management
- Audit and compliance oversight
- System configuration
- Backup and restore operations

### 👔 Manager
- Sales and inventory oversight
- Financial reporting access
- Team performance monitoring
- Audit statistics viewing

### 💼 Accountant
- Financial data management
- Expense tracking
- Revenue analysis
- Financial reporting
- Payment processing oversight

### 💰 Cashier
- Point-of-sale operations
- Sales processing
- Customer management
- Basic inventory viewing

### 📦 StoreKeeper
- Inventory management
- Stock level monitoring
- Product catalog maintenance
- Supplier coordination

## 📁 Project Structure

```
Kabras-Sugar-Store-Management-System/
├── admin/                 # Admin-specific pages
│   ├── dashboard.php     # Admin dashboard
│   ├── users.php         # User management
│   ├── audits.php        # Audit management
│   ├── compliance.php    # Compliance tracking
│   └── backup.php        # Database backup
├── accountant/           # Accountant dashboard and tools
├── manager/              # Manager dashboard and analytics
├── cashier/              # Cashier point-of-sale interface
├── storekeeper/          # Inventory management interface
├── handlers/             # Business logic handlers
│   ├── AuthHandler.php   # Authentication & authorization
│   ├── AuditHandler.php  # Audit operations
│   ├── FinanceHandler.php# Financial operations
│   └── ...               # Other handlers
├── config/               # Configuration files
│   └── database.php      # Database connection
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   └── js/              # JavaScript files
├── includes/            # Reusable components
├── modules/             # Feature modules
├── Queries/             # Database scripts
├── backups/             # Database backups
└── uploads/             # File uploads directory
```

## 🔧 Configuration

### Database Configuration
Edit `config/database.php` to match your database setup:
```php
class Database {
    private $host = 'localhost';
    private $db   = 'kabras_store';
    private $user = 'your_username';
    private $pass = 'your_password';
    private $charset = 'utf8mb4';
}
```

### File Permissions
Ensure proper permissions for upload directories:
```bash
chmod 755 uploads/
chmod 755 backups/
```

## 📊 Database Schema

The system uses the following main tables:
- `users` - User accounts and roles
- `products` - Product catalog and inventory
- `sales` - Sales transactions
- `sale_items` - Individual sale line items
- `payments` - Payment records
- `customers` - Customer information
- `suppliers` - Supplier/vendor data
- `purchases` - Purchase orders
- `expenses` - Operational expenses
- `audit_reports` - Audit trail
- `compliance_audits` - Compliance checks
- `compliance_violations` - Compliance issues

## 🔒 Security Features

- **Session Management**: Secure session handling with timeout
- **SQL Injection Protection**: PDO prepared statements
- **Role-Based Access Control**: Granular permissions per role
- **Input Validation**: Server-side validation for all forms
- **Password Security**: Secure password hashing
- **Audit Logging**: Complete audit trail for all operations

## 📈 Usage

### First Time Setup
1. Access the login page
2. Create an admin account
3. Add initial products and suppliers
4. Configure user roles and permissions

### Daily Operations
1. **Cashiers**: Process sales and manage customer transactions
2. **StoreKeepers**: Monitor inventory and manage stock levels
3. **Accountants**: Track expenses and generate financial reports
4. **Managers**: Monitor performance and oversee operations
5. **Admins**: Manage users and ensure system compliance

## 🧪 Testing

### Manual Testing Checklist
- [ ] User registration and login
- [ ] Role-based access control
- [ ] Product CRUD operations
- [ ] Sales processing
- [ ] Payment recording
- [ ] Financial reporting
- [ ] Audit trail functionality
- [ ] Data export features

### Automated Testing
```bash
# Run PHP unit tests (if implemented)
php vendor/bin/phpunit

# Database connection test
php config/database.php
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Use meaningful commit messages
- Test all changes thoroughly
- Update documentation as needed
- Maintain backward compatibility

## 📝 API Documentation

The system includes RESTful API endpoints in the `api/` directory for:
- Product management
- Sales data retrieval
- User authentication
- Financial reporting

## 🔄 Backup & Recovery

### Automated Backups
- Daily database backups stored in `backups/` directory
- Configurable backup schedules
- Manual backup functionality in admin panel

### Recovery Process
1. Access admin backup panel
2. Download latest backup file
3. Restore using MySQL import:
```bash
mysql -u username -p kabras_store < backup_file.sql
```

## 🐛 Troubleshooting

### Common Issues
1. **Database Connection Failed**
   - Verify database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Check database user permissions

2. **Permission Denied Errors**
   - Set proper file permissions on upload directories
   - Check web server user permissions

3. **Session Issues**
   - Verify PHP session configuration
   - Check for conflicting session settings

### Debug Mode
Enable debug mode by setting in PHP configuration:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Grealishgit** - *Initial work*

## 🙏 Acknowledgments

- Built for Kabras Sugar Store management needs
- Inspired by modern retail management systems
- Uses open-source libraries and frameworks

## 📞 Support

For support and questions:
- Create an issue in the GitHub repository
- Check the troubleshooting section
- Review the API documentation

---

**Version**: 1.0.0
**Last Updated**: October 2025
**PHP Version Required**: 7.4+
**MySQL Version Required**: 5.7+