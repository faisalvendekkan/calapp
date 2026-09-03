<?php
declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();

$authUsername = (string) (getenv('CALCAPP_USERNAME') ?: '');
$authPasswordHash = (string) (getenv('CALCAPP_PASSWORD_HASH') ?: '');
$authConfigured = $authUsername !== '' && preg_match('/^[a-f0-9]{64}$/', $authPasswordHash) === 1;
$authError = null;
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if ($authConfigured && $_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $submittedUsername = trim((string) ($_POST['username'] ?? ''));
    $submittedPasswordHash = hash('sha256', (string) ($_POST['password'] ?? ''));

    if (hash_equals($authUsername, $submittedUsername) && hash_equals($authPasswordHash, $submittedPasswordHash)) {
        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $authError = 'Incorrect username or password.';
}

$isAuthenticated = ($_SESSION['authenticated'] ?? false) === true;
$first = $_POST['first'] ?? '';
$second = $_POST['second'] ?? '';
$operation = $_POST['operation'] ?? 'add';
$result = null;
$error = null;

if ($isAuthenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'calculate') {
    if (!is_numeric($first) || !is_numeric($second)) {
        $error = 'Please enter two valid numbers.';
    } else {
        $a = (float) $first;
        $b = (float) $second;

        switch ($operation) {
            case 'add':
                $result = $a + $b;
                break;
            case 'subtract':
                $result = $a - $b;
                break;
            case 'multiply':
                $result = $a * $b;
                break;
            case 'divide':
                if ($b === 0.0) {
                    $error = 'Division by zero is not allowed.';
                } else {
                    $result = $a / $b;
                }
                break;
            default:
                $error = 'Please choose a valid operation.';
        }
    }
}

function displayNumber(float $number): string
{
    if (is_infinite($number) || is_nan($number)) {
        return 'Result is outside the supported range';
    }

    return rtrim(rtrim(number_format($number, 10, '.', ''), '0'), '.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CalcApp</title>
    <style>
        :root {
            color-scheme: dark;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #050505;
            color: #fff7bf;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at top left, rgba(250, 204, 21, .2), transparent 34%),
                radial-gradient(circle at bottom right, rgba(234, 179, 8, .14), transparent 34%),
                #050505;
        }

        .calculator {
            width: min(100%, 440px);
            padding: 32px;
            border: 1px solid rgba(250, 204, 21, .35);
            border-radius: 24px;
            background: rgba(10, 10, 10, .9);
            box-shadow: 0 24px 70px rgba(0, 0, 0, .35);
            backdrop-filter: blur(18px);
        }

        h1 { margin: 0 0 8px; font-size: 2rem; }
        .subtitle { margin: 0 0 28px; color: #fde047; }
        label { display: block; margin-bottom: 8px; font-weight: 700; }

        .topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
        }

        .logout-form { margin: 0; }

        .logout-button {
            width: auto;
            min-height: 38px;
            margin: 0;
            padding: 0 14px;
            border: 1px solid #ca8a04;
            color: #fde047;
            background: transparent;
            font-size: .9rem;
        }

        input, select, button {
            width: 100%;
            min-height: 48px;
            border-radius: 12px;
            font: inherit;
        }

        input, select {
            margin-bottom: 18px;
            padding: 0 14px;
            border: 1px solid #713f12;
            background: #171717;
            color: #fefce8;
        }

        input:focus, select:focus {
            outline: 3px solid rgba(250, 204, 21, .25);
            border-color: #facc15;
        }

        button {
            margin-top: 6px;
            border: 0;
            color: #050505;
            background: linear-gradient(135deg, #fde047, #eab308);
            font-weight: 800;
            cursor: pointer;
        }

        button:hover { filter: brightness(1.08); }

        .message {
            margin-top: 22px;
            padding: 16px;
            border-radius: 12px;
            text-align: center;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .result { background: rgba(250, 204, 21, .14); color: #fde047; }
        .error { background: rgba(250, 204, 21, .1); color: #fef08a; border: 1px solid #ca8a04; }
    </style>
</head>
<body>
    <?php if (!$isAuthenticated): ?>
    <main class="calculator">
        <h1>CalcApp Login</h1>
        <?php if (!$authConfigured): ?>
            <p class="subtitle">Login is not configured yet.</p>
            <div class="message error" role="alert">Set CALCAPP_USERNAME and CALCAPP_PASSWORD_HASH in the private hosting environment.</div>
        <?php else: ?>
            <p class="subtitle">Sign in to use the calculator.</p>

            <form method="post" action="">
                <input type="hidden" name="action" value="login">

                <label for="username">Username</label>
                <input id="username" name="username" type="text" autocomplete="username" required autofocus>

                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>

                <button type="submit">Sign in</button>
            </form>

            <?php if ($authError !== null): ?>
                <div class="message error" role="alert"><?= htmlspecialchars($authError, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    <?php else: ?>
    <main class="calculator">
        <div class="topbar">
            <div>
                <h1>CalcApp</h1>
                <p class="subtitle">A simple calculator powered by PHP.</p>
            </div>
            <form class="logout-form" method="post" action="">
                <input type="hidden" name="action" value="logout">
                <button class="logout-button" type="submit">Log out</button>
            </form>
        </div>

        <form method="post" action="">
            <input type="hidden" name="action" value="calculate">
            <label for="first">First number</label>
            <input id="first" name="first" type="number" step="any" required value="<?= htmlspecialchars((string) $first, ENT_QUOTES, 'UTF-8') ?>">

            <label for="operation">Operation</label>
            <select id="operation" name="operation">
                <option value="add" <?= $operation === 'add' ? 'selected' : '' ?>>Add (+)</option>
                <option value="subtract" <?= $operation === 'subtract' ? 'selected' : '' ?>>Subtract (−)</option>
                <option value="multiply" <?= $operation === 'multiply' ? 'selected' : '' ?>>Multiply (×)</option>
                <option value="divide" <?= $operation === 'divide' ? 'selected' : '' ?>>Divide (÷)</option>
            </select>

            <label for="second">Second number</label>
            <input id="second" name="second" type="number" step="any" required value="<?= htmlspecialchars((string) $second, ENT_QUOTES, 'UTF-8') ?>">

            <button type="submit">Calculate</button>
        </form>

        <?php if ($result !== null): ?>
            <div class="message result" role="status">Result: <?= htmlspecialchars(displayNumber($result), ENT_QUOTES, 'UTF-8') ?></div>
        <?php elseif ($error !== null): ?>
            <div class="message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </main>
    <?php endif; ?>
</body>
</html>

