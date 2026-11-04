# CIS 344 Library Management System

## Group Members

- Afia Boateng
- AbdulMateen Mudasiru
- Ashraf Abubakar

## Project Overview

The Library Management System is a CIS 344 group project developed using PHP, MySQL, HTML, CSS, and JavaScript.

The application allows library staff to manage members, books, borrowing transactions, and returns. Library members can create accounts, log in, view available books, and review their borrowing information.

## Technologies Used

- PHP
- MySQL / MariaDB
- MySQL Workbench
- HTML
- CSS
- JavaScript
- XAMPP
- PDO

## Main Database Tables

### MEMBERS
Stores registered library member information.

### BOOKS
Stores book information and tracks total and available copies.

### LOANS
Stores borrowing and return transactions and connects MEMBERS and BOOKS using foreign keys.

## Database Relationships

- MEMBERS → LOANS: One-to-Many
- BOOKS → LOANS: One-to-Many

The LOANS table connects members with the books they borrow.

## Main Features

- Member account registration
- Member login
- Administrator/staff login
- Add library members
- Add books
- View book inventory
- Borrow books
- Return books
- Track available book copies
- View active loans
- View member borrowing information
- SQL JOIN queries connecting MEMBERS, BOOKS, and LOANS
- Input validation
- PDO prepared statements
- CSRF protection
- Password hashing

## Project Structure

```text
Library_Management_System/
│
├── assets/
│   ├── style.css
│   └── script.js
│
├── sql/
│   └── Library_Management_System.sql
│
├── config.php
├── home.php
├── index.php
├── login.php
├── logout.php
├── member.php
├── member_login.php
├── member_logout.php
├── register.php
├── forgot_password.php
└── README.md
```

## Local Setup Using XAMPP on Windows

1. Install and open XAMPP.

2. Start Apache and MySQL.

3. Place the `Library_Management_System` project folder inside:

```text
C:\xampp\htdocs\
```

4. Open MySQL Workbench or phpMyAdmin.

5. Run:

```text
sql/Library_Management_System.sql
```

This creates the `library_management_system` database and the MEMBERS, BOOKS, and LOANS tables.

6. Check `config.php` and make sure the database settings match the local MySQL configuration.

For the development environment used by this project, MySQL/MariaDB runs locally on port `3307`.

7. Open the application in a browser through the local XAMPP server.

Start with:

```text
/Library_Management_System/home.php
```

## Testing the Application

After installation:

1. Open the main Library Management System homepage.
2. Create a member account.
3. Log in as a library member.
4. Confirm that available books can be viewed.
5. Open the administrator login.
6. Log in to the staff dashboard.
7. Add a member.
8. Add a book.
9. Borrow a book for a member.
10. Confirm that the available number of copies decreases.
11. View the active loan.
12. Return the book.
13. Confirm that the available number of copies increases again.

## SQL JOIN Example

The application uses SQL JOIN queries to combine information from MEMBERS, LOANS, and BOOKS.

```sql
SELECT
    l.loan_id,
    m.full_name AS member_name,
    m.email,
    b.title AS book_title,
    b.author,
    l.borrowed_at,
    l.due_date
FROM LOANS AS l
JOIN MEMBERS AS m
    ON l.member_id = m.member_id
JOIN BOOKS AS b
    ON l.book_id = b.book_id
WHERE l.returned_at IS NULL
ORDER BY l.due_date ASC;
```

This query retrieves active loans while displaying information about both the member and the borrowed book.

## Security Features

The project includes:

- PDO prepared statements
- Server-side input validation
- Output escaping
- CSRF protection
- Password hashing
- Session-based authentication

## Group Contributions

### Afia Boateng
- Database structure and design
- MEMBERS, BOOKS, and LOANS tables
- Primary and foreign keys
- SQL JOIN queries
- Database relationship testing
- Database documentation

### AbdulMateen Mudasiru
- PHP backend development
- Member and book forms
- Borrowing and return functionality
- PHP/MySQL integration
- Validation and backend testing

### Ashraf Abubakar
- HTML/CSS/JavaScript frontend
- Application navigation and interface design
- Books, members, loans, borrowing, and return interfaces
- User guide and screenshots
- Demo preparation
- GitHub/README organization

## Academic Project Notice

This application was developed as a student project for CIS 344 at Lehman College, CUNY.

It is intended for educational demonstration and is not an official Lehman College library system or a production library service.