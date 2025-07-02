<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// File to store user data
$usersFile = 'users.txt';
$username = $_SESSION['username'];
$money = $_SESSION['money'];

// Slot symbols
$symbols = ['🍒', '🍋', '🔔', '⭐', '🍀', '7️⃣'];
$rows = 3;
$cols = 3;
$betOptions = [10, 25, 50, 75, 100];

// Handle spin
$result = [];
$win = 0;
$message = '';
if (isset($_POST['spin'])) {
    $bet = (int)$_POST['bet'];
    if ($bet > $money) {
        $message = 'Not enough balance!';
    } elseif (!in_array($bet, $betOptions)) {
        $message = 'Invalid bet!';
    } else {
        // Generate slot grid
        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $cols; $j++) {
                $result[$i][$j] = $symbols[array_rand($symbols)];
            }
        }
        // Simple win logic: if any row has all same symbols, win 5x bet
        for ($i = 0; $i < $rows; $i++) {
            if (count(array_unique($result[$i])) === 1) {
                $win += $bet * 5;
            }
        }
        $money = $money - $bet + $win;
        $_SESSION['money'] = $money;
        // Update user balance in file
        $users = file_exists($usersFile) ? file($usersFile, FILE_IGNORE_NEW_LINES) : [];
        foreach ($users as $idx => $user) {
            list($u, $p, $m) = explode(':', $user);
            if ($u === $username) {
                $users[$idx] = "$u:$p:$money";
                break;
            }
        }
        file_put_contents($usersFile, implode("\n", $users) . "\n");
        $message = $win > 0 ? "You won ₱$win!" : "No win this time.";
    }
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle reset balance
if (isset($_POST['reset'])) {
    $money = 1000;
    $_SESSION['money'] = $money;
    // Update user balance in file
    $users = file_exists($usersFile) ? file($usersFile, FILE_IGNORE_NEW_LINES) : [];
    foreach ($users as $idx => $user) {
        $parts = explode(':', $user);
        if (count($parts) === 4) {
            list($u, $e, $p, $m) = $parts;
        } else {
            list($u, $p, $m) = $parts; // fallback for old format
        }
        if ($u === $username) {
            if (count($parts) === 4) {
                $users[$idx] = "$u:$e:$p:1000";
            } else {
                $users[$idx] = "$u:$p:1000";
            }
            break;
        }
    }
    file_put_contents($usersFile, implode("\n", $users) . "\n");
    $message = "Balance reset to ₱1000.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Slot Machine</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <form method="post" style="float:right;">
            <button type="submit" name="logout">Logout</button>
        </form>
        <p>Balance: <strong>₱<?php echo $money; ?></strong></p>
        <form method="post">
            <label>Bet Amount:</label>
            <select name="bet">
                <?php foreach ($betOptions as $opt): ?>
                    <option value="<?php echo $opt; ?>" <?php if(isset($_POST['bet']) && $_POST['bet']==$opt) echo 'selected'; ?>>₱<?php echo $opt; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="spin">Spin</button>
        </form>
        <form method="post" style="display:inline;">
            <button type="submit" name="reset">Reset Balance</button>
        </form>
        <?php if ($message) echo "<p class='message'>$message</p>"; ?>
        <?php if ($result): ?>
            <div class="slot-grid">
                <?php for (
                    $i = 0; $i < $rows; $i++): ?>
                    <div class="slot-row">
                        <?php for ($j = 0; $j < $cols; $j++): ?>
                            <span class="slot-cell"><?php echo $result[$i][$j]; ?></span>
                        <?php endfor; ?>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
