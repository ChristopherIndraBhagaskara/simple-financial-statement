<?php
ini_set('display_errors', '1');

class FinancialStatement
{
    private $account;
    private $username;

    public function __construct($username)
    {
        $this->account = [
            'balance' => 0,
            'transactions' => [],
        ];

        $this->username = $username;

        // session_start();

        if (!isset($_SESSION['account'][$username])) {
            $_SESSION['account'][$username] = $this->account;
        } else {
            $this->account = $_SESSION['account'][$username];
        }
    }

    public function deposit($amount)
    {
        if (!is_numeric($amount) || $amount <= 0) {
            return "Invalid amount for deposit.";
        }
        
        $this->account['balance'] += $amount;
        $this->account['transactions'][] = [
            'time' => date("Y-m-d H:i:s",time()),
            'type' => 'Deposit',
            'amount' => $amount,
            'to' => null,
            'description' => null
        ];
        
        $_SESSION['account'][$this->username] = $this->account;

        return "Deposit of $amount successfully made.";
    }

    public function withdraw($amount)
    {
        if (!is_numeric($amount) || $amount <= 0) {
            return "Invalid amount for withdrawal.";
        }

        if ($amount > $this->account['balance']) {
            return "Your balance is insufficient";
        }

        $this->account['balance'] -= $amount;
        $this->account['transactions'][] = [
            'time' => date("Y-m-d H:i:s",time()),
            'type' => 'Withdrawal',
            'amount' => $amount,
            'to' => null,
            'description' => null
        ];

        $_SESSION['account'][$this->username] = $this->account;

        return "Withdrawal of $amount successfully made.";
    }

    public function checkBalance()
    {
        return "Current balance: {$this->account['balance']}";
    }

    public function getTransactionHistory()
    {
        return $this->account['transactions'];
    }

    public function transfer($amount, $to)
    {
        if (($this->username == "Feon" && $to == "Vira") || ($this->username == "Vira" && $to == "Feon")) {
            if (!is_numeric($amount) || $amount <= 0) {
                return "Invalid amount for transfer.";
            }
    
            if ($amount > $this->account['balance']) {
                return "Your balance is insufficient";
            }
    
            $this->account['balance'] -= $amount;
            $this->account['transactions'][] = [
                'time' => date("Y-m-d H:i:s",time()),
                'type' => 'Transfer',
                'amount' => $amount,
                'to' => $to,
                'description' => 'Transfer to ' . $to
            ];
    
            if (!isset($_SESSION['account'][$to])) {
                $_SESSION['account'][] = new FinancialStatement($to);
            }

            $_SESSION['account'][$to]['balance'] += $amount;
            $_SESSION['account'][$to]['transactions'][] = [
                'time' => date("Y-m-d H:i:s",time()),
                'type' => 'Transfer',
                'amount' => $amount,
                'to' => null,
                'description' => 'Transfer from ' . $this->username
            ];
    
            $_SESSION['account'][$this->username] = $this->account;
    
            return "Transfer of $amount to $to successful.";
            
        } else {
            return "Recipient account not found.";
        };
    }
}