# Mini CRM

Mini CRM system built with Laravel 12.x,Bootstrap and javascript following best practices for optimization and scalability.

## Features

- **Contacts Management**: Full CRUD operations with multi-department support
- **Departments Management**: Full CRUD operations
- **Authentication**: Secure API authentication using Laravel Sanctum
- **Search & Filtering**: AJAX-ready search with partial name matching, phone, and department filters
- **Pagination**: Configurable pagination or "Load More" functionality

## Requirements

- PHP 8.2+
- MySQL 8.0+ / MariaDB 10.3+
- Composer 2.x

## Backend Installation

### 1. Clone and Install Dependencies

```bash
cd mini-crm
cd backend
composer install
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database

Edit `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_crm
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Run Migrations and Seeders

```bash
php artisan migrate
php artisan db:seed
```

This will create:
- Admin user: `admin@layout.com` / `adminlayout#$`
- Default departments (R&D, Integration, Sales, Marketing, HR, Finance, IT, Operations)
- Import contacts from the CSV file

### 5. Start the Development Server

```bash
php artisan serve
```

API will be available at `http://localhost:8000/api`
Postman collection under `./backend/postman_collection.json`



## Database Schema

### Tables

1. **users** - Admin users for authentication
2. **contacts** - Contact information
3. **departments** - Department definitions
4. **contact_department** - Many-to-many pivot table

### Relationships

- A **Contact** can belong to **multiple Departments** (Many-to-Many)
- A **Department** can have **multiple Contacts** (Many-to-Many)

---
## Frontend Installation

### Start the project

```bash
cd mini-crm
cd frontend 
php -S localhost:3000
```

## Configuration Options

### Pagination Type
Set in `.env` and just refresh your browser:
```env
PAGINATION_TYPE=pagination  # or 'load_more'
PER_PAGE=5
```
---