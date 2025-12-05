# 🛒 E-Shop – Modern Laravel E-Commerce Platform

<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="#"><img src="https://img.shields.io/badge/Laravel-11.x-ff2d20?style=flat&logo=laravel&logoColor=white" alt="Laravel"></a>
  <a href="#"><img src="https://img.shields.io/badge/PHP-%5E8.2-blue?style=flat&logo=php" alt="PHP"></a>
  <a href="#"><img src="https://img.shields.io/badge/Status-Production%20Ready-brightgreen?style=flat" alt="Status"></a>
  <a href="https://github.com/your-github-username/eshop/stargazers"><img src="https://img.shields.io/github/stars/your-github-username/eshop?style=social" alt="GitHub stars"></a>
</p>

---

## ✨ Overview

**E-Shop** is a modern, full-featured **Laravel e-commerce platform** with a clean UI and a production-ready architecture.

It includes:

- A beautiful, mobile-friendly storefront  
- Powerful admin dashboard for managing products, categories, and orders  
- Secure checkout with payment gateway integration  
- API-ready backend so you can plug in mobile apps or SPA frontends later  

Use it as:

- A starting point for a real online store  
- A base project for clients  
- A learning resource for building serious Laravel apps  

---

## 🚀 Features

### 🛍️ Storefront

- Responsive layout (desktop, tablet, mobile)
- Product listing with:
  - Category filtering
  - Search
  - Price & popularity sorting
- Product details page:
  - Gallery images
  - Description & specs
  - Stock status & pricing
- Shopping cart:
  - Add / remove / update quantity
  - Subtotal, shipping, and total calculation
  - Guest cart persisted via session

### 🔐 Authentication & Accounts

- User registration & login
- Email verification ready
- Password reset via email
- Profile management (name, email, address)
- Order history for each customer

### 💳 Checkout & Payments

- Shipping details & order review
- Payment gateway integration (Stripe example wired in structure)
- Order creation + status tracking (Pending, Paid, Shipped, Completed, Cancelled)
- Email notification structure for order confirmation (hooks ready)

### 🧑‍💻 Admin Panel

- Secure admin login
- Dashboard overview (orders, revenue, latest customers)
- Category management (CRUD)
- Product management:
  - Title, description, price, stock, SKU
  - Category assignment
  - Image upload
- Order management:
  - View orders by status
  - Update order status
  - See order items & customer info

### ⚙️ Tech Stack

- **Backend:** Laravel 11+ (MVC, Eloquent ORM)
- **Frontend:** Blade, TailwindCSS, Alpine.js
- **Database:** MySQL / MariaDB / PostgreSQL
- **Auth:** Laravel Breeze / built-in auth scaffolding (depending on your setup)
- **Extras:** Laravel Debugbar (optional for local), Laravel IDE Helper (optional)

---

## 🏗️ Project Structure (High Level)

```text
app/
  Http/
    Controllers/
      Front/
      Admin/
    Middleware/
  Models/
bootstrap/
config/
database/
  factories/
  migrations/
  seeders/
public/
resources/
  views/
    front/
    admin/
  css/
  js/
routes/
  web.php
  api.php
