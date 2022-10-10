## Installation

Clone the repository.

```bash
git clone https://github.com/daenuli/joybox.git
```

Switch to the repository folder.

```bash
cd joybox
```

Use the package manager [composer](https://getcomposer.org/) to install required packages.

```bash
composer install
```

Create new database.

```bash
mysql -u root -p -e'create database joybox'
```

Copy the example env file and changes config in the .env file.

```bash
cp .env.example .env
```

Run database migration

```bash
php artisan migrate
```

Seed database to get user account 

```bash
php artisan db:seed
```

Run development server

```bash
php -S localhost:8000 -t public
```

Run unit test

```bash
vendor/bin/phpunit
```

Access development server at http://localhost:8000

Access API Documentation at http://localhost:8000/api/documentation

<!-- Access development server at http://localhost:8000 -->
<!-- Access API Documentation at http://localhost:8000/api/documentation -->