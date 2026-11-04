<?php

require __DIR__ . '/config.php';

/*
=====================================================
ADMIN / LIBRARY STAFF AREA ONLY
=====================================================
*/

if (
    !isset($_SESSION['admin_logged_in']) ||
    $_SESSION['admin_logged_in'] !== true
) {
    header('Location: login.php');
    exit;
}

$errors = [];


/*
=====================================================
HANDLE FORM SUBMISSIONS
=====================================================
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $action = (string)($_POST['action'] ?? '');

    try {

        /*
        =====================================================
        ADD MEMBER
        =====================================================
        */

        if ($action === 'add_member') {

            $name = trim((string)($_POST['full_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));

            if ($name === '' || mb_strlen($name) > 120) {
                throw new DomainException(
                    'Enter a member name (up to 120 characters).'
                );
            }

            if (
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                strlen($email) > 190
            ) {
                throw new DomainException(
                    'Enter a valid email address.'
                );
            }

            if (strlen($phone) > 30) {
                throw new DomainException(
                    'Phone number is too long.'
                );
            }

            $stmt = $pdo->prepare(
                'INSERT INTO MEMBERS
                (full_name, email, phone)
                VALUES (?, ?, ?)'
            );

            $stmt->execute([
                $name,
                $email,
                $phone ?: null
            ]);

            redirect_with('Member added successfully.');
        }


        /*
        =====================================================
        ADD BOOK
        =====================================================
        */

        elseif ($action === 'add_book') {

            $title = trim((string)($_POST['title'] ?? ''));
            $author = trim((string)($_POST['author'] ?? ''));
            $isbn = trim((string)($_POST['isbn'] ?? ''));

            $copies = filter_var(
                $_POST['total_copies'] ?? null,
                FILTER_VALIDATE_INT,
                [
                    'options' => [
                        'min_range' => 1,
                        'max_range' => 100000
                    ]
                ]
            );

            if ($title === '' || mb_strlen($title) > 200) {
                throw new DomainException(
                    'Enter a book title (up to 200 characters).'
                );
            }

            if ($author === '' || mb_strlen($author) > 150) {
                throw new DomainException(
                    'Enter an author (up to 150 characters).'
                );
            }

            if (strlen($isbn) > 20) {
                throw new DomainException(
                    'ISBN is too long.'
                );
            }

            if ($copies === false) {
                throw new DomainException(
                    'Copies must be a positive whole number.'
                );
            }

            $stmt = $pdo->prepare(
                'INSERT INTO BOOKS
                (title, author, isbn, total_copies, available_copies)
                VALUES (?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $title,
                $author,
                $isbn ?: null,
                $copies,
                $copies
            ]);

            redirect_with('Book added successfully.');
        }


        /*
        =====================================================
        BORROW BOOK
        =====================================================
        */

        elseif ($action === 'borrow') {

            $member = filter_var(
                $_POST['member_id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            $book = filter_var(
                $_POST['book_id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            $due = (string)($_POST['due_date'] ?? '');

            $date = DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $due
            );

            if (
                !$member ||
                !$book ||
                !$date ||
                $date->format('Y-m-d') !== $due ||
                $due < date('Y-m-d')
            ) {
                throw new DomainException(
                    'Choose a member, a book, and a valid due date.'
                );
            }

            $pdo->beginTransaction();

            $check = $pdo->prepare(
                'SELECT available_copies
                 FROM BOOKS
                 WHERE book_id = ?
                 FOR UPDATE'
            );

            $check->execute([$book]);

            $record = $check->fetch();

            if (
                !$record ||
                (int)$record['available_copies'] < 1
            ) {
                throw new DomainException(
                    'That book is unavailable.'
                );
            }

            $exists = $pdo->prepare(
                'SELECT member_id
                 FROM MEMBERS
                 WHERE member_id = ?'
            );

            $exists->execute([$member]);

            if (!$exists->fetch()) {
                throw new DomainException(
                    'Member not found.'
                );
            }

            $stmt = $pdo->prepare(
                'INSERT INTO LOANS
                (member_id, book_id, due_date)
                VALUES (?, ?, ?)'
            );

            $stmt->execute([
                $member,
                $book,
                $due
            ]);

            $stmt = $pdo->prepare(
                'UPDATE BOOKS
                 SET available_copies = available_copies - 1
                 WHERE book_id = ?'
            );

            $stmt->execute([$book]);

            $pdo->commit();

            redirect_with('Book borrowed successfully.');
        }


        /*
        =====================================================
        RETURN BOOK
        =====================================================
        */

        elseif ($action === 'return') {

            $loan = filter_var(
                $_POST['loan_id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if (!$loan) {
                throw new DomainException(
                    'Invalid loan.'
                );
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'SELECT book_id, returned_at
                 FROM LOANS
                 WHERE loan_id = ?
                 FOR UPDATE'
            );

            $stmt->execute([$loan]);

            $record = $stmt->fetch();

            if (
                !$record ||
                $record['returned_at'] !== null
            ) {
                throw new DomainException(
                    'Loan not found or already returned.'
                );
            }

            $stmt = $pdo->prepare(
                'UPDATE LOANS
                 SET returned_at = NOW()
                 WHERE loan_id = ?'
            );

            $stmt->execute([$loan]);

            $stmt = $pdo->prepare(
                'UPDATE BOOKS
                 SET available_copies = available_copies + 1
                 WHERE book_id = ?'
            );

            $stmt->execute([
                $record['book_id']
            ]);

            $pdo->commit();

            redirect_with('Book returned successfully.');
        }

        else {

            throw new DomainException(
                'Unknown action.'
            );
        }

    }

    catch (DomainException $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $errors[] = $e->getMessage();
    }

    catch (PDOException $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $errors[] =
            $e->getCode() === '23000'
            ? 'Duplicate email/ISBN or invalid record.'
            : 'Database operation failed. Please check the schema.';
    }
}


/*
=====================================================
GET REGISTERED MEMBERS
=====================================================
*/

$members = $pdo->query(
    'SELECT
        member_id,
        full_name,
        email,
        phone,
        created_at
     FROM MEMBERS
     ORDER BY member_id DESC'
)->fetchAll();


/*
=====================================================
GET BOOKS
=====================================================
*/

$books = $pdo->query(
    'SELECT
        book_id,
        title,
        author,
        available_copies,
        total_copies
     FROM BOOKS
     ORDER BY title'
)->fetchAll();


/*
=====================================================
GET ACTIVE LOANS

IMPORTANT CIS 344 REQUIREMENT:
THIS QUERY USES A 3-TABLE JOIN

LOANS + MEMBERS + BOOKS
=====================================================
*/

$loans = $pdo->query(
    'SELECT
        l.loan_id,
        m.full_name,
        b.title,
        l.borrowed_at,
        l.due_date
     FROM LOANS l
     JOIN MEMBERS m
        ON l.member_id = m.member_id
     JOIN BOOKS b
        ON l.book_id = b.book_id
     WHERE l.returned_at IS NULL
     ORDER BY l.borrowed_at DESC'
)->fetchAll();


$flash = $_SESSION['flash'] ?? null;

unset($_SESSION['flash']);

?>

<!doctype html>

<html lang="en">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>
    Staff Portal - Lehman Library
</title>

<link
    rel="stylesheet"
    href="assets/style.css"
>

<style>

/*
=====================================================
STAFF NAVIGATION
=====================================================
*/

.staff-nav {
    background: #173b5e;
    color: white;

    padding: 15px 5%;

    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 20px;
}

.staff-brand {
    font-size: 20px;
    font-weight: bold;
}

.staff-links {
    display: flex;
    align-items: center;
    gap: 12px;
}

.staff-links a {
    text-decoration: none;
    font-weight: bold;

    padding: 9px 15px;

    border-radius: 6px;
}

.home-link {
    color: white;
    border: 1px solid white;
}

.logout-link {
    background: white;
    color: #173b5e;
}

.staff-notice {
    max-width: 1100px;

    margin: 20px auto;

    padding: 15px 20px;

    background: #fff4d6;

    border-left: 5px solid #d49b00;

    border-radius: 6px;

    color: #654b00;
}

@media (max-width: 700px) {

    .staff-nav {
        flex-direction: column;
        text-align: center;
    }

    .staff-links {
        flex-wrap: wrap;
        justify-content: center;
    }
}

</style>

</head>


<body>


<!-- =====================================================
LIBRARY STAFF NAVIGATION
===================================================== -->

<nav class="staff-nav">

    <div class="staff-brand">
        📚 Lehman Library — Staff Portal
    </div>


    <div class="staff-links">

        <a
            href="home.php"
            class="home-link"
        >
            🏠 Main Page
        </a>


        <a
            href="logout.php"
            class="logout-link"
        >
            🚪 Logout
        </a>

    </div>

</nav>


<header>

    <h1>
        Library Administration
    </h1>

    <p>
        Authorized Library Employees Only
    </p>

</header>


<div class="staff-notice">

    🔐 <strong>Staff Portal:</strong>

    This area is restricted to authorized library
    employees and administrators.

</div>


<main>


<?php if ($flash): ?>

    <div class="success">

        <?= h($flash) ?>

    </div>

<?php endif; ?>


<?php foreach ($errors as $error): ?>

    <div class="error">

        <?= h($error) ?>

    </div>

<?php endforeach; ?>



<div class="grid">


<!-- =====================================================
ADD MEMBER
===================================================== -->

<section>

    <h2>
        👤 Add Member
    </h2>


    <form method="post">

        <input
            type="hidden"
            name="csrf"
            value="<?= h(csrf_token()) ?>"
        >

        <input
            type="hidden"
            name="action"
            value="add_member"
        >


        <label>

            Full Name

            <input
                name="full_name"
                maxlength="120"
                required
            >

        </label>


        <label>

            Email

            <input
                type="email"
                name="email"
                maxlength="190"
                required
            >

        </label>


        <label>

            Phone

            <input
                name="phone"
                maxlength="30"
            >

        </label>


        <button type="submit">
            Add Member
        </button>

    </form>

</section>



<!-- =====================================================
ADD BOOK
===================================================== -->

<section>

    <h2>
        📚 Add Book
    </h2>


    <form method="post">

        <input
            type="hidden"
            name="csrf"
            value="<?= h(csrf_token()) ?>"
        >

        <input
            type="hidden"
            name="action"
            value="add_book"
        >


        <label>

            Title

            <input
                name="title"
                maxlength="200"
                required
            >

        </label>


        <label>

            Author

            <input
                name="author"
                maxlength="150"
                required
            >

        </label>


        <label>

            ISBN (optional)

            <input
                name="isbn"
                maxlength="20"
            >

        </label>


        <label>

            Number of Copies

            <input
                type="number"
                name="total_copies"
                min="1"
                value="1"
                required
            >

        </label>


        <button type="submit">
            Add Book
        </button>

    </form>

</section>



<!-- =====================================================
BORROW BOOK
===================================================== -->

<section>

    <h2>
        📖 Borrow Book
    </h2>


    <form method="post">

        <input
            type="hidden"
            name="csrf"
            value="<?= h(csrf_token()) ?>"
        >

        <input
            type="hidden"
            name="action"
            value="borrow"
        >


        <label>

            Member

            <select
                name="member_id"
                required
            >

                <option value="">
                    Select Member
                </option>


                <?php foreach ($members as $m): ?>

                    <option
                        value="<?= (int)$m['member_id'] ?>"
                    >

                        <?= h($m['full_name']) ?>

                        (<?= h($m['email']) ?>)

                    </option>

                <?php endforeach; ?>

            </select>

        </label>



        <label>

            Available Book

            <select
                name="book_id"
                required
            >

                <option value="">
                    Select Book
                </option>


                <?php foreach ($books as $b): ?>

                    <?php
                    if ((int)$b['available_copies'] > 0):
                    ?>

                        <option
                            value="<?= (int)$b['book_id'] ?>"
                        >

                            <?= h($b['title']) ?>

                            (<?= (int)$b['available_copies'] ?> available)

                        </option>

                    <?php endif; ?>

                <?php endforeach; ?>

            </select>

        </label>



        <label>

            Due Date

            <input
                type="date"
                name="due_date"
                min="<?= date('Y-m-d') ?>"
                required
            >

        </label>


        <button type="submit">
            Borrow Book
        </button>

    </form>

</section>



<!-- =====================================================
CURRENT INVENTORY
===================================================== -->

<section>

    <h2>
        📦 Current Inventory
    </h2>


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
                        Available / Total
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php foreach ($books as $b): ?>

                <tr>

                    <td>
                        <?= h($b['title']) ?>
                    </td>

                    <td>
                        <?= h($b['author']) ?>
                    </td>

                    <td>

                        <?= (int)$b['available_copies'] ?>

                        /

                        <?= (int)$b['total_copies'] ?>

                    </td>

                </tr>

            <?php endforeach; ?>


            <?php if (!$books): ?>

                <tr>

                    <td colspan="3">
                        No books yet.
                    </td>

                </tr>

            <?php endif; ?>


            </tbody>

        </table>

    </div>

</section>


</div>



<!-- =====================================================
REGISTERED MEMBERS
===================================================== -->

<section>

    <h2>
        👥 Registered Members
    </h2>


    <div class="table-wrap">

        <table>

            <thead>

                <tr>

                    <th>
                        Member ID
                    </th>

                    <th>
                        Full Name
                    </th>

                    <th>
                        Email
                    </th>

                    <th>
                        Phone
                    </th>

                    <th>
                        Date Joined
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php foreach ($members as $m): ?>

                <tr>

                    <td>
                        <?= (int)$m['member_id'] ?>
                    </td>

                    <td>
                        <?= h($m['full_name']) ?>
                    </td>

                    <td>
                        <?= h($m['email']) ?>
                    </td>

                    <td>
                        <?= h($m['phone'] ?? 'Not provided') ?>
                    </td>

                    <td>
                        <?= h($m['created_at']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>


            <?php if (!$members): ?>

                <tr>

                    <td colspan="5">
                        No registered members yet.
                    </td>

                </tr>

            <?php endif; ?>


            </tbody>

        </table>

    </div>

</section>



<!-- =====================================================
ACTIVE LOANS
===================================================== -->

<section>

    <h2>
        🔄 Active Loans — Return a Book
    </h2>


    <div class="table-wrap">

        <table>

            <thead>

                <tr>

                    <th>
                        Member
                    </th>

                    <th>
                        Book
                    </th>

                    <th>
                        Borrowed
                    </th>

                    <th>
                        Due
                    </th>

                    <th>
                        Action
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php foreach ($loans as $l): ?>

                <tr>

                    <td>
                        <?= h($l['full_name']) ?>
                    </td>

                    <td>
                        <?= h($l['title']) ?>
                    </td>

                    <td>
                        <?= h($l['borrowed_at']) ?>
                    </td>

                    <td>
                        <?= h($l['due_date']) ?>
                    </td>

                    <td>

                        <form
                            method="post"
                            class="inline"
                        >

                            <input
                                type="hidden"
                                name="csrf"
                                value="<?= h(csrf_token()) ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="return"
                            >

                            <input
                                type="hidden"
                                name="loan_id"
                                value="<?= (int)$l['loan_id'] ?>"
                            >


                            <button type="submit">
                                Return
                            </button>

                        </form>

                    </td>

                </tr>

            <?php endforeach; ?>


            <?php if (!$loans): ?>

                <tr>

                    <td colspan="5">
                        No active loans.
                    </td>

                </tr>

            <?php endif; ?>


            </tbody>

        </table>

    </div>

</section>


</main>



<footer>

    Lehman Library Management System

    · CIS 344 Student Project

    · PHP & MySQL

</footer>


</body>

</html>