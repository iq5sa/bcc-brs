📊 Business Registration Search

A lightweight PHP web application that allows users to search and filter business registrations by registration key and year, complete with pagination and sub-activity details.



✨ Features
        • Search business registrations by registration key
	• 🏷️ Filter records by registration year using clickable tags
	• 📄 Paginate through large datasets
	• 🔗 View related sub-activity names from a joined table
	• ⚡ No framework — pure PHP + MySQL



🛠 Requirements
	• PHP 7.4+
	• MySQL 5.7+ or MariaDB
	• Optional: Apache/Nginx or local environments like XAMPP, Laragon, or MAMP


🚀 Installation & Setup

1. Clone the Repository

```bash
git clone https://github.com/iq5sa/bcc-brs.git
cd business-registration-search
```



2. Create the Database

Import the database.sql file into your MySQL server:

```bash
mysql -u root -p your_database_name < database.sql
```






3. Update Database Credentials

Open index.php and edit the database connection section to match your environment:

```php
$host = 'localhost';
$db = 'your_database_name';
$user = 'your_db_user';
$pass = 'your_db_password';

```




4. Start the PHP Development Server

You can run the app locally using PHP’s built-in web server:

```bash
php -S localhost:8000
```



Then open your browser and visit:

```bash
http://localhost:8000/index.php
```

