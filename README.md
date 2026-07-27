# Hostel Room Requirement & Allocation Tracker Website 🏨

A complete, modern college project built with **HTML5, CSS3, JavaScript, PHP, and MySQL**. Fits XAMPP / Apache server environments.

---

## 🌟 Key Features

1. **Student Room Requirement Portal**:
   - Browse available rooms (Single, Double, Triple Bed, AC / Non-AC).
   - Submit room allocation application form with student details and room preferences.
   - Live Application Status Tracker with visual step-by-step progress bar (`REQ-2026-XXXX`).
   - Maintenance Ticket & Complaint Registration (Electrical, Plumbing, Wi-Fi, Furniture).

2. **Warden Admin Control Panel (`admin_dashboard.php`)**:
   - Manage student applications (Approve / Reject / Allocate room numbers).
   - Inventory Management (Add new rooms, specify rent, total beds, AC facility).
   - Maintenance complaint resolution log.

3. **Database Schema (`database.sql`)**:
   - Tables: `users`, `rooms`, `room_requests`, `complaints`.
   - Sample initial data pre-configured for instant demo!

---

## 🚀 How to Run on XAMPP (Step-by-Step)

### Step 1: Open XAMPP Control Panel
- Open **XAMPP Control Panel** on your computer.
- Click **Start** for **Apache** and **MySQL**.

### Step 2: Copy Project Files to `htdocs`
- Copy the entire `hostel_room_tracker` project folder to your XAMPP installation directory:
  `C:\xampp\htdocs\hostel_room_tracker`

### Step 3: Setup MySQL Database in phpMyAdmin
1. Open your web browser and go to: `http://localhost/phpmyadmin`
2. Click on **Databases** tab at the top.
3. Create a new database named: `hostel_db`
4. Click on `hostel_db`, then go to the **Import** tab.
5. Click **Choose File** and select `database.sql` from your project folder.
6. Click **Import** (or **Go**).

### Step 4: Run the Website in Browser
Open your browser and navigate to:
👉 **`http://localhost/hostel_room_tracker/index.php`**

---

## 📁 File Architecture

```
hostel_room_tracker/
├── config.php              # PDO Database Connection & Utility Functions
├── database.sql            # MySQL Database Tables & Sample Data
├── index.php               # Landing Page & Live Statistics Overview
├── rooms.php               # Room Directory with Live Search & Filters
├── request_room.php        # Student Room Requirement Request Form
├── track_status.php        # Live Status Tracker (with Stepper Bar)
├── complaints.php          # Student Maintenance Ticket Registration
├── admin_dashboard.php     # Warden Admin Management Dashboard
├── assets/
│   ├── css/
│   │   └── style.css       # Ultra-Modern Glassmorphism Design Stylesheet
│   └── js/
│       └── main.js        # Dynamic Search & Interactive UI Handlers
└── README.md               # Project Setup & Documentation
```

---

## 💡 Presentation Tips for College Viva

1. **Tech Stack**: State that the project uses standard **HTML5/CSS3/JS** for front-end, **PHP 8 (PDO)** for backend logic, and **MySQL** for database management.
2. **Security & Best Practices**: Mention that user inputs are sanitized against XSS attacks using `htmlspecialchars()`, and PDO prepared statements prevent SQL injection.
3. **Responsive UI**: Highlight the responsive glassmorphism card design, step-by-step progress stepper for status tracking, and live filtering.
