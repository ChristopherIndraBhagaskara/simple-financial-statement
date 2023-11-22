<?php

    include_once 'FinancialStatement.php';
    session_start();
    
    // Auth

    if (isset($_POST['login']))
    {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (($username == 'Feon' || $username == 'Vira' ) && $password === '1') {
            $_SESSION['current_user'] = $username;
        } else {
            $loginError = "Invalid username or password.";
        }
    }
    
    if (isset($_POST['logout']) || !isset($_SESSION['current_user'])){
        $_SESSION['current_user'] = 'Guest';
    }

    $financialStatement = new FinancialStatement($_SESSION['current_user']);

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'deposit':
                $message = $financialStatement->deposit($_POST['amount']);
                break;
            case 'withdraw':
                $message = $financialStatement->withdraw($_POST['amount']);
                break;
            case 'balance':
                $message = $financialStatement->checkBalance();
                break;
            case 'transfer':
                $to = $_POST['to'];
                $transferMessage = $financialStatement->transfer($_POST['amount'], $to);
                break;
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Simple Financial Statement</title>
    </head>
    <body>
        <h1>Welcome, <?php echo $_SESSION['current_user']; ?>!</h1>
        <form method="post" action="index.php">
            <div style="margin-bottom: 0.5em">
                <label for="amount">Amount:</label>
                <input type="text" name="amount" id="amount">            
            </div>
            <div>
                <input type="submit" name="action" value="deposit">
                <input type="submit" name="action" value="withdraw">
                <input type="submit" name="action" value="balance">
            </div>
        </form>

        <?php
            if (isset($message)) {
                echo "<p>$message</p>";
            }

            if (isset($transferMessage)) {
                echo "<p>$transferMessage</p>";
            }

            $transactionHistory = $financialStatement->getTransactionHistory();
            if (!empty($transactionHistory)) {
        ?>
            <hr style="margin-top: 3em">
            <h2>History</h2>
            <table border="1" style="border-collapse: collapse">
                <thead>
                    <tr>
                        <td>Time</td>
                        <td>Type</td>
                        <td>Debit</td>
                        <td>Credit</td>
                        <td>Balance</td>
                        <?php if($_SESSION['current_user'] != "Guest") { ?>
                        <td>Description</td>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $balance = 0;
                        foreach ($transactionHistory as $transaction) {
                            if($transaction['type'] == 'Transfer' && $transaction['to'] == null) {
                                $debit = $transaction['amount'];
                                $credit = 0;
                            } else {
                                $debit = $transaction['type'] == 'Deposit' ? $transaction['amount'] : 0;
                                $credit = $transaction['type'] == 'Deposit' ? 0 : $transaction['amount'];
                            }
                            $balance += $debit - $credit;

                            echo "<tr>";
                            echo "<td>" . $transaction['time'] . "</td>";
                            echo "<td>" . $transaction['type'] . "</td>";
                            echo "<td>" . $debit . "</td>";
                            echo "<td>" . $credit . "</td>";
                            echo "<td>" . $balance . "</td>";
                            if($_SESSION['current_user'] != "Guest") {
                            echo "<td>" . $transaction['description'] . "</td>";
                            }
                            echo "</tr>";
                        }
                    ?>
                </tbody>
            </table>
        <?php 
            }
        ?>

    <?php
        if($_SESSION['current_user'] != "Guest") {
    ?>
    <h2>Transfer</h2>
    <form method="post" action="index.php">
        <div style="margin-bottom: 0.5em">
            <label for="amount_transfer">Amount:</label>
            <input type="text" name="amount" id="amount_transfer" required>
        </div>
        <div style="margin-bottom: 0.5em">
            <label for="to">to:</label>
            <input type="text" name="to" value="<?php echo $_SESSION['current_user'] == 'Feon' ? 'Vira' : 'Feon' ?>">
        </div>
        <div style="margin-bottom: 0.5em">
            <input type="submit" name="action" value="transfer">
        </div>
    </form>

    <form method="post" action="index.php">
        <div style="margin-bottom: 0.5em">
            <input type="submit" name="logout" value="logout">
        </div>
    </form>
    <?php } else { ?>
        <hr style="margin-top: 3em">
        <h1>Login</h1>
        <form method="post" action="index.php">
            <div style="margin-bottom: 0.5em">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>                
            </div>
            <div style="margin-bottom: 0.5em">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div style="margin-bottom: 0.5em">
                <input type="submit" name="login" value="login">
            </div>
        </form>
        <?php
            if (isset($loginError)) {
                echo "<p>$loginError</p>";
            }
        } ?>
    </body>
</html>