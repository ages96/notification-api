# Notification API

A simple Email Notification API built with Symfony 6, PHP 8.3, and MySQL.  
This project demonstrates creating, listing, and sending notifications with validation, caching, and pagination.

---

## Table of Contents

- [Overview](#overview)  
- [Requirements](#requirements)  
- [Project Setup](#project-setup)  
- [Database Design](#database-design)  
- [API Endpoints](#api-endpoints)  
- [Twig Dashboard](#twig-dashboard)  
- [Architecture & Decisions](#architecture--decisions)  
- [Workarounds & Notes](#workarounds--notes)  
- [Future Improvements](#future-improvements)  

---

## Overview

This API allows:

- Creating notifications  
- Listing notifications with pagination  
- Simulating sending notifications  
- Tracking status (pending → sent)  

For the purpose of this test, sending a notification is simulated without actual email delivery.

---

## Requirements

- PHP 8.3+  
- Symfony CLI  
- MySQL 8+  
- Composer  

---

## Project Setup

1. Install Dependencies:

```bash
composer install
```

2. Configure MySQL in `.env`:

```
DATABASE_URL="mysql://username:password@127.0.0.1:3306/notification_api"
```
3. Run Migration:

```bash
php bin/console doctrine:migrations:migrate
```

4. Run Server:

```bash
symfony serve
```

---

## Database Design

**Notification Entity**

| Field           | Type       | Notes                         |
|-----------------|-----------|-------------------------------|
| id              | int       | Primary key, auto-increment   |
| recipientEmail  | string    | Valid email (validated)       |
| subject         | string    | Non-empty                     |
| body            | text      | Non-empty                     |
| status          | string    | pending / sent                |
| createdAt       | datetime  | When created                  |
| sentAt          | datetime  | When “sent”                   |

Validation rules ensure correct and safe input.

---

## API Endpoints

| Method | Endpoint | Description | Request Body |
|--------|---------|------------|-------------|
| POST   | /api/notifications | Create new notification | `{ "recipientEmail": "...", "subject": "...", "body": "..." }` |
| GET    | /api/notifications?page=1&limit=10 | List notifications (paginated & cached) | N/A |
| POST   | /api/notifications/{id}/send | Simulate sending notification | N/A |

### Pagination & Cache

- Query params: `page` (default=1), `limit` (default=10)  
- Cached for 60 seconds to reduce database load  

---

## Twig Dashboard (Optional Frontend)

- `GET /dashboard/`  
- Create, list, and send notifications via a simple web interface  
- Flash messages provide success/warning feedback  
- Designed for demonstration purposes  

---

## Architecture & Decisions

1. Symfony 6 + Doctrine ORM: chosen for rapid setup, integrated database handling, and mature ecosystem.  
2. Entity Validation: ensures `recipientEmail` is valid and `subject` / `body` are non-empty.  
3. Controller Design: RESTful endpoints for clarity and separation of concerns.  
4. Caching: Symfony Cache for list endpoint to improve performance and reduce DB load.  
5. Pagination: implemented manually using `setFirstResult` & `setMaxResults` for scalability.  
6. Sending Logic: simulated sending with `status` and `sentAt`; Symfony Mailer could be integrated in production.  

---

## Workarounds & Notes

- Doctrine cache errors resolved by matching `symfony/orm-pack` and `doctrine/orm` versions with PHP 8.3.  
- Symfony CLI installation issues fixed by manually downloading the binary and moving it to `/usr/local/bin`.  
- Validation handling provides JSON output of all errors.  
- Twig dashboard is simple and optional, intended for demo/testing.  

---

## Future Improvements

- Implement real email sending with Symfony Mailer.  
- Add retry mechanism for failed sends.  
- Extend pagination metadata (`totalItems`, `totalPages`).  
- Add filtering by status or recipient.  
- Add unit and functional tests.  

---

## How to Test

1. Create Notification:

```bash
curl -X POST http://127.0.0.1:8000/api/notifications \
-H "Content-Type: application/json" \
-d '{"recipientEmail":"test@example.com","subject":"Hello","body":"World"}'
```

2. List Notifications:

```bash
curl http://127.0.0.1:8000/api/notifications?page=1&limit=5
```

3. Send Notification:

```bash
curl -X POST http://127.0.0.1:8000/api/notifications/1/send
```

## Demo

You can watch a short demo of the Notification API and the dashboard here:

[![Notification API Demo](https://img.youtube.com/vi/QhruPrumCIc/0.jpg)](https://www.youtube.com/watch?v=QhruPrumCIc)
