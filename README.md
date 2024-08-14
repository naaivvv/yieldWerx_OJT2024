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
1. Connect PHP to Microsoft SQL Server

   ``` bash
   https://www.youtube.com/watch?v=XLTkcB_T8Mo
   ```
   
2. Transfer php_pdo_sqlsrv and php_sqlsrv files to php ext folder
   
   ![image](https://github.com/user-attachments/assets/8ec568d3-223e-41a9-9c0f-ce184697ea3b)
   ![image](https://github.com/user-attachments/assets/93c55b12-024c-4930-b692-7787b7ccd1ea)
   

3. Insert the files as extension in php.ini
 
 ![image](https://github.com/user-attachments/assets/43a677e6-dd9a-42ef-86fd-d392b32205d6)
 

4. Establish connection in VS Code



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
