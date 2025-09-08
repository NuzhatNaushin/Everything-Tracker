<?php
// models/todo.php

class Todo {
    private $conn;
    private $table = 'todos';

    /**
     * Constructor
     * @param PDO 
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Adds a new to-do item to the database.
     * @param array 
     * @return bool 
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
     * editing todo item
     * @param int $id 
     * @param array $data 
     * @return bool 
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
     * Toggle completion 
     * @param int $id 
     * @param int $user_id 
     * @param int $completed 
     * @return bool
     */
    public function toggleCompletion($id, $user_id, $completed) {
        $query = "UPDATE " . $this->table . " SET completed = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$completed, $id, $user_id]);
    }

    /**
     * Fetches a single todo item 
     * @param int $id 
     * @return array|false 
     */
    public function getTodoById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Deletes a to-do item 
     * @param int $id 
     * @param int $user_id 
     * @return bool 
     */
    public function delete($id, $user_id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id, $user_id]);
    }

    /**
     * Retrieves all to-do items + filters and sorting.
     * @param int $user_id 
     * @param array $filters 
     * @param string $sort_by 
     * @return array 
     */
    public function getTodosByUserId($user_id, $filters = [], $sort_by = 'created_at DESC') {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = ?";
        $params = [$user_id];

       //filteringh 
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

        //sorting
        if (!empty($sort_by)) {
            $query .= " ORDER BY " . $sort_by;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>