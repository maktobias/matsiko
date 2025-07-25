# Restaurant Management System

A comprehensive web-based restaurant management system built with PHP, MySQL, HTML, CSS, and JavaScript. This system provides complete functionality for managing restaurant operations including orders, tables, menu items, users, and reporting.

## Features

### Admin Panel
- **Dashboard** - Overview of daily operations with key metrics
- **User Management** - Create, edit, and manage system users
- **User Groups Management** - Set up role-based permissions
- **Store Management** - Manage multiple restaurant locations
- **Table Management** - Configure and monitor restaurant tables
- **Product & Categories Management** - Manage menu items and categories
- **Order Management** - Track and update food orders
- **Reports & Analytics** - View earnings, bills, and generate reports
- **Company Information** - Manage restaurant details and settings

### Employee Panel
- **Employee Dashboard** - Role-based dashboard for staff
- **Order Processing** - Create and manage orders (permission-based)
- **Table Status** - Monitor table availability (permission-based)
- **Menu Access** - View product catalog (permission-based)

### Key Functionality
- **Real-time Order Tracking** - Live status updates for orders
- **Table Status Management** - Available, occupied, reserved, maintenance
- **Payment Processing** - Track paid and unpaid bills
- **Invoice Generation** - Printable receipts and invoices
- **Monthly Reports** - Comprehensive analytics and reporting
- **Multi-store Support** - Manage multiple restaurant locations
- **Role-based Access Control** - Granular permissions system
- **Responsive Design** - Works on desktop and mobile devices

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Quick Setup

1. **Download/Clone the project**
   ```bash
   git clone https://github.com/yourusername/restaurant-management-system.git
   cd restaurant-management-system
   ```

2. **Configure Database**
   - Create a MySQL database named `restaurant_management`
   - Update database credentials in `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'restaurant_management';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

3. **Run Setup**
   - Navigate to `http://yourdomain.com/setup.php` in your browser
   - This will create all necessary tables and sample data
   - **Important**: Delete `setup.php` after installation for security

4. **Login**
   - Go to `http://yourdomain.com/login.php`
   - Default credentials:
     - Username: `admin`
     - Password: `password`
   - **Change the default password immediately after first login**

## File Structure

```
restaurant-management-system/
├── admin/                  # Admin panel pages
│   ├── dashboard.php
│   ├── users.php
│   ├── user_groups.php
│   ├── stores.php
│   ├── tables.php
│   ├── products.php
│   ├── categories.php
│   ├── orders.php
│   ├── reports.php
│   └── company.php
├── employee/               # Employee panel pages
│   └── dashboard.php
├── assets/                 # Static assets
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/                 # Configuration files
│   └── database.php
├── includes/               # Common includes
│   ├── auth.php
│   ├── header.php
│   ├── sidebar.php
│   └── footer.php
├── database/               # Database schema
│   └── schema.sql
├── index.php              # Main entry point
├── login.php              # Login page
├── logout.php             # Logout handler
├── setup.php              # Installation script
└── README.md
```

## Usage Guide

### For Administrators

1. **User Management**
   - Create employee accounts with appropriate roles
   - Assign users to groups with specific permissions
   - Monitor user activity and access

2. **Store Setup**
   - Add restaurant locations
   - Assign managers to stores
   - Configure store-specific settings

3. **Menu Management**
   - Create product categories
   - Add menu items with pricing and details
   - Set availability status

4. **Table Configuration**
   - Add tables with capacity information
   - Monitor table status in real-time
   - Manage reservations

5. **Order Processing**
   - View all orders across locations
   - Update order status
   - Process payments

6. **Reporting**
   - Generate daily, weekly, monthly reports
   - Track revenue and expenses
   - Monitor popular items

### For Employees

1. **Taking Orders**
   - Select available table
   - Add items from menu
   - Calculate totals and process

2. **Table Management**
   - Update table status
   - Monitor occupancy
   - Handle reservations

3. **Order Tracking**
   - Monitor order progress
   - Update status as needed
   - Handle special requests

## Security Features

- **Password Hashing** - All passwords are securely hashed
- **Session Management** - Secure session handling
- **Role-based Access** - Granular permission system
- **SQL Injection Protection** - Prepared statements used throughout
- **Input Validation** - All user inputs are validated and sanitized

## Default User Groups

1. **Super Admin** - Full system access
2. **Manager** - Orders, tables, products, reports access
3. **Waiter** - Orders and tables access
4. **Kitchen Staff** - Orders access only

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL server is running
   - Verify database exists

2. **Permission Denied**
   - Check file permissions on web server
   - Ensure PHP has write access to session directory

3. **Blank Page**
   - Enable PHP error reporting
   - Check server error logs
   - Verify all required PHP extensions are installed

### Support

For technical support or feature requests, please:
1. Check existing documentation
2. Search through known issues
3. Create detailed bug reports with system information

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Changelog

### Version 1.0.0
- Initial release
- Complete restaurant management functionality
- Admin and employee panels
- Reporting and analytics
- Multi-store support

---

**Note**: This is a demonstration system. For production use, additional security measures and testing are recommended.