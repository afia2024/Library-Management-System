<?php

require __DIR__ . '/config.php';

// =====================================================
// MEMBER MUST BE LOGGED IN
// =====================================================
if (!isset($_SESSION['member_id'])) {
    header('Location: member_login.php');
    exit;
}

$memberId = (int) $_SESSION['member_id'];
$memberName = $_SESSION['member_name'] ?? 'Member';

$success = '';
$error = '';

// =====================================================
// BORROW BOOK
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_book'])) {

    verify_csrf();

    $bookId = (int) ($_POST['book_id'] ?? 0);

    if ($bookId <= 0) {
        $error = 'Invalid book selected.';
    } else {

        try {

            $pdo->beginTransaction();

            // Get book and lock the row during transaction
            $stmt = $pdo->prepare(
                'SELECT
                    book_id,
                    title,
                    available_copies
                 FROM BOOKS
                 WHERE book_id = ?
                 FOR UPDATE'
            );

            $stmt->execute([$bookId]);

            $book = $stmt->fetch();

            if (!$book) {

                throw new Exception('Book not found.');

            }

            if ((int) $book['available_copies'] <= 0) {

                throw new Exception(
                    'Sorry, this book is currently unavailable.'
                );

            }

            // Prevent member from borrowing same book twice
            // while the first loan is still active
            $stmt = $pdo->prepare(
                'SELECT loan_id
                 FROM LOANS
                 WHERE member_id = ?
                   AND book_id = ?
                   AND returned_at IS NULL
                 LIMIT 1'
            );

            $stmt->execute([
                $memberId,
                $bookId
            ]);

            if ($stmt->fetch()) {

                throw new Exception(
                    'You already have this book checked out.'
                );

            }

            // Due date = 14 days from today
            $dueDate = date(
                'Y-m-d',
                strtotime('+14 days')
            );

            // Create loan
            $stmt = $pdo->prepare(
                'INSERT INTO LOANS
                    (member_id, book_id, due_date)
                 VALUES
                    (?, ?, ?)'
            );

            $stmt->execute([
                $memberId,
                $bookId,
                $dueDate
            ]);

            // Reduce available copies
            $stmt = $pdo->prepare(
                'UPDATE BOOKS
                 SET available_copies = available_copies - 1
                 WHERE book_id = ?'
            );

            $stmt->execute([$bookId]);

            $pdo->commit();

            $success =
                '"' . $book['title'] .
                '" was borrowed successfully! Due date: ' .
                date('M j, Y', strtotime($dueDate)) . '.';

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = $e->getMessage();
        }
    }
}


// =====================================================
// SEARCH
// =====================================================
$search = trim($_GET['search'] ?? '');

if ($search !== '') {

    $stmt = $pdo->prepare(
        'SELECT
            book_id,
            title,
            author,
            isbn,
            total_copies,
            available_copies
         FROM BOOKS
         WHERE title LIKE ?
            OR author LIKE ?
            OR isbn LIKE ?
         ORDER BY title ASC'
    );

    $searchValue = '%' . $search . '%';

    $stmt->execute([
        $searchValue,
        $searchValue,
        $searchValue
    ]);

    $books = $stmt->fetchAll();

} else {

    $books = $pdo->query(
        'SELECT
            book_id,
            title,
            author,
            isbn,
            total_copies,
            available_copies
         FROM BOOKS
         ORDER BY title ASC'
    )->fetchAll();
}


// =====================================================
// MEMBER LOAN HISTORY
// BOOKS + LOANS JOIN
// =====================================================
$stmt = $pdo->prepare(
    'SELECT
        l.loan_id,
        b.title,
        b.author,
        l.borrowed_at,
        l.due_date,
        l.returned_at
     FROM LOANS AS l
     JOIN BOOKS AS b
        ON l.book_id = b.book_id
     WHERE l.member_id = ?
     ORDER BY l.borrowed_at DESC'
);

$stmt->execute([$memberId]);

$loans = $stmt->fetchAll();


// =====================================================
// MEMBER STATISTICS
// =====================================================
$activeLoans = 0;
$returnedLoans = 0;

foreach ($loans as $loan) {

    if ($loan['returned_at'] === null) {
        $activeLoans++;
    } else {
        $returnedLoans++;
    }
}

$totalLoans = count($loans);

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Member Portal | Library Management System
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f7fb;
            color: #16263d;
        }


        /* ==========================================
           HEADER
        ========================================== */

        .topbar {

            background:
                linear-gradient(
                    135deg,
                    #123d69,
                    #075a96
                );

            color: white;

            padding: 18px 5%;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            box-shadow:
                0 3px 12px
                rgba(0,0,0,.15);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .brand-icon {
            font-size: 42px;
        }

        .brand h1 {
            margin: 0;
            font-size: 27px;
        }

        .brand p {
            margin: 3px 0 0;
            opacity: .85;
        }

        .nav {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nav a {

            color: white;
            text-decoration: none;

            padding: 10px 14px;

            border-radius: 8px;
        }

        .nav a:hover,
        .nav a.active {

            background:
                rgba(255,255,255,.16);
        }


        /* ==========================================
           PAGE LAYOUT
        ========================================== */

        .page {

            max-width: 1450px;

            margin: 28px auto;

            padding: 0 20px;

            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                300px;

            gap: 22px;
        }


        .panel {

            background: white;

            border:
                1px solid #dce5ef;

            border-radius: 14px;

            padding: 22px;

            box-shadow:
                0 5px 16px
                rgba(22,38,61,.06);
        }


        .section-title {

            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 4px;
        }

        .section-title h2 {

            margin: 0;

            font-size: 25px;
        }

        .subtitle {

            color: #63758b;

            margin:
                0 0 20px;
        }


        /* ==========================================
           MESSAGES
        ========================================== */

        .success {

            background: #e8f8ef;

            color: #17643a;

            border:
                1px solid #bce8ce;

            padding: 13px 15px;

            border-radius: 9px;

            margin-bottom: 18px;
        }

        .error {

            background: #ffecec;

            color: #8c2525;

            border:
                1px solid #f3c1c1;

            padding: 13px 15px;

            border-radius: 9px;

            margin-bottom: 18px;
        }


        /* ==========================================
           SEARCH
        ========================================== */

        .search-form {

            display: flex;

            gap: 10px;

            margin-bottom: 22px;
        }

        .search-form input {

            flex: 1;

            padding: 12px 14px;

            border:
                1px solid #bac9da;

            border-radius: 8px;

            font-size: 15px;
        }

        .search-form button {

            border: 0;

            background: #075a96;

            color: white;

            padding: 0 22px;

            border-radius: 8px;

            font-weight: bold;

            cursor: pointer;
        }

        .search-form button:hover {
            background: #064777;
        }


        /* ==========================================
           BOOK GRID
        ========================================== */

        .books-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fill,
                    minmax(205px, 1fr)
                );

            gap: 16px;
        }

        .book-card {

            border:
                1px solid #dce5ef;

            border-radius: 12px;

            padding: 17px;

            background: #fff;

            display: flex;

            flex-direction: column;

            min-height: 315px;

            transition:
                transform .15s ease,
                box-shadow .15s ease;
        }

        .book-card:hover {

            transform:
                translateY(-3px);

            box-shadow:
                0 8px 20px
                rgba(20,55,90,.10);
        }


        .book-cover {

            height: 115px;

            border-radius: 9px;

            background:
                linear-gradient(
                    135deg,
                    #17375e,
                    #0c72b8
                );

            color: white;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 50px;

            margin-bottom: 15px;
        }


        .book-card h3 {

            margin:
                0 0 8px;

            font-size: 18px;
        }

        .book-card p {

            margin: 4px 0;

            font-size: 14px;

            color: #50637a;
        }

        .book-card strong {
            color: #1b2c42;
        }


        .availability {

            margin-top: 9px;

            font-weight: bold;
        }

        .available {
            color: #15803d;
        }

        .unavailable {
            color: #b42318;
        }


        .borrow-form {

            margin-top: auto;

            padding-top: 15px;
        }

        .borrow-button {

            width: 100%;

            border: 0;

            border-radius: 8px;

            padding: 10px;

            color: white;

            background: #0968ad;

            font-weight: bold;

            cursor: pointer;
        }

        .borrow-button:hover {
            background: #07558d;
        }

        .borrow-button:disabled {

            background: #aeb9c5;

            cursor: not-allowed;
        }


        /* ==========================================
           SIDEBAR
        ========================================== */

        .sidebar {

            display: flex;

            flex-direction: column;

            gap: 18px;
        }


        .profile {

            text-align: center;
        }

        .avatar {

            width: 65px;

            height: 65px;

            margin:
                0 auto 10px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 30px;

            background: #dceeff;
        }

        .profile h3 {
            margin: 4px 0;
        }

        .profile p {

            color: #66788d;

            margin: 0;
        }


        .stats h3 {

            margin:
                0 0 15px;
        }

        .stat {

            display: flex;

            justify-content:
                space-between;

            padding:
                11px 0;

            border-bottom:
                1px solid #e8edf3;
        }

        .stat:last-child {
            border-bottom: 0;
        }

        .number {

            min-width: 32px;

            text-align: center;

            background: #dff2ff;

            color: #075a96;

            font-weight: bold;

            border-radius: 6px;

            padding: 3px 8px;
        }


        /* ==========================================
           LOANS
        ========================================== */

        .loans-section {

            margin-top: 22px;
        }


        .table-wrap {
            overflow-x: auto;
        }

        table {

            width: 100%;

            border-collapse:
                collapse;
        }

        th,
        td {

            padding:
                12px 10px;

            text-align: left;

            border-bottom:
                1px solid #e6ecf2;
        }

        th {

            background: #f3f7fb;

            color: #30465f;

            font-size: 14px;
        }

        .status {

            display:
                inline-block;

            padding:
                5px 9px;

            border-radius:
                20px;

            font-size:
                12px;

            font-weight:
                bold;
        }

        .status-active {

            background:
                #fff3cd;

            color:
                #806000;
        }

        .status-returned {

            background:
                #e5f7ed;

            color:
                #17643a;
        }


        .empty {

            padding: 25px;

            text-align: center;

            color: #6a7b8e;
        }


        /* ==========================================
           FOOTER
        ========================================== */

        footer {

            text-align: center;

            padding:
                25px 15px;

            color: #68798c;
        }


        /* ==========================================
           RESPONSIVE
        ========================================== */

        @media (
            max-width: 950px
        ) {

            .page {

                grid-template-columns:
                    1fr;
            }

            .sidebar {

                display: grid;

                grid-template-columns:
                    repeat(
                        2,
                        minmax(0,1fr)
                    );
            }
        }


        @media (
            max-width: 650px
        ) {

            .topbar {

                flex-direction:
                    column;

                align-items:
                    flex-start;
            }

            .sidebar {

                grid-template-columns:
                    1fr;
            }

            .search-form {

                flex-direction:
                    column;
            }

            .search-form button {

                padding:
                    12px;
            }

            .books-grid {

                grid-template-columns:
                    1fr;
            }
        }

    </style>

</head>


<body>


<header class="topbar">

    <div class="brand">

        <div class="brand-icon">
            📚
        </div>

        <div>

            <h1>
                Library Management System
            </h1>

            <p>
                Member Portal
            </p>

        </div>

    </div>


    <nav class="nav">

        <a href="home.php">
            🏠 Home
        </a>

        <a
            href="#books"
            class="active"
        >
            📖 Books
        </a>

        <a href="#loans">
            📋 My Loans
        </a>

        <a href="member_logout.php">
            🚪 Logout
        </a>

    </nav>

</header>



<div class="page">


    <!-- ==========================================
         MAIN CONTENT
    =========================================== -->

    <main>


        <section
            class="panel"
            id="books"
        >

            <div class="section-title">

                <span style="font-size:27px">
                    📚
                </span>

                <h2>
                    Browse Books
                </h2>

            </div>

            <p class="subtitle">
                Search and borrow books from the library collection.
            </p>


            <?php if ($success): ?>

                <div class="success">
                    <?= h($success) ?>
                </div>

            <?php endif; ?>


            <?php if ($error): ?>

                <div class="error">
                    <?= h($error) ?>
                </div>

            <?php endif; ?>


            <!-- SEARCH -->

            <form
                method="get"
                class="search-form"
            >

                <input
                    type="text"
                    name="search"
                    placeholder="Search by title, author, or ISBN..."
                    value="<?= h($search) ?>"
                >

                <button type="submit">
                    🔍 Search
                </button>

            </form>


            <!-- BOOKS -->

            <?php if (!$books): ?>

                <div class="empty">

                    No books were found.

                </div>

            <?php else: ?>


                <div class="books-grid">


                    <?php foreach ($books as $book): ?>


                        <article class="book-card">


                            <div class="book-cover">
                                📖
                            </div>


                            <h3>
                                <?= h($book['title']) ?>
                            </h3>


                            <p>

                                <strong>
                                    Author:
                                </strong>

                                <?= h($book['author']) ?>

                            </p>


                            <p>

                                <strong>
                                    ISBN:
                                </strong>

                                <?= h(
                                    $book['isbn']
                                    ?: 'Not listed'
                                ) ?>

                            </p>


                            <?php
                                $available =
                                    (int)
                                    $book[
                                        'available_copies'
                                    ];

                                $total =
                                    (int)
                                    $book[
                                        'total_copies'
                                    ];
                            ?>


                            <p
                                class="
                                    availability
                                    <?= $available > 0
                                        ? 'available'
                                        : 'unavailable'
                                    ?>
                                "
                            >

                                <?php if ($available > 0): ?>

                                    Available:
                                    <?= $available ?>
                                    of
                                    <?= $total ?>

                                <?php else: ?>

                                    Currently unavailable

                                <?php endif; ?>

                            </p>


                            <form
                                method="post"
                                class="borrow-form"
                            >

                                <input
                                    type="hidden"
                                    name="csrf"
                                    value="<?= h(csrf_token()) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="book_id"
                                    value="<?= (int)$book['book_id'] ?>"
                                >

                                <button
                                    type="submit"
                                    name="borrow_book"
                                    class="borrow-button"
                                    <?= $available <= 0
                                        ? 'disabled'
                                        : ''
                                    ?>
                                >

                                    <?php if ($available > 0): ?>

                                        📖 Borrow Book

                                    <?php else: ?>

                                        Unavailable

                                    <?php endif; ?>

                                </button>

                            </form>


                        </article>


                    <?php endforeach; ?>


                </div>


            <?php endif; ?>


        </section>



        <!-- ==========================================
             MY LOANS
        =========================================== -->

        <section
            class="panel loans-section"
            id="loans"
        >

            <div class="section-title">

                <span style="font-size:25px">
                    📋
                </span>

                <h2>
                    My Borrowed Books
                </h2>

            </div>

            <p class="subtitle">
                Your current and previous library loans.
            </p>


            <?php if (!$loans): ?>

                <div class="empty">

                    You haven't borrowed any books yet.

                </div>


            <?php else: ?>


                <div class="table-wrap">


                    <table>


                        <thead>

                            <tr>

                                <th>
                                    Book
                                </th>

                                <th>
                                    Author
                                </th>

                                <th>
                                    Borrowed
                                </th>

                                <th>
                                    Due Date
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($loans as $loan): ?>


                            <tr>

                                <td>
                                    <?= h($loan['title']) ?>
                                </td>

                                <td>
                                    <?= h($loan['author']) ?>
                                </td>

                                <td>

                                    <?= date(
                                        'M j, Y',
                                        strtotime(
                                            $loan[
                                                'borrowed_at'
                                            ]
                                        )
                                    ) ?>

                                </td>

                                <td>

                                    <?= date(
                                        'M j, Y',
                                        strtotime(
                                            $loan[
                                                'due_date'
                                            ]
                                        )
                                    ) ?>

                                </td>

                                <td>

                                    <?php
                                    if (
                                        $loan['returned_at']
                                        === null
                                    ):
                                    ?>

                                        <span
                                            class="
                                                status
                                                status-active
                                            "
                                        >
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="
                                                status
                                                status-returned
                                            "
                                        >
                                            Returned
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>


                        <?php endforeach; ?>


                        </tbody>


                    </table>


                </div>


            <?php endif; ?>


        </section>


    </main>



    <!-- ==========================================
         SIDEBAR
    =========================================== -->

    <aside class="sidebar">


        <section class="panel profile">

            <div class="avatar">
                👤
            </div>

            <h3>
                Welcome,
                <?= h($memberName) ?>
            </h3>

            <p>
                Library Member
            </p>

        </section>



        <section class="panel stats">

            <h3>
                📚 My Loans
            </h3>


            <div class="stat">

                <span>
                    Total Borrowed
                </span>

                <span class="number">
                    <?= $totalLoans ?>
                </span>

            </div>


            <div class="stat">

                <span>
                    Active Loans
                </span>

                <span class="number">
                    <?= $activeLoans ?>
                </span>

            </div>


            <div class="stat">

                <span>
                    Returned Books
                </span>

                <span class="number">
                    <?= $returnedLoans ?>
                </span>

            </div>


        </section>



        <section class="panel stats">

            <h3>
                ⚡ Quick Actions
            </h3>


            <div class="stat">

                <a
                    href="#books"
                    style="
                        text-decoration:none;
                        color:#075a96;
                        font-weight:bold;
                    "
                >
                    📖 Browse Books
                </a>

            </div>


            <div class="stat">

                <a
                    href="#loans"
                    style="
                        text-decoration:none;
                        color:#075a96;
                        font-weight:bold;
                    "
                >
                    📋 View My Loans
                </a>

            </div>


            <div class="stat">

                <a
                    href="home.php"
                    style="
                        text-decoration:none;
                        color:#075a96;
                        font-weight:bold;
                    "
                >
                    🏠 Main Page
                </a>

            </div>


        </section>


    </aside>


</div>



<footer>

    CIS 344 Group Project
    ·
    Library Management System
    ·
    Lehman College

</footer>


</body>
</html>