<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

// Check logged in
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$uid = (int) $_SESSION['user_id'];

// Validate input
$description = trim($_POST['description'] ?? '');
$amount = $_POST['amount'] ?? '';
$type = $_POST['type'] ?? '';
$category = $_POST['category'] ?? '';

if ($description === '' || $amount === '' || !is_numeric($amount) || !in_array($type, ['income','expense'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid input']);
  exit;
}

$amount = (float)$amount;
$tdate = date('Y-m-d'); // you can send a date from client if required
$created_at = date('Y-m-d H:i:s');

// Insert
$ins = $conn->prepare("INSERT INTO transactions (user_id, tdate, type, category, amount, description, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
$ins->bind_param("isssdss", $uid, $tdate, $type, $category, $amount, $description, $created_at);
$ok = $ins->execute();
$ins->close();

if (!$ok) {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to add transaction']);
  exit;
}

// Recalculate totals and fetch updated transactions and chart data
// Totals
$totStmt = $conn->prepare("SELECT 
  COALESCE(SUM(CASE WHEN type='income' THEN amount END),0) AS total_income,
  COALESCE(SUM(CASE WHEN type='expense' THEN amount END),0) AS total_expense
FROM transactions WHERE user_id = ?");
$totStmt->bind_param("i", $uid);
$totStmt->execute();
$tot = $totStmt->get_result()->fetch_assoc();
$totStmt->close();

$total_income = (float)$tot['total_income'];
$total_expense = (float)$tot['total_expense'];
$balance = $total_income - $total_expense;

// Transactions (latest 100)
$stmt = $conn->prepare("SELECT id, tdate, type, category, amount, description FROM transactions WHERE user_id = ? ORDER BY tdate DESC, created_at DESC LIMIT 200");
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Chart data grouped by category (expenses and incomes combined by category)
$chartStmt = $conn->prepare("SELECT category, SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense_total, SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS income_total FROM transactions WHERE user_id = ? GROUP BY category");
$chartStmt->bind_param("i", $uid);
$chartStmt->execute();
$chartRes = $chartStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$chartStmt->close();

// Build category totals (we'll keep expense totals for the pie; you can change to total incomes too)
$chart = [];
foreach ($chartRes as $r) {
  // Use sum of expense and income if you want overall total per category, or use expense only.
  $chart[$r['category']] = (float)$r['expense_total'] + (float)$r['income_total'];
}

echo json_encode([
  'success' => true,
  'totals' => [
    'income' => $total_income,
    'expense' => $total_expense,
    'balance' => $balance
  ],
  'transactions' => $transactions,
  'chart' => $chart
]);
exit;
