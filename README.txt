Boba Query Order Management System

This project implements a web-based system to manage drink orders for a boba shop. It uses PHP and MySQL and runs locally through the XAMPP server environment. Employees can log in and are redirected to either a cashier or barista view depending on their role. Cashiers can place and edit orders, while baristas can view and complete them.

Project Files:

config.php – Database connection configuration
index.php – Entry point that redirects users to the appropriate page
login.php – Login interface for employees
logout.php – Ends session and returns user to login
cashier.php – Cashier dashboard to place and edit orders
barista.php – Barista dashboard to view and complete orders
create.sql – SQL script to create all tables
load.sql – SQL script to insert sample data
logo.png – Image used for branding on the login page

Installation Instructions:

Install XAMPP from https://www.apachefriends.org
Start Apache and MySQL in the XAMPP Control Panel.
Create the database:
Go to http://localhost/phpmyadmin
Create a database named boba_query
Use the SQL tab to run create.sql
Then run load.sql to populate sample data
Place all project files into the directory:
C:\xampp\htdocs\boba_query\ (or the equivalent directory for your system)
In config.php, make sure your database credentials are correct (default: user root, no password).

How to Use the System:

Open a web browser and visit:
http://localhost/boba_query/
Log in with one of the sample credentials:
Cashier (ID: 1, Password: alice123)
Barista (ID: 2, Password: bob123)
Based on the role, users are redirected to either:
cashier.php: Allows placing or editing drink orders
barista.php: Displays current orders with option to mark them as completed
Use the "Log Out" link to end the session and return to the login screen.

Database Tables:

employees: Stores employee information, password hashes, and roles
drinks: List of drink options
toppings: List of topping options
orders: Records customer orders linked to drinks, toppings, and the placing employee

Functionality Summary:

Secure login and session handling
Role-based interface redirection
Cashiers can create and update orders
Baristas can view and complete (delete) orders

