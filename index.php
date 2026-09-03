<?php
declare(strict_types=1);

$first = $_POST['first'] ?? '';
$second = $_POST['second'] ?? '';
$operation = $_POST['operation'] ?? 'add';
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            background: #0f172a;
            color: #e2e8f0;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, .2), transparent 34%),
                radial-gradient(circle at bottom right, rgba(139, 92, 246, .2), transparent 34%),
                #0f172a;
        }

        .calculator {
            width: min(100%, 440px);
            padding: 32px;
            border: 1px solid rgba(148, 163, 184, .2);
            border-radius: 24px;
            background: rgba(15, 23, 42, .82);
            box-shadow: 0 24px 70px rgba(0, 0, 0, .35);
            backdrop-filter: blur(18px);
        }

        h1 { margin: 0 0 8px; font-size: 2rem; }
        .subtitle { margin: 0 0 28px; color: #94a3b8; }
        label { display: block; margin-bottom: 8px; font-weight: 700; }

        input, select, button {
            width: 100%;
            min-height: 48px;
            border-radius: 12px;
            font: inherit;
        }

        input, select {
            margin-bottom: 18px;
            padding: 0 14px;
            border: 1px solid #334155;
            background: #111827;
            color: #f8fafc;
        }

        input:focus, select:focus {
            outline: 3px solid rgba(56, 189, 248, .25);
            border-color: #38bdf8;
        }

        button {
            margin-top: 6px;
            border: 0;
            color: #082f49;
            background: linear-gradient(135deg, #67e8f9, #a78bfa);
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

        .result { background: rgba(34, 197, 94, .14); color: #86efac; }
        .error { background: rgba(239, 68, 68, .14); color: #fca5a5; }
    </style>
</head>
<body>
    <main class="calculator">
        <h1>CalcApp</h1>
        <p class="subtitle">A simple calculator powered by PHP.</p>

        <form method="post" action="">
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
</body>
</html>
