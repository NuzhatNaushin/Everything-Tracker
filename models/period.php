<?php
// models/Period.php

/**
 * The Period class is the model for handling all period-related database operations.
 */
class Period {
    private $conn;
    private $periodsTable = 'periods';
    private $symptomsTable = 'symptoms';

    /**
     * Constructor for the Period class.
     * @param PDO $db The database connection object.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Logs a new period entry.
     *
     * @param int $user_id
     * @param string $start_date
     * @param string $end_date
     * @return bool
     */
    public function addPeriod($user_id, $start_date, $end_date) {
        $query = "INSERT INTO " . $this->periodsTable . " (user_id, start_date, end_date) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $start_date, $end_date]);
    }

    /**
     * Retrieves all period entries for a given user, ordered by date.
     *
     * @param int $user_id
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getPeriods($user_id, $month, $year) {
        $start_of_month = date('Y-m-01', strtotime("$year-$month-01"));
        $end_of_month = date('Y-m-t', strtotime("$year-$month-01"));
        
        $query = "SELECT * FROM " . $this->periodsTable . " WHERE user_id = ? AND ((start_date BETWEEN ? AND ?) OR (end_date BETWEEN ? AND ?) OR (start_date < ? AND end_date > ?)) ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $start_of_month, $end_of_month, $start_of_month, $end_of_month, $start_of_month, $end_of_month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets all period entries for a user, regardless of month/year.
     * @param int $user_id
     * @return array
     */
    public function getAllPeriods($user_id) {
        $query = "SELECT id, user_id, start_date, end_date FROM " . $this->periodsTable . " WHERE user_id = ? ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calculates the average cycle length based on past periods.
     * @param int $user_id
     * @return int|null
     */
    public function getAverageCycleLength($user_id) {
        $periods = $this->getAllPeriods($user_id);
        if (count($periods) < 2) {
            return null;
        }

        $cycle_lengths = [];
        for ($i = 0; $i < count($periods) - 1; $i++) {
            $current_start = strtotime($periods[$i]['start_date']);
            $previous_start = strtotime($periods[$i+1]['start_date']);
            $diff_in_days = ($current_start - $previous_start) / (60 * 60 * 24);
            $cycle_lengths[] = $diff_in_days;
        }
        
        return count($cycle_lengths) > 0 ? round(array_sum($cycle_lengths) / count($cycle_lengths)) : null;
    }

    /**
     * Logs a daily symptom entry for a user.
     * @param int $user_id
     * @param string $date
     * @param string|null $cramps
     * @param string|null $flow_level
     * @param string|null $mood
     * @param string|null $notes
     * @return bool
     */
    public function addSymptom($user_id, $date, $cramps, $flow_level, $mood, $notes) {
        // First, check if a symptom entry already exists for this user and date
        $query = "SELECT id FROM " . $this->symptomsTable . " WHERE user_id = ? AND date = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $date]);
        $existing_symptom = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_symptom) {
            // Update the existing entry
            $update_query = "UPDATE " . $this->symptomsTable . " SET cramps = ?, flow_level = ?, mood = ?, notes = ? WHERE id = ?";
            $update_stmt = $this->conn->prepare($update_query);
            return $update_stmt->execute([$cramps, $flow_level, $mood, $notes, $existing_symptom['id']]);
        } else {
            // Insert a new entry
            $insert_query = "INSERT INTO " . $this->symptomsTable . " (user_id, date, cramps, flow_level, mood, notes) VALUES (?, ?, ?, ?, ?, ?)";
            $insert_stmt = $this->conn->prepare($insert_query);
            return $insert_stmt->execute([$user_id, $date, $cramps, $flow_level, $mood, $notes]);
        }
    }

    /**
     * Gets all symptom entries for a user within a given month.
     * @param int $user_id
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getSymptoms($user_id, $month, $year) {
        $start_of_month = date('Y-m-01', strtotime("$year-$month-01"));
        $end_of_month = date('Y-m-t', strtotime("$year-$month-01"));

        $query = "SELECT * FROM " . $this->symptomsTable . " WHERE user_id = ? AND date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $start_of_month, $end_of_month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
