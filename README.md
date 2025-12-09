# Order & Payment Management API

Laravel-based RESTful API for managing orders and processing payments using an extensible payment gateway architecture.

---

## 🚀 Features

- RESTful API design  
- Authentication using JWT  
- Order management (create, update, delete, list, calculate total)  
- Payment processing using pluggable gateways (Strategy Pattern)  
- Config-based gateway setup through `.env`  
- Full request validation  
- API documentation  
- Unit & Feature tests

---

## ⚙️ Installation

```bash
git clone https://github.com/ahmedzarzamony/tocaan-order-api.git
cd project
composer install
cp .env.example .env
php artisan key:generate
```

---

## Database Setup

### - Configure .env

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=orders_db
DB_USERNAME=root
DB_PASSWORD=
```

### - Migrate  
```bash
php artisan migrate --seed
```
---

## JWT Authentication Setup

```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

Add to .env (automatically generated):

```bash
JWT_SECRET=xxxx
```

---

## Payment Gateways Configuration

```bash
config/payment.php

//Set default gateway
PAYMENT_GATEWAY=credit_card
```

### Example config:
```bash
<?php
return [
    'default_gateway' => env('PAYMENT_GATEWAY', 'credit_card'),
    'gateways' => [
        'credit_card' => [
            'class' => App\Services\Payment\CreditCardGateway::class,
            'api_key' => env('CREDIT_CARD_API_KEY', ''),
            'api_secret' => env('CREDIT_CARD_API_SECRET', ''),
        ],
        'paypal' => [
            'class' => App\Services\Payment\PaypalGateway::class,
            'client_id' => env('PAYPAL_CLIENT_ID', ''),
            'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
        ],
    ],
];

```

---

## Extensibility (Strategy Pattern)
The system uses Strategy Pattern to allow adding new payment gateways with minimal changes.

### How to add a new gateway:

#### 1- Create new class implementing the interface:
```bash
class StripeGateway implements PaymentGatewayInterface {
    public function process(Payment $payment): bool {
        // Stripe logic
    }
}
```

#### Add config in config/payment.php
```bash
'stripe' => [
    'class' => App\Services\Payment\StripeGateway::class,
    'api_key' => env('STRIPE_KEY'),
    'api_secret' => env('STRIPE_SECRET'),
],
```

 **NOTES:**
 * This will automatically be added to the valid gateway rules (ValidationRule),
 * and it will automatically register in the PaymentGatewayFactory.

 
✔ No changes required in controller.

✔ No changes in models

✔ Fully scalable design

---

## Authentication Endpoints

| Method | Endpoint       | Description             |
| ------ | -------------- | ----------------------- |
| POST   | /auth/login    | Login                   |
| POST   | /auth/register | register                |
| POST   | /auth/logout   | logout                |
| POST   | /auth/refresh-token | Refresh Token                |


Use Bearer token for authenticated endpoints:
```bash
Authorization: Bearer <token>
```

## Orders Endpoints

| Method | Endpoint     | Description             |
| ------ | ------------ | ----------------------- |
| POST   | /orders      | Create new order        |
| PUT    | /orders/{id} | Update order            |
| DELETE | /orders/{id} | Delete order            |
| GET    | /orders      | List orders (paginated) |
| GET    | /orders/{id} | Show order              |


## Payments Endpoint

| Method | Endpoint       | Description             |
| ------ | -------------- | ----------------------- |
| GET    | /payments      | List payments           |
| POST   | /payments      | pay order               |

---

## 📄 Notes & Assumptions

- Payment gateways are mocked for testing.
- Validation errors follow standard Laravel format.
- Standardized JSON responses for all endpoints.
- Designed to support real external payment integrations in the future.

---

## 🧪 Testing

```bash
php artisan test
```

**Included tests:**
- Feature Tests
- Create order
- Update order
- Delete order
- Prevent deleting order with payments
- Payment processing
- Pending order cannot be paid
- Authentication tests

**Unit Tests:**
- Gateway Factory returns correct driver
- CreditCardGateway / PaypalGateway
- Order total calculation

---

## Running the Project

```Bash
php artisan serve
```

## 📬 Postman Collection

A complete **Postman Collection** is included to help reviewers easily explore and test all API endpoints. The collection contains sample requests, responses, and error cases.

### 📁 Collection Location

The collection file is included in the repository under the `postman/` folder:


> ⚠️ Note: The collection already contains all necessary variables (e.g., `url`, `token`) inside it. No separate environment file is needed.

### 📁 Collection Structure

The collection is organized into logical folders:

#### **Auth**
- Register
- Login
- Logout
- Refresh Token

#### **Orders**
- Create Order
- Update Order
- Delete Order
- Get Order Details
- List Orders (with pagination)

#### **Payments**
- Pay for an Order
- Get Payments

### 🧪 Included Examples

Each request includes:
- Body examples (JSON)
- Headers
- Authorization (Bearer Token via collection variable)
- Success response examples
- Error response examples (e.g., validation errors, unauthorized access)
