<?php
// models/Event.php

class Event {
    private $conn;
    private $table = 'events';

    /**
     * Construction
     * @param PDO 
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Creates a new event.
     *
     * @param int $user_id
     * @param string $title
     * @param string $description
     * @param string $start_date
     * @param string $end_date
     * @param string $category
     * @param string $color_code
     * @return bool
     */
    public function create($user_id, $title, $description, $start_date, $end_date, $category, $color_code) {
        $query = "INSERT INTO " . $this->table . " (user_id, title, description, start_date, end_date, category, color_code) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $title, $description, $start_date, $end_date, $category, $color_code]);
    }

    /**
     * Retrieves all events for a given user in a month 
     *
     * @param int $user_id
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getEventsByMonth($user_id, $month, $year) {
        // start and end dates
        $start_of_month = sprintf('%04d-%02d-01', $year, $month);
        $end_of_month = date('Y-m-t', strtotime($start_of_month));

        // for own events
        $user_events_query = "SELECT * FROM " . $this->table . " WHERE user_id = ? AND start_date BETWEEN ? AND ?";
        
        // for shared events
        $shared_events_query = "SELECT e.* FROM " . $this->table . " e 
                                LEFT JOIN shared_events se ON e.id = se.event_id 
                                WHERE se.user_id = ? AND e.start_date BETWEEN ? AND ?";

        // two queries-UNION ALL to get all events
        $query = "($user_events_query) UNION ($shared_events_query)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $start_of_month, $end_of_month, $user_id, $start_of_month, $end_of_month]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * editing event
     *
     * @param int $id
     * @param int $user_id
     * @param string $title
     * @param string $description
     * @param string $start_date
     * @param string $end_date
     * @param string $category
     * @param string $color_code
     * @return bool
     */
    public function update($id, $user_id, $title, $description, $start_date, $end_date, $category, $color_code) {
        $query = "UPDATE " . $this->table . " SET title = ?, description = ?, start_date = ?, end_date = ?, category = ?, color_code = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$title, $description, $start_date, $end_date, $category, $color_code, $id, $user_id]);
    }

    /**
     * Deletes an event.
     *
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
     * Shares an event with another user.
     *
     * @param int $event_id
     * @param string $invitee_email
     * @return bool
     */
    public function shareEvent($event_id, $invitee_email) {
        try {
            // Find the user ID of the invitee
            $user_query = "SELECT id FROM users WHERE email = ?";
            $user_stmt = $this->conn->prepare($user_query);
            $user_stmt->execute([$invitee_email]);
            $invitee_user = $user_stmt->fetch(PDO::FETCH_ASSOC);

            // If user not found or is the same user, return false
            if (!$invitee_user || $invitee_user['id'] == $_SESSION['user_id']) {
                return false;
            }

            $invitee_id = $invitee_user['id'];

            // Insert into shared_events table
            $share_query = "INSERT INTO shared_events (event_id, user_id) VALUES (?, ?)";
            $share_stmt = $this->conn->prepare($share_query);
            return $share_stmt->execute([$event_id, $invitee_id]);

        } catch (PDOException $e) {
            // Handle duplicate entry error - if event already shared 
            if ($e->getCode() == '23000') {
                return false;
            }
            throw $e;
        }
    }
}
