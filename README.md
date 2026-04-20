# Loyalty Point System API

A RESTful API built with **Symfony 7** for managing customer loyalty points, including earning points from transactions and redeeming gifts.

---

## Tech Stack

- **PHP** 8.5.4
- **Symfony** 7.x
- **Doctrine ORM** — database abstraction
- **MySQL** 9.6.0 (via Docker)
- **Docker** — containerized database

---

## Requirements

- PHP >= 8.1
- Composer
- Docker Desktop
- Symfony CLI (optional)

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/your-username/my_project.git
cd my_project
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

Copy the example env file and update the values:

```bash
cp .env .env.local
```

Edit `.env.local`:

```dotenv
DATABASE_URL="mysql://root:your_password@127.0.0.1:3306/loyalty?serverVersion=9.6.0&charset=utf8mb4"
```

### 4. Start MySQL with Docker

```bash
docker run -d \
  --name mysql-loyalty \
  -e MYSQL_ROOT_PASSWORD=rootpassword \
  -e MYSQL_DATABASE=loyalty \
  -p 3306:3306 \
  mysql:8.0
```

### 5. Run database migrations

```bash
php bin/console doctrine:migrations:migrate
```

### 6. Start the development server

```bash
# Using PHP built-in server
php -S localhost:8000 -t public/

# Or using Symfony CLI
symfony server:start
```

The API will be available at `http://localhost:8000`.

---

## Database Schema

```
members       — stores customer information
wallets       — stores current point balance per member
transactions  — stores purchase transaction records
points        — log of all point changes (earn/redeem)
gifts         — gift catalog available for redemption
redemptions   — records of gift redemptions
```

---

## API Documentation

### Base URL

```
http://localhost:8000/api/v1
```

---

### API 1 — Earn Points

**Endpoint:** `POST /api/v1/transactions`

**Request Body:**
```json
{
    "member_id": 1,
    "amount": 500000
}
```

**Business Logic:**
- Creates a record in `transactions`
- Calculates reward points: `points = amount × 1%`
- Creates a record in `points` with positive `point_amount`
- Updates `balance` in `wallets`
- All steps are wrapped in a single Database Transaction

**Success Response `201`:**
```json
{
    "success": true,
    "message": "Giao dịch thành công",
    "data": {
        "transaction_id": 1,
        "member_id": 1,
        "amount": 500000,
        "points_earned": 5000,
        "new_balance": "5000"
    }
}
```

**Error Responses:**

| Status | Message |
|--------|---------|
| `400` | `member_id và amount là bắt buộc` |
| `400` | `amount phải lớn hơn 0` |
| `404` | `Member không tồn tại` |
| `404` | `Ví không tồn tại` |
| `500` | `Lỗi hệ thống: ...` |

---

### API 2 — Redeem Gift

**Endpoint:** `POST /api/v1/redemptions`

**Request Body:**
```json
{
    "member_id": 1,
    "gift_id": 1
}
```

**Business Logic:**
- Checks gift exists and `stock > 0`
- Checks member wallet balance >= `point_cost`
- Decreases gift stock by 1
- Creates a record in `redemptions`
- Creates a record in `points` with negative `point_amount`
- Updates wallet `balance`
- Uses `PESSIMISTIC_WRITE` lock to handle race conditions

**Success Response `201`:**
```json
{
    "success": true,
    "message": "Đổi quà thành công",
    "data": {
        "redemption_id": 1,
        "member_id": 1,
        "gift_name": "Voucher 50k",
        "points_used": 500,
        "new_balance": "4500"
    }
}
```

**Error Responses:**

| Status | Message |
|--------|---------|
| `400` | `member_id và gift_id là bắt buộc` |
| `400` | `Quà không còn khả dụng` |
| `400` | `Quà đã hết hàng` |
| `400` | `Số dư điểm không đủ` |
| `404` | `Member không tồn tại` |
| `404` | `Quà không tồn tại` |
| `500` | `Lỗi hệ thống: ...` |

---

### API 3 — Wallet Inquiry

**Endpoint:** `GET /api/v1/members/{member_id}/wallet`

**Example:** `GET /api/v1/members/1/wallet`

**Business Logic:**
- Returns member information
- Returns current wallet balance
- Returns last 10 point history records (both earn and redeem)

**Success Response `200`:**
```json
{
    "success": true,
    "data": {
        "member": {
            "id": 1,
            "fullname": "Nguyễn Văn An",
            "email": "an.nguyen@gmail.com",
            "created_at": "2026-04-20 10:00:00"
        },
        "wallet": {
            "id": 1,
            "balance": "4500",
            "updated_at": "2026-04-20 10:30:00"
        },
        "point_history": [
            {
                "id": 2,
                "point_amount": -500,
                "description": "Đổi quà: Voucher 50k",
                "created_at": "2026-04-20 10:30:00",
                "type": "redeem"
            },
            {
                "id": 1,
                "point_amount": 5000,
                "description": "Tích điểm từ giao dịch #1 - Số tiền: 500000",
                "created_at": "2026-04-20 10:00:00",
                "type": "earn"
            }
        ]
    }
}
```

**Error Responses:**

| Status | Message |
|--------|---------|
| `404` | `Member không tồn tại` |
| `404` | `Ví không tồn tại` |

---

## Project Structure

```
my_project/
├── config/                  — Symfony configuration
│   ├── packages/
│   │   └── doctrine.yaml    — Database configuration
│   └── routes.yaml          — Route configuration
├── migrations/              — Database migration files
├── public/
│   └── index.php            — Application entry point
├── src/
│   ├── Controller/
│   │   ├── TransactionController.php
│   │   ├── RedemptionController.php
│   │   └── WalletController.php
│   ├── Entity/
│   │   ├── Member.php
│   │   ├── Wallet.php
│   │   ├── Transaction.php
│   │   ├── Point.php
│   │   ├── Gift.php
│   │   └── Redemption.php
│   └── Repository/
│       └── PointRepository.php
├── .env                     — Environment variables (example)
├── .env.local               — Environment variables (local, not committed)
├── .gitignore
├── composer.json
└── README.md
```


## Business Rules

| Rule | Detail |
|------|--------|
| Points formula | `points = amount × 1%` |
| Minimum amount | Must be greater than 0 |
| Balance consistency | `wallets.balance` must always equal the sum of all related `points.point_amount` |
| Concurrency | Uses `PESSIMISTIC_WRITE` lock to prevent race conditions on gift stock and wallet balance |
| Transaction integrity | All steps in earn/redeem must succeed together or roll back entirely |

---
