<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$uid = (int) $_SESSION['user_id'];
$tx_id = (int) ($_POST['tx_id'] ?? 0);
$description = trim($_POST['description'] ?? '');
$amount = floatval($_POST['amount'] ?? 0);
$type = $_POST['type'] ?? '';
$category = $_POST['category'] ?? '';

if (!$tx_id || !$description || !$amount || !in_array($type, ['income', 'expense']) || !$category) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Update transaction
$stmt = $conn->prepare("UPDATE transactions SET description=?, amount=?, type=?, category=? WHERE id=? AND user_id=?");
$stmt->bind_param("sdssii", $description, $amount, $type, $category, $tx_id, $uid);
$updated = $stmt->execute();
$stmt->close();

if (!$updated) {
    echo json_encode(['success' => false, 'error' => 'Failed to update']);
    exit;
}

// Get updated transactions
$stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id=? ORDER BY tdate DESC, created_at DESC");
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate totals
$totStmt = $conn->prepare("SELECT 
  COALESCE(SUM(CASE WHEN type='income' THEN amount END),0) AS total_income,
  COALESCE(SUM(CASE WHEN type='expense' THEN amount END),0) AS total_expense
FROM transactions WHERE user_id = ?");
$totStmt->bind_param("i", $uid);
$totStmt->execute();
$totRes = $totStmt->get_result()->fetch_assoc();
$totStmt->close();

$total_income = (float)$totRes['total_income'];
$total_expense = (float)$totRes['total_expense'];
$balance = $total_income - $total_expense;

// Chart data (all categories)
$chartStmt = $conn->prepare("SELECT category, SUM(amount) AS total FROM transactions WHERE user_id=? GROUP BY category");
$chartStmt->bind_param("i", $uid);
$chartStmt->execute();
$chartData = $chartStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$chartStmt->close();

$chart = [];
foreach ($chartData as $row) {
    $chart[$row['category']] = (float)$row['total'];
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
