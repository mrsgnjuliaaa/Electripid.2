<?php

session_start();

require_once '../connect.php';

if (!isset($_SESSION['recovery_email'])) {

    header('Location: login.php');

    exit;
}

$error_message = '';

$email =
$_SESSION['recovery_email'];

$query = "

SELECT *

FROM USER

WHERE email = '$email'

LIMIT 1

";

$result =
executeQuery($query);

if (!$result ||
    mysqli_num_rows($result) !== 1) {

    header('Location: login.php');

    exit;
}

$user =
mysqli_fetch_assoc($result);

/* RANDOM QUESTION */

$question_map = [

    1 => 'What is your favorite food?',

    2 => 'What is your favorite color?',

    3 => 'What city were you born in?',

    4 => 'What is your first pet\'s name?',

    5 => 'What is your childhood nickname?'

];

if (!isset($_SESSION['recovery_question'])) {

    $_SESSION['recovery_question'] =
    rand(1, 5);
}

$question_number =
$_SESSION['recovery_question'];

$question =
$question_map[$question_number];

/* VERIFY ANSWER */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $answer =
    strtolower(
        trim($_POST['answer'] ?? '')
    );

    $stored_answer =
    $user[
        'security_answer_' .
        $question_number
    ];

    if (
        password_verify(
            $answer,
            $stored_answer
        )
    ) {

        $_SESSION['user_id'] =
        $user['user_id'];

        $_SESSION['fname'] =
        $user['fname'];

        $_SESSION['lname'] =
        $user['lname'];

        $_SESSION['email'] =
        $user['email'];

        $_SESSION['role'] =
        $user['role'];

        $_SESSION['force_password_change'] =
        true;

        $_SESSION['login_attempts'] = 0;

        unset($_SESSION['recovery_question']);

        unset($_SESSION['recovery_email']);

        header(
            'Location: dashboard.php?force_password_change=1'
        );

        exit;

    } else {

        $error_message =
        'Incorrect recovery answer.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width,
                   initial-scale=1.0">

    <title>

        Security Question Recovery

    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>

        body {

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            background:
            linear-gradient(
                135deg,
                #E3F2FD,
                #F8FBFF
            );

            font-family: Arial, sans-serif;
        }

        .recovery-card {

            width: 420px;

            max-width: 92%;

            background: white;

            border-radius: 24px;

            padding: 35px;

            box-shadow:
            0 15px 40px rgba(0,0,0,0.08);
        }

        .recovery-icon {

            width: 90px;
            height: 90px;

            margin: auto;

            border-radius: 50%;

            background:
            rgba(13,110,253,0.1);

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 2.5rem;

            color: #0d6efd;

            margin-bottom: 20px;
        }

        .question-box {

            background:
            #F4F8FF;

            border-radius: 16px;

            padding: 18px;

            font-weight: 600;

            color: #2c3e50;

            margin-bottom: 25px;
        }

        .btn-recovery {

            width: 100%;

            border-radius: 12px;

            padding: 12px;

            font-weight: 600;
        }
        .form-control {

            height: 50px;

            padding: 12px 15px;

            font-size: 16px;

            line-height: 1.5;

            overflow: visible;

            white-space: normal;
        }

    </style>

</head>

<body>

    <div class="recovery-card">

        <div class="text-center">

            <div class="recovery-icon">

                <i class="bi bi-shield-lock-fill"></i>

            </div>

            <h3 class="fw-bold">

                Security Question Recovery

            </h3>

            <p class="text-muted">

                Answer your recovery question
                to access your account.

            </p>

        </div>

        <?php if (!empty($error_message)): ?>

            <div class="alert alert-danger">

                <i class="bi bi-exclamation-circle me-2"></i>

                <?php
                echo htmlspecialchars(
                    $error_message
                );
                ?>

            </div>

        <?php endif; ?>

        <div class="question-box">

            <?php echo $question; ?>

        </div>

        <form method="POST">

            <div class="mb-3">

                <label class="form-label fw-semibold">

                    Your Answer

                </label>

                <input type="text"
                       class="form-control"
                       name="answer"
                       placeholder="Enter your answer"
                       required>

            </div>

            <button type="submit"
                    class="btn btn-primary btn-recovery">

                <i class="bi bi-unlock me-2"></i>

                Verify Answer

            </button>

            <div class="text-center mt-3">

                <a href="login.php"
                   class="text-decoration-none">

                    Back to Login

                </a>

            </div>

        </form>

    </div>

</body>

</html>