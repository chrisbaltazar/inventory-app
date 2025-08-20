# README 

## 📦 Inventory Management App

A demo Inventory Management Application built with PHP + Symfony + MySQL, showcasing clean architecture, role-based access control (RBAC), and modern development practices.

## 🚀 Features

- Authentication & Authorization 
- User registration & login 
- Role-Based Access Control (Admin, User)
- User & roles Management 
- Inventory Management 
  - Track item availability & status 
- Loan System 
  - Record loans & returns 
  - Associate users with borrowed items 
- Event Tracking 
- Responsive UI 
  - Clean, mobile-friendly design

## 🛠️ Tech Stack
- Backend: Symfony 6.3 (PHP 8.2)
- Database: MySQL 8
- Frontend: Twig templates, JS Datatables & CSS Bootstrap 5 

## ⚙️ Installation & Setup

### Prerequisites
- PHP 8.2+ 
- Composer 
- MySQL 8 
- Symfony CLI (recommended)

### Steps
#### 1. Clone the repository
```
git clone https://github.com/yourusername/inventory-app.git
cd inventory-app
```
#### 2. Install dependencies
```
composer install
```

#### 3. Configure environment variables
cp .env.dev .env.local

#### 4. Update DB credentials in .env.local
```
DATABASE_URL=
```

#### 5. Run database migrations
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

#### 6. Start the Symfony server
```
symfony serve
```

The app will be available at: http://localhost:8000

## 🔑 Default Users (Demo Data)

Run the following to load fixtures:
```
php bin/console doctrine:fixtures:load
```
### _Docker installation WIP_