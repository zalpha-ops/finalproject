# 🛩️ Eagle Flight School Management System

A comprehensive web-based management system for flight schools, built with PHP and MySQL.

## 📋 Features

- **Student Management**: Track student profiles, progress, and achievements
- **Instructor Portal**: Manage courses, grades, and student assignments
- **Course Management**: Create and organize flight training courses
- **Scheduling System**: Schedule flight sessions, ground school, and exams
- **Grade Tracking**: Record and monitor student performance
- **Aircraft Management**: Track aircraft availability and maintenance
- **Announcements**: Communicate with students and instructors
- **Reports**: Generate progress and performance reports
- **Training Hours**: Log and track flight hours

## 🚀 Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP (for local development)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/eagle-flight-school.git
   cd eagle-flight-school
   ```

2. **Create the database**
   - Open phpMyAdmin
   - Import the `database_setup.sql` file
   - This will create the `eagle-flight-school` database with all tables

3. **Configure database connection**
   - Copy `db_connect_local.php.example` to `db_connect_local.php`
   - Update the database credentials:
     ```php
     $host = 'localhost';
     $dbname = 'eagle-flight-school';
     $username = 'root';
     $password = ''; // Your MySQL password
     ```

4. **Set up file permissions**
   ```bash
   chmod 755 uploads/
   ```

5. **Access the application**
   - Navigate to `http://localhost/eagle-flight-school/`
   - Default admin login:
     - Username: `admin`
     - Password: `admin123`
   - **⚠️ Change the default password immediately after first login!**

## 📁 Project Structure

```
eagle-flight-school/
├── admin_*.php           # Admin dashboard and management pages
├── instructor_*.php      # Instructor portal pages
├── student_*.php         # Student portal pages
├── db_connect_local.php  # Database configuration (not in git)
├── database_setup.sql    # Database schema and setup
├── uploads/              # User uploaded files
├── vendor/               # Composer dependencies
└── README.md            # This file
```

## 🔐 Security Notes

- Never commit `db_connect_local.php` or any files with credentials
- Change default admin password immediately
- Keep PHP and MySQL updated
- Use HTTPS in production
- Regularly backup your database

## 🛠️ Configuration

### Database Connection Files

The system supports multiple database configurations:
- `db_connect_local.php` - Local development (XAMPP/WAMP)
- `db_connect.php` - Production server
- Create your own based on your environment

### File Uploads

Configure upload directory in your PHP files:
```php
$upload_dir = 'uploads/';
```

## 📊 Database Schema

The system includes the following main tables:
- `users` - System users (students, instructors, admins)
- `student_profiles` - Detailed student information
- `instructors` - Instructor information
- `courses` - Flight training courses
- `schedules` - Training session schedules
- `grades` - Student grades and assessments
- `aircraft` - Aircraft fleet management
- `assignments` - Course assignments
- `announcements` - System announcements

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👥 Support

For support, email support@eagleflight.com or open an issue in the repository.

## 🔄 Version History

- **v1.0.0** - Initial release
  - Student and instructor portals
  - Course management
  - Scheduling system
  - Grade tracking

## ⚠️ Important Notes

- This system is designed for educational purposes
- Always backup your database before updates
- Test thoroughly before deploying to production
- Keep sensitive files out of version control

---

Made with ❤️ for Eagle Flight School
