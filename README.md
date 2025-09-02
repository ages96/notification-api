# Notification API

A simple Email Notification API built with Symfony 6, PHP 8.3, and MySQL.  
This project demonstrates creating, listing, and sending notifications with validation, caching, and pagination.  

This README explains **how to use the API** and **why each design choice was made** to show the development process.

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
- [How to Test](#how-to-test)  
- [Demo](#demo)  

---

## Overview

This API allows:

- Creating notifications  
- Listing notifications with pagination  
- Simulating sending notifications  
- Tracking status (`pending → sent`)  

> **Note:** Sending notifications is simulated for this test. Real mailer integration (e.g., Symfony Mailer) can be added for production.

The goal is **to demonstrate clean architecture, validation, caching, and RESTful API design**, not a production-ready email system.

---

## Requirements

```
- PHP 8.3+
- Symfony CLI
- MySQL 8+
- Composer
```

> **Rationale:**  
PHP 8.3 ensures modern language features and security improvements.  
Symfony 6 provides a mature ecosystem for API development.  
MySQL 8+ works well with Doctrine ORM and handles relational data reliably.

---

## Project Setup

1. Install Dependencies:

```
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

```
symfony serve
```

> **Rationale:**  
Symfony CLI simplifies running and managing the dev server.  
`.env` configuration keeps credentials separate from code.

---

## Database Design

**Notification Entity**
| Field           | Type       | Notes & Rationale                                      |
|-----------------|-----------|-------------------------------------------------------|
| id              | int       | Primary key, auto-increment                           |
| recipientEmail  | string    | Valid email (validated) to avoid invalid emails      |
| subject         | string    | Non-empty; ensures meaningful notification content   |
| body            | text      | Non-empty; allows full message content               |
| status          | string    | pending / sent; tracks workflow                       |
| createdAt       | datetime  | When created; useful for auditing and ordering       |
| sentAt          | datetime  | Nullable; set when notification is "sent"            |

> **Rationale:**  
Tracking `status` and `sentAt` allows clear workflow management.  
Validation ensures only correct input is stored.

---

## API Endpoints
| Method | Endpoint                          | Description                        | Request Body                                     |
|--------|----------------------------------|------------------------------------|------------------------------------------------|
| POST   | /api/notifications               | Create new notification             | { "recipientEmail": "...", "subject": "...", "body": "..." } |
| GET    | /api/notifications?page=1&limit=10 | List notifications (paginated & cached) | N/A                                         |
| POST   | /api/notifications/{id}/send     | Simulate sending notification       | N/A                                             |

### Pagination & Cache

```
- Query params: page (default=1), limit (default=10)
- Cached for 60 seconds to reduce database load
```

> **Rationale:**  
Caching reduces DB queries for frequently accessed lists.  
Manual pagination allows scalable query handling.

---

## Twig Dashboard (Optional Frontend)

```
- GET /dashboard/
- Create, list, and send notifications via a simple web interface
- Flash messages provide success/warning feedback
```

> **Rationale:**  
The dashboard is optional and intended for demonstration purposes only.  
Provides a quick visual interface to test the API without external tools.

---

## Architecture & Decisions

1. Symfony 6 + Doctrine ORM: rapid setup, integrated database handling, mature ecosystem
2. Entity Validation: ensures recipientEmail is valid, subject and body are non-empty
3. Controller Design: RESTful endpoints for clarity and separation of concerns
4. Caching: Symfony Cache for list endpoint to improve performance and reduce DB load
5. Pagination: implemented manually using setFirstResult & setMaxResults for scalability
6. Sending Logic: simulated sending with status and sentAt; real mailer could be added later


> **Rationale:**  
These choices prioritize clarity, maintainability, and demonstrable API best practices.

---

## Workarounds & Notes

- Doctrine cache errors resolved by matching symfony/orm-pack and doctrine/orm versions with PHP 8.3
- Symfony CLI installation issues fixed by manually downloading the binary and moving it to /usr/local/bin
- Validation handling provides JSON output of all errors
- Twig dashboard is simple and optional, intended for demo/testing


---

## Future Improvements

- Implement real email sending with Symfony Mailer
- Add retry mechanism for failed sends
- Extend pagination metadata (totalItems, totalPages)
- Add filtering by status or recipient
- Add unit and functional tests

---

## How to Test

1. Create Notification:

```
curl -X POST http://127.0.0.1:8000/api/notifications \
-H "Content-Type: application/json" \
-d '{"recipientEmail":"test@example.com","subject":"Hello","body":"World"}'
```

2. List Notifications:

```
curl http://127.0.0.1:8000/api/notifications?page=1&limit=5
```

3. Send Notification:

```
curl -X POST http://127.0.0.1:8000/api/notifications/1/send
```

---

## Demo

You can watch a short demo of the Notification API and the dashboard here:

[![Notification API Demo](https://img.youtube.com/vi/QhruPrumCIc/0.jpg)](https://www.youtube.com/watch?v=QhruPrumCIc)
