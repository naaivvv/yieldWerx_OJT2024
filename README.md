# YieldWerx Data Extraction and Analytics System

# Features
- Utilizes the Repository Pattern and Services for maintainability
- Follows clean code and Separation of Concerns principles
- Optimizes database performance by avoiding the N+1 problem.
- Implements a responsive design to ensure a good user experience on all devices
- Uses reusable components and layouts to avoid duplication of code
- utilizes charts and graphs to ensure that users can easily understand the information presented
- features a simple design to ensure easy navigation for users

# Languages and Tools
## Frontend
- Tailwind CSS 3
- Flowbite
- HTML

## Backend
- PHP 8
- Javascript

## Database
- MSSQL

## Tools
- Git
- Github
- ODBC driver
- Microsoft Drivers for PHP for SQL Server
- Composer
- XAMPP
- SQL Server Management Studio 20
 
## Requirements
- Node.js
- Composer
- PHP 8
- ODBC driver
- Microsoft Drivers for PHP for SQL Server
- XAMPP
- SQL Server Management Studio 20

### Installation Steps

7. Clone the repository inside C:\xampp\htdocs\

   ```bash
   git clone https://github.com/naaivvv/WireDesk.git
   ```

8. Install the dependencies

   ```bash
   composer install
   ```

   ```bash
   npm install
   ```
9. In the .env file, add database information to connect to the database

   ```env
   DB_SERVERNAME=SERVERNAME/SQLEXPRESS
   DB_DATABASE=yielWerx_OJT2024
   DB_USERNAME=
   DB_PASSWORD=

   ```
10. Launch the frontend asset of the system

   ```bash
   npm run dev
   ```
11. Visit the application

    ```bash
    http://localhost/yieldwerx_OJT2024/PHP/selection_page.php
    ```
