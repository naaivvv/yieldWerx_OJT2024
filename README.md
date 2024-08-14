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
   

3. Make sure the PHP version matches the php_pdo_sqlsrv and php_sqlsrv
   
   ![Screenshot 2024-08-14 230820](https://github.com/user-attachments/assets/0a55b205-64c7-4b55-8e2b-1f00d41af48a)
   ![Screenshot 2024-08-14 231159](https://github.com/user-attachments/assets/b6a52d8b-ee3e-4774-854e-cb3232e55975)
   
   *Note: The PHP version presented in step number 2 is 8.1, therefore, php_pdo_sqlsrv_81_ts & php_sqlsrv_81_ts was chose*
   
5. Insert the files as extension in php.ini
 
 ![image](https://github.com/user-attachments/assets/43a677e6-dd9a-42ef-86fd-d392b32205d6)
 

5. Establish connection in VS Code



6. Clone the repository inside C:\xampp\htdocs\

   ```bash
   git clone https://github.com/naaivvv/yieldWerx_OJT2024
   ```

7. Install the dependencies

   ```bash
   composer install
   ```

   ```bash
   npm install
   ```
   
8. In the (.env file), add database information to connect to the database

   ```env
   DB_SERVERNAME=SERVERNAME/SQLEXPRESS
   DB_DATABASE=yielWerx_OJT2024
   DB_USERNAME=
   DB_PASSWORD=
   ```
   
9. Launch the frontend asset of the system

   ```bash
   npm run dev
   ```

10. Visit the application

    ```bash
    http://localhost/yieldwerx_OJT2024/PHP/selection_page.php
    ```
