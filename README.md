# Restaurant Management System

A comprehensive web-based restaurant management system built with PHP, MySQL, HTML, CSS, and JavaScript. This system provides complete restaurant operations management including orders, inventory, users, reports, and more.

## Features

### Admin Panel
- **Dashboard**: Overview of daily/monthly statistics, recent orders, and quick actions
- **User Management**: Create, edit, delete users with role-based access control
- **User Groups Management**: Define roles and permissions for different user types
- **Store Management**: Manage multiple restaurant locations/branches
- **Company Information Management**: Update restaurant details and settings

### Employee Panel
- **Order Management**: Create, update, and track food orders
- **Kitchen Operations**: View and update order preparation status
- **Table Management**: Manage table status and reservations

### Core Features
- **Product & Categories Management**: Manage food items, categories, pricing, and availability
- **Restaurant Table Management**: Handle table layouts, capacity, and QR codes
- **Order Processing**: Complete order lifecycle from creation to completion
- **Payment Tracking**: Monitor paid/unpaid bills and payment methods
- **Invoice Generation**: Generate and print invoices for orders
- **Reports & Analytics**: 
  - View total earnings and revenue breakdown
  - Monthly and daily reports
  - Order statistics and trends
  - Export functionality

### User Access Control
- **Administrator**: Full system access
- **Manager**: Store management, products, orders, reports
- **Waiter**: Order taking and table management
- **Kitchen**: Kitchen operations and order status updates

## System Requirements

- **Web Server**: Apache/Nginx
- **PHP**: Version 7.4 or higher
- **Database**: MySQL 5.7 or higher
- **Browser**: Modern browsers (Chrome, Firefox, Safari, Edge)

## Installation

### 1. Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE restaurant_management;
```

2. Import the database schema:
```bash
mysql -u username -p restaurant_management < database/restaurant_db.sql
```

3. Update database credentials in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'restaurant_management');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 2. File Permissions

Set appropriate file permissions:
```bash
chmod -R 755 assets/
chmod -R 644 *.php
chmod -R 644 includes/
chmod -R 644 classes/
```

### 3. Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

## Default Login Credentials

**Administrator Account:**
- Username: `admin`
- Password: `password`

**Important:** Change the default password immediately after installation!

## File Structure

```
restaurant_management/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── classes/
│   ├── User.php
│   ├── Order.php
│   ├── Product.php
│   └── Store.php
├── config/
│   └── database.php
├── database/
│   └── restaurant_db.sql
├── includes/
│   ├── session.php
│   ├── header.php
│   └── footer.php
├── dashboard.php
├── login.php
├── logout.php
├── orders.php
├── products.php
├── users.php
├── reports.php
├── index.php
└── README.md
```

## Configuration

### Database Configuration
Edit `config/database.php` to match your database settings.

### Session Configuration
Session settings are managed in `includes/session.php`. Default session timeout is 2 hours.

### Application Settings
Key settings can be modified in the database `settings` table:
- Tax rate
- Currency symbol
- Order/Invoice prefixes

## Usage Guide

### For Administrators

1. **User Management**: 
   - Navigate to Users → Add new users and assign roles
   - Create user groups with specific permissions

2. **Store Setup**:
   - Add stores/branches in Store Management
   - Configure tables for each store
   - Generate QR codes for tables

3. **Menu Management**:
   - Create product categories
   - Add food items with pricing and details
   - Set featured products

### For Staff

1. **Taking Orders**:
   - Select table and customer details
   - Add items to order
   - Process payment

2. **Kitchen Operations**:
   - View incoming orders
   - Update preparation status
   - Mark orders ready

3. **Reports**:
   - Generate daily/monthly reports
   - Track earnings and payments
   - Export data for analysis

## Security Features

- **Password Hashing**: All passwords are hashed using PHP's password_hash()
- **SQL Injection Prevention**: All queries use prepared statements
- **XSS Protection**: All output is escaped using htmlspecialchars()
- **Session Security**: Secure session management with timeout
- **Role-based Access Control**: Granular permissions system
- **Input Validation**: Client and server-side validation

## Customization

### Adding New Features

1. Create new PHP classes in `classes/` directory
2. Add new pages following the existing structure
3. Update navigation menus in `includes/header.php`
4. Add appropriate permissions to user groups

### Styling

- Main styles are in `assets/css/style.css`
- Uses CSS Grid and Flexbox for responsive design
- Font Awesome icons included
- Custom color schemes can be modified in CSS variables

### Database Extensions

- Add new tables as needed
- Update corresponding PHP classes
- Maintain foreign key relationships

## API Endpoints

The system includes several AJAX endpoints for dynamic functionality:
- Order status updates
- Payment processing
- Real-time data updates

## Troubleshooting

### Common Issues

1. **Database Connection Errors**:
   - Verify database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Check database permissions

2. **Permission Errors**:
   - Verify file permissions are correctly set
   - Check web server user permissions

3. **Session Issues**:
   - Ensure session.save_path is writable
   - Check PHP session configuration

4. **Login Problems**:
   - Verify password hash in database
   - Check user status is 'active'
   - Clear browser cookies/cache

### Debug Mode

Enable PHP error reporting for development:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Check the troubleshooting section
- Review the code documentation
- Submit issues for bugs or feature requests

## Version History

- **v1.0.0**: Initial release with core functionality
- User management and authentication
- Order and product management
- Basic reporting features
- Responsive web interface

## Future Enhancements

- Mobile app integration
- Advanced analytics and charts
- Inventory management
- Multi-language support
- Online ordering system
- Integration with payment gateways
- Customer loyalty programs