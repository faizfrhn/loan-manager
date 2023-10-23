# Loan Manager App

A simple loan manager app as part of an assessment. 
The app allows for a customer to apply for a loan and if the loan application is approved, to be able submit the weekly repayment.
Also, included basic function for admin to approve any loan that is applied by the customer.

## Installation

Clone the repo locally:

```sh
git clone https://github.com/faizfrhn/loan-manager.git loan-manager && cd loan-manager
```

Install PHP dependencies:

```sh
composer install
```

Setup configuration:

```sh
cp .env.example .env
```

Generate application key:

```sh
php artisan key:generate
```

Create an SQLite database. You can also use another database (MySQL, Postgres), simply update your configuration accordingly.

```sh
touch database/database.sqlite
```

Run database migrations:

```sh
php artisan migrate
```

Run database seeder:

```sh
php artisan db:seed
```

Create a symlink to the storage:

```sh
php artisan storage:link
```

Run the dev server (the output will give the address):

```sh
php artisan serve
```

You're ready to go! Visit the url in your browser, and login with:

Admin
-   **Username:** admin@example.com
-   **Password:** password

Customer
-   **Username:** customer@example.com
-   **Password:** password

Customer Two
-   **Username:** customer2@example.com
-   **Password:** password
