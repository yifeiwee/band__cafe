# Band Cafe - Band Practice Management System

**Band Cafe** is a PHP/MySQL web application designed to manage band practice sessions. It offers separate interfaces for users and administrators, allowing users to register, log in, submit practice requests, and view their schedules, while admins can review, approve, or reject requests. The application features a modern, Shadcn-inspired UI built with Tailwind CSS for a clean and user-friendly experience.

## Features

- **User Interface**:
  - Register and log in with secure authentication.
  - Submit practice requests with details like date, time, transport needs, and goals.
  - View personal practice records in a table format.
  - See approved sessions in a calendar view using FullCalendar.
- **Admin Interface**:
  - Manage all practice requests with options to approve or reject.
  - Access a dedicated dashboard for administrative tasks.
- **Design**:
  - Modern, minimalistic UI inspired by Shadcn design principles.
  - Responsive layout using Tailwind CSS for seamless use on various devices.
  - Modular UI components for reusable and maintainable design elements.

## Technologies

- **Backend**: PHP 7/8 with MySQL (or MariaDB) database.
- **Frontend**: HTML5, CSS3 (Tailwind CSS), JavaScript (FullCalendar for calendar view).
- **Security**: Password hashing with `password_hash()` and session management for user authentication.
- **Database**: MySQL with tables for users and practice requests.

## Installation

### Prerequisites
- XAMPP (or similar local server environment) with PHP 7/8 and MySQL/MariaDB installed.
- Web browser for accessing the application.

### Setup Steps
1. **Clone or Download the Project**:
   - Place the project folder in your XAMPP `htdocs` directory (e.g., `c:/xampp/htdocs/band__cafe`).

2. **Database Configuration**:
   - Start XAMPP and ensure Apache and MySQL services are running.
   - Open phpMyAdmin (usually at `http://localhost/phpmyadmin`) and create a new database named `bandcafe`.
   - Import the SQL schema from `database_schema.sql` to set up the required tables (`users` and `practice_requests`).
   - Update `config.php` with your database credentials (replace `db_user` and `db_pass` with your MySQL username and password).

3. **Run the Application**:
   - Access the application by navigating to `http://localhost/band__cafe` in your web browser.
   - You will be redirected to the login page. Register a new user or log in with existing credentials.

4. **Admin Access**:
   - To create an admin user, manually set the `role` field to `admin` for a specific user in the `users` table via phpMyAdmin, or register a user and update the role directly in the database.

## Usage

- **User Flow**:
  - Register a new account or log in with existing credentials.
  - From the dashboard, submit a new practice request with session details.
  - View your practice history under "My Practice Records" or check the calendar for approved sessions.
- **Admin Flow**:
  - Log in with an admin account to access the Admin Dashboard.
  - Review all practice requests and approve or reject them as needed.

## File Structure

- `index.php`: Entry point that redirects to login or dashboard based on session status.
- `config.php`: Database connection settings.
- `login.php` & `register.php`: User authentication pages.
- `dashboard.php`: Main user dashboard with navigation options.
- `request.php`: Form for submitting new practice requests.
- `my_records.php`: Displays user's practice records.
- `calendar.php` & `fetchEvents.php`: Calendar view for approved sessions.
- `admin.php`: Admin dashboard for managing requests.
- `logout.php`: Handles user logout.
- `database_schema.sql`: SQL schema for setting up the database.
- `components/`: Directory containing reusable UI components.
  - `header.php`: Header component for consistent page layout.
  - `input.php`: Input field component for forms.
  - `nav_card.php`: Navigation card component for dashboard links.
  - `button.php`: Button component for interactive elements.
  - `card.php`: Card component for content display.

## Database Schema

- **users**: Stores user information.
  - `id` (Primary Key, Auto Increment)
  - `username` (Unique, VARCHAR)
  - `password` (VARCHAR, hashed)
  - `role` (ENUM: 'user', 'admin')
- **practice_requests**: Stores practice session details.
  - `id` (Primary Key, Auto Increment)
  - `user_id` (Foreign Key referencing `users.id`)
  - `date` (DATE)
  - `start_time` & `end_time` (TIME)
  - `transport_needed` (TINYINT: 0=No, 1=Yes)
  - `target_goal` (VARCHAR)
  - `status` (ENUM: 'pending', 'approved', 'rejected')

## Security Notes

- Passwords are securely hashed using PHP's `password_hash()` function.
- User inputs are sanitized using prepared statements to prevent SQL injection.
- Session management ensures protected access to user and admin pages.

## Contributing

This project is a basic implementation of a band practice management system. Contributions for additional features (e.g., email notifications, advanced filtering) or UI enhancements are welcome. Please fork the repository or submit pull requests with your changes.

## License

This project is open-source and available for use under the MIT License. Feel free to modify and distribute as needed.

---

*Built with PHP, MySQL, and Tailwind CSS for a seamless band practice scheduling experience.*
