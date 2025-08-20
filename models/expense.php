<?php
// models/Expense.php

/**
 * The Expense class is the model for handling all expense-related database operations.
 */
class Expense {
    private $conn;
    private $table = 'expenses';
    private $sharedTable = 'shared_expenses'; // Example for future sharing feature, though not implemented in this module.

    /**
     * Constructor for the Expense class.
     * @param PDO $db The database connection object.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Adds a new expense for a user.
     *
     * @param int $user_id
     * @param float $amount
     * @param string $category
     * @param string $description
     * @param string $date
     * @return bool
     */
    public function addExpense($user_id, $amount, $category, $description, $date) {
        $query = "INSERT INTO " . $this->table . " (user_id, amount, category, description, date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $amount, $category, $description, $date]);
    }

    /**
     * Retrieves all expenses for a given user and month.
     *
     * @param int $user_id
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getMonthlyExpenses($user_id, $month, $year) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ? ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $month, $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retrieves a single expense by ID for a specific user.
     *
     * @param int $id
     * @param int $user_id
     * @return array|false
     */
    public function getExpenseById($id, $user_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id, $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Updates an existing expense.
     *
     * @param int $id
     * @param int $user_id
     * @param float $amount
     * @param string $category
     * @param string $description
     * @param string $date
     * @return bool
     */
    public function updateExpense($id, $user_id, $amount, $category, $description, $date) {
        $query = "UPDATE " . $this->table . " SET amount = ?, category = ?, description = ?, date = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$amount, $category, $description, $date, $id, $user_id]);
    }

    /**
     * Deletes an expense.
     *
     * @param int $id
     * @param int $user_id
     * @return bool
     */
    public function deleteExpense($id, $user_id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id, $user_id]);
    }

    /**
     * Gets a summary of monthly expenses by category.
     *
     * @param int $user_id
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getMonthlySummary($user_id, $month, $year) {
        $query = "SELECT category, SUM(amount) AS total_amount FROM " . $this->table . " WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ? GROUP BY category";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $month, $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Gets a summary of expenses for a given date range.
     *
     * @param int $user_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function getReportData($user_id, $start_date, $end_date) {
        $query = "SELECT date, category, amount FROM " . $this->table . " WHERE user_id = ? AND date BETWEEN ? AND ? ORDER BY date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
