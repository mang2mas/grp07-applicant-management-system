# Group 07 – Applicant Management System

A web-based Applicant Management System developed by Group 07 to streamline and manage the applicant recruitment process. The system provides a centralized platform for managing applicant information, departments, positions, interviews, and related recruitment records.

## Table of Contents

- [Project Overview](#project-overview)
- [Technology Stack](#technology-stack)
- [Prerequisites](#prerequisites)
- [Project Structure](#project-structure)
- [Installation and Setup](#installation-and-setup)
- [Database Configuration](#database-configuration)
- [Running the Application](#running-the-application)
- [Troubleshooting](#troubleshooting)
- [Development Team](#development-team)

## Project Overview

The Group 07 Applicant Management System is designed to help organize and manage applicant-related information through a centralized web application.

The project uses PHP for server-side processing and PostgreSQL for database management.

## Technology Stack

| Component | Technology |
|---|---|
| Frontend | HTML, CSS, JavaScript |
| CSS Framework | Tailwind CSS |
| Backend | PHP |
| Database | PostgreSQL |
| Database Connectivity | PDO / PostgreSQL |
| Development Environment | Visual Studio Code |
| Version Control | Git and GitHub |

## Prerequisites

Before installing the project, make sure the following are installed on your computer:

1. **PHP** – Required to execute the application.
2. **PostgreSQL** – Required for database management.
3. **pgAdmin 4** – Recommended for managing the PostgreSQL database.
4. **Visual Studio Code** – Recommended code editor.
5. **Git** – Required to clone the repository.

### Required PHP Extensions

Make sure the following PHP extensions are enabled:

- `pdo`
- `pdo_pgsql`

To check your PHP installation, open your terminal and execute:

```bash
php -v
```

To check the enabled extensions:

```bash
php -m
```

Make sure `PDO` and `pdo_pgsql` appear in the list.

## Project Structure

The project follows this general directory organization:

```text
grp07-applicant-management-system/
│
├── config/
│
├── db/
│   ├── 01_schema.sql
│   └── 02_seed.sql
│
├── public/
│   └── index.php
│
├── src/
│   ├── actions/
│   └── views/
│   └── bootstrap.php
│   └── helpers.php
│
├── .env.example
├── .gitignore
└── README.md
```

**Note:** The directory structure above describes the main application components. Additional files and directories may be present in the repository.

## Installation and Setup

Follow these instructions to set up the project on your local machine.

### Step 1: Clone the Repository

Open your terminal and execute:

```bash
git clone https://github.com/mang2mas/grp07-applicant-management-system.git
```

Navigate to the project directory:

```bash
cd grp07-applicant-management-system
```

### Step 2: Open the Project

Open the project in Visual Studio Code:

```bash
code .
```

### Step 3: Configure Environment Variables

Create a local environment configuration file by copying the example:

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**Command Prompt:**

```cmd
copy .env.example .env
```

Open the `.env` file and configure your PostgreSQL connection credentials:

```env
DB_HOST=127.0.0.1
DB_PORT=5432
DB_NAME=grp07_applicantdb
DB_USER=your_postgres_username
DB_PASSWORD=your_postgres_password
DB_SSLMODE=disable
```

Replace the username and password with your actual PostgreSQL credentials.

**Important:** Never upload your `.env` file or expose your database credentials in a public repository.

## Database Configuration

The application uses PostgreSQL as its database management system.

### Step 1: Create the Database

Open pgAdmin 4 or your PostgreSQL SQL interface.

Create a new database named:

```sql
CREATE DATABASE grp07_applicantdb;
```

### Step 2: Execute the Database Schema

Open the SQL file:

```text
db/01_schema.sql
```

Execute the entire script against the `grp07_applicantdb` database.

This creates the database tables and their corresponding relationships, constraints, and other database structures.

### Step 3: Insert Initial Database Records

After successfully executing the schema, open:

```text
db/02_seed.sql
```

Execute the script against the same database.

This inserts the initial records required by the application.

**Important:** Always execute the schema before the seed file. Some records depend on existing parent records and foreign key relationships.


## Running the Application

Once the database and environment configuration are ready, you can start the application.

### Step 1: Open the Project Terminal

Make sure your terminal is located in the root project directory:

```text
grp07-applicant-management-system/
```

### Step 2: Start the PHP Development Server

Execute:

```bash
php -S localhost:8000 -t public
```

The PHP development server should start successfully.

### Step 3: Open the Application

Open your preferred web browser and navigate to:

```text
http://localhost:8000
```

The Applicant Management System should now be accessible.

### Step 4: Stop the Development Server

To stop the running PHP development server, return to the terminal and press:

```text
CTRL + C
```

## Troubleshooting

Here are some common issues you might encounter while setting up the application.

| Problem | Possible Solution |
|---|---|
| PHP command not recognized | Add PHP to your system PATH or verify your PHP installation. |
| Database connection failed | Check your PostgreSQL credentials and database configuration in `.env`. |
| PDO driver not found | Enable the `pdo_pgsql` PHP extension. |
| Database does not exist | Make sure `grp07_applicantdb` has been created. |
| Foreign key constraint violation | Execute the schema first and ensure referenced parent records exist before inserting dependent records. |
| Address already in use | Use another available port, such as `php -S localhost:8001 -t public`. |
| Page not found | Make sure the PHP server is running with `public` as its document root. |

## Development Team

**Group 07**

Developed as an academic project for educational purposes.

**Repository:**  
https://github.com/mang2mas/grp07-applicant-management-system

---

*Group 07 – Applicant Management System*
