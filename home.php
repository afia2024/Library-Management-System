<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Lehman Library Management System</title>

<style>

/* =========================================
   GENERAL
========================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f7fa;
    color: #1f2937;
}


/* =========================================
   NAVIGATION
========================================= */

nav {
    background: white;
    padding: 18px 8%;

    display: flex;
    justify-content: space-between;
    align-items: center;

    box-shadow: 0 2px 10px rgba(0,0,0,0.08);

    position: sticky;
    top: 0;
    z-index: 1000;
}

.logo {
    font-size: 24px;
    font-weight: bold;
    color: #245f91;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 25px;
}

.nav-links a {
    text-decoration: none;
    color: #444;
    font-weight: bold;
}

.nav-links a:hover {
    color: #3578b8;
}

.staff-nav {
    padding: 9px 15px;
    border: 1px solid #3578b8;
    border-radius: 6px;
}


/* =========================================
   HERO
========================================= */

.hero {
    min-height: 590px;

    background:
        linear-gradient(
            135deg,
            #173b5e,
            #3578b8
        );

    display: flex;
    justify-content: center;
    align-items: center;

    text-align: center;

    padding: 60px 20px;

    color: white;
}

.hero-content {
    max-width: 850px;
}

.hero-icon {
    font-size: 75px;
    margin-bottom: 20px;
}

.hero h1 {
    font-size: 50px;
    margin-bottom: 12px;
}

.hero h2 {
    font-size: 22px;
    font-weight: normal;

    color: #dceaf5;

    margin-bottom: 25px;
}

.hero p {
    max-width: 700px;

    margin: 0 auto 35px;

    font-size: 18px;
    line-height: 1.7;

    color: #e7eef5;
}


/* =========================================
   HERO BUTTONS
========================================= */

.hero-buttons {
    display: flex;
    justify-content: center;

    gap: 15px;

    flex-wrap: wrap;
}

.btn {
    display: inline-block;

    padding: 14px 28px;

    border-radius: 7px;

    text-decoration: none;

    font-weight: bold;

    transition: 0.2s;
}

.member-btn {
    background: white;
    color: #245f91;
}

.member-btn:hover {
    background: #eaf1f7;
    transform: translateY(-2px);
}

.create-btn {
    border: 2px solid white;
    color: white;
}

.create-btn:hover {
    background: white;
    color: #245f91;
}


/* =========================================
   ABOUT
========================================= */

.about {
    background: white;

    padding: 75px 8%;

    text-align: center;
}

.about h2 {
    color: #245f91;

    font-size: 34px;

    margin-bottom: 18px;
}

.about p {
    max-width: 750px;

    margin: auto;

    color: #666;

    line-height: 1.8;

    font-size: 17px;
}


/* =========================================
   FEATURES
========================================= */

.features-section {
    padding: 75px 8%;

    background: #f5f7fa;
}

.features-title {
    text-align: center;

    margin-bottom: 45px;
}

.features-title h2 {
    color: #245f91;

    font-size: 32px;

    margin-bottom: 10px;
}

.features-title p {
    color: #777;
}

.features {
    max-width: 1100px;

    margin: auto;

    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 25px;
}

.feature-card {
    background: white;

    padding: 35px 25px;

    text-align: center;

    border-radius: 12px;

    box-shadow: 0 3px 15px rgba(0,0,0,0.07);

    transition: 0.2s;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-icon {
    font-size: 42px;

    margin-bottom: 15px;
}

.feature-card h3 {
    color: #245f91;

    margin-bottom: 12px;
}

.feature-card p {
    color: #666;

    line-height: 1.6;
}


/* =========================================
   ACCESS SECTION
========================================= */

.access-section {
    background: white;

    padding: 80px 20px;

    text-align: center;
}

.access-section h2 {
    color: #245f91;

    font-size: 34px;

    margin-bottom: 10px;
}

.access-description {
    color: #666;

    margin-bottom: 45px;
}

.access-options {
    max-width: 900px;

    margin: auto;

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 30px;
}


/* =========================================
   MEMBER CARD
========================================= */

.access-card {
    background: #fafafa;

    padding: 40px 30px;

    border: 1px solid #ddd;

    border-radius: 12px;

    box-shadow: 0 3px 12px rgba(0,0,0,0.05);
}

.access-icon {
    font-size: 50px;

    margin-bottom: 15px;
}

.access-card h3 {
    color: #245f91;

    font-size: 24px;

    margin-bottom: 12px;
}

.access-card p {
    color: #666;

    line-height: 1.6;

    margin-bottom: 25px;
}

.member-login-button {
    display: block;

    width: 100%;

    padding: 13px;

    background: #3578b8;

    color: white;

    text-decoration: none;

    border-radius: 6px;

    font-weight: bold;

    margin-bottom: 12px;
}

.member-login-button:hover {
    background: #28679f;
}

.create-account-button {
    display: block;

    width: 100%;

    padding: 12px;

    color: #3578b8;

    border: 1px solid #3578b8;

    text-decoration: none;

    border-radius: 6px;

    font-weight: bold;
}

.create-account-button:hover {
    background: #eef5fa;
}


/* =========================================
   ADMIN / STAFF CARD
========================================= */

.staff-card {
    background: #f3f5f6;

    border: 2px solid #263238;
}

.staff-card h3 {
    color: #263238;
}

.staff-label {
    display: inline-block;

    background: #263238;

    color: white;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

    margin-bottom: 15px;
}

.admin-login-button {
    display: block;

    width: 100%;

    padding: 13px;

    background: #263238;

    color: white;

    text-decoration: none;

    border-radius: 6px;

    font-weight: bold;
}

.admin-login-button:hover {
    background: #111;
}

.staff-warning {
    margin-top: 18px !important;

    margin-bottom: 0 !important;

    font-size: 13px;

    color: #777 !important;
}


/* =========================================
   PROJECT NOTICE
========================================= */

.project-notice {
    background: #eaf1f7;

    padding: 35px 20px;

    text-align: center;
}

.project-notice p {
    color: #555;

    line-height: 1.6;
}


/* =========================================
   FOOTER
========================================= */

footer {
    background: #172b3a;

    color: #d7e0e7;

    text-align: center;

    padding: 30px 20px;
}

footer h3 {
    margin-bottom: 8px;
}

footer p {
    margin-top: 5px;

    font-size: 14px;
}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 800px) {

    nav {
        padding: 18px 5%;
    }

    .nav-links {
        gap: 10px;
    }

    .nav-links a {
        font-size: 13px;
    }

    .hero h1 {
        font-size: 36px;
    }

    .hero h2 {
        font-size: 19px;
    }

    .features {
        grid-template-columns: 1fr;
    }

    .access-options {
        grid-template-columns: 1fr;
    }

}

</style>

</head>


<body>


<!-- =========================================
     NAVIGATION
========================================= -->

<nav>

    <div class="logo">
        📚 Lehman Library
    </div>


    <div class="nav-links">

        <a href="home.php">
            Home
        </a>

        <a href="#about">
            About
        </a>

        <a href="#access">
            Login
        </a>

        <a
            href="login.php"
            class="staff-nav"
        >
            Staff
        </a>

    </div>

</nav>



<!-- =========================================
     HERO
========================================= -->

<section class="hero">

    <div class="hero-content">


        <div class="hero-icon">
            📚
        </div>


        <h1>
            Lehman Library
        </h1>


        <h2>
            Library Management System
        </h2>


        <p>

            Welcome to our library management system.

            Members can access their library accounts,
            explore available books, and keep track of
            their borrowing activity.

        </p>


        <div class="hero-buttons">


            <a
                href="member_login.php"
                class="btn member-btn"
            >
                👤 Member Login
            </a>


            <a
                href="register.php"
                class="btn create-btn"
            >
                Create Account
            </a>


        </div>

    </div>

</section>



<!-- =========================================
     ABOUT
========================================= -->

<section
    class="about"
    id="about"
>

    <h2>
        About the Library System
    </h2>


    <p>

        The Lehman Library Management System provides
        an organized and convenient way to manage
        library activities.

        Members can create accounts, view available
        books, and monitor their borrowing history
        and due dates.

        Authorized library staff can manage books,
        members, loans, borrowing, and returns through
        a separate secure staff portal.

    </p>

</section>



<!-- =========================================
     FEATURES
========================================= -->

<section class="features-section">


    <div class="features-title">

        <h2>
            Library Services
        </h2>

        <p>
            Simple tools for managing your library experience.
        </p>

    </div>


    <div class="features">


        <div class="feature-card">

            <div class="feature-icon">
                📖
            </div>

            <h3>
                Browse Books
            </h3>

            <p>

                View books in the library and check
                how many copies are currently available.

            </p>

        </div>



        <div class="feature-card">

            <div class="feature-icon">
                👤
            </div>

            <h3>
                Member Account
            </h3>

            <p>

                Create an account and securely access
                your personal member dashboard.

            </p>

        </div>



        <div class="feature-card">

            <div class="feature-icon">
                📅
            </div>

            <h3>
                Track Loans
            </h3>

            <p>

                View borrowed books, borrowing dates,
                due dates, and return status.

            </p>

        </div>


    </div>

</section>



<!-- =========================================
     MEMBER / ADMIN LOGIN CHOICE
========================================= -->

<section
    class="access-section"
    id="access"
>


    <h2>
        Access the Library
    </h2>


    <p class="access-description">

        Choose the correct portal below.

    </p>



    <div class="access-options">


        <!-- =====================================
             MEMBER
        ====================================== -->

        <div class="access-card">


            <div class="access-icon">
                👤
            </div>


            <h3>
                Library Member
            </h3>


            <p>

                Access your member account to view
                available books and keep track of
                your personal borrowing history.

            </p>


            <a
                href="member_login.php"
                class="member-login-button"
            >
                👤 Member Login
            </a>


            <a
                href="register.php"
                class="create-account-button"
            >
                Create Member Account
            </a>


        </div>



        <!-- =====================================
             ADMIN / EMPLOYEE
        ====================================== -->

        <div class="access-card staff-card">


            <div class="access-icon">
                🔐
            </div>


            <div class="staff-label">
                EMPLOYEES ONLY
            </div>


            <h3>
                Library Staff / Admin
            </h3>


            <p>

                Authorized library employees can
                access the staff portal to manage
                members, books, loans, borrowing,
                and returns.

            </p>


            <a
                href="login.php"
                class="admin-login-button"
            >
                🔐 Admin Login
            </a>


            <p class="staff-warning">

                Authorized library employees
                and administrators only.

            </p>


        </div>


    </div>

</section>



<!-- =========================================
     STUDENT PROJECT NOTICE
========================================= -->

<section class="project-notice">

    <p>

        <strong>CIS 344 Student Project</strong>

        <br>

        Library Management System developed using
        PHP, MySQL, HTML, and CSS.

    </p>

</section>



<!-- =========================================
     FOOTER
========================================= -->

<footer>


    <h3>
        📚 Lehman Library
    </h3>


    <p>
        Library Management System
    </p>


    <p>
        CIS 344 Group Project
    </p>


</footer>


</body>

</html>