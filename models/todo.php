<?php
// models/todo.php

/**
 * The Todo class is the model for handling all to-do list database operations.
 */
class Todo {
    private $conn;
    private $table = 'todos';

    /**
     * Constructor for the Todo class.
     * @param PDO $db The database connection object.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Adds a new to-do item to the database.
     * @param array $data An associative array containing task details.
     * @return bool True on success, false on failure.
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (user_id, task, due_date, priority, label, recurring, recurring_frequency) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['user_id'],
            $data['task'],
            $data['due_date'],
            $data['priority'],
            $data['label'],
            $data['recurring'],
            $data['recurring_frequency']
        ]);
    }

    /**
     * Updates an existing to-do item in the database.
     * @param int $id The ID of the to-do item.
     * @param array $data An associative array with updated task details.
     * @return bool True on success, false on failure.
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET task = ?, due_date = ?, priority = ?, label = ?, completed = ?, recurring = ?, recurring_frequency = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['task'],
            $data['due_date'],
            $data['priority'],
            $data['label'],
            $data['completed'],
            $data['recurring'],
            $data['recurring_frequency'],
            $id,
            $data['user_id']
        ]);
    }

    /**
     * Toggles the completion status of a to-do item.
     * @param int $id The ID of the to-do item.
     * @param int $user_id The ID of the user to ensure ownership.
     * @param int $completed The new completion status (0 or 1).
     * @return bool True on success, false on failure.
     */
    public function toggleCompletion($id, $user_id, $completed) {
        $query = "UPDATE " . $this->table . " SET completed = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$completed, $id, $user_id]);
    }

    /**
     * Fetches a single todo item by its ID.
     * @param int $id The ID of the todo item.
     * @return array|false The todo item data or false if not found.
     */
    public function getTodoById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Deletes a to-do item from the database.
     * @param int $id The ID of the to-do item to delete.
     * @param int $user_id The ID of the user to ensure ownership.
     * @return bool True on success, false on failure.
     */
    public function delete($id, $user_id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id, $user_id]);
    }

    /**
     * Retrieves all to-do items for a specific user, with optional filters and sorting.
     * @param int $user_id The ID of the user.
     * @param array $filters An array of filters (e.g., ['status' => 'incomplete', 'priority' => 'High']).
     * @param string $sort_by The column to sort by (e.g., 'due_date').
     * @return array An array of to-do items.
     */
    public function getTodosByUserId($user_id, $filters = [], $sort_by = 'created_at DESC') {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = ?";
        $params = [$user_id];

        // Apply filters dynamically
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'incomplete') {
                $query .= " AND completed = 0";
            } elseif ($filters['status'] === 'completed') {
                $query .= " AND completed = 1";
            }
        }
        if (!empty($filters['priority'])) {
            $query .= " AND priority = ?";
            $params[] = $filters['priority'];
        }
        if (!empty($filters['label'])) {
            $query .= " AND label LIKE ?";
            $params[] = '%' . $filters['label'] . '%';
        }
        if (!empty($filters['search'])) {
            $search_term = '%' . $filters['search'] . '%';
            $query .= " AND (task LIKE ? OR due_date LIKE ?)";
            $params[] = $search_term;
            $params[] = $search_term;
        }

        // Apply sorting
        if (!empty($sort_by)) {
            $query .= " ORDER BY " . $sort_by;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>