<?php
// models/Period.php

/**
 * The Period class is the model for handling all period-related database operations.
 */
class Period
{
    private $conn;
    private $periods_table = 'periods';
    private $symptoms_table = 'symptoms';

    /**
     * Constructor for the Period class.
     * @param PDO $db The database connection object.
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Adds a new period entry.
     *
     * @param int $user_id
     * @param string $start_date
     * @param string $end_date
     * @return bool
     */
    public function addPeriod($user_id, $start_date, $end_date)
    {
        $query = "INSERT INTO " . $this->periods_table . " (user_id, start_date, end_date) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $start_date, $end_date]);
    }

    /**
     * Updates an existing period entry.
     *
     * @param int $id
     * @param string $start_date
     * @param string $end_date
     * @param int $user_id
     * @return bool
     */
    public function updatePeriod($id, $start_date, $end_date, $user_id)
    {
        $query = "UPDATE " . $this->periods_table . " SET start_date = ?, end_date = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$start_date, $end_date, $id, $user_id]);
    }
    
    /**
     * Deletes a period entry.
     *
     * @param int $id
     * @param int $user_id
     * @return bool
     */
    public function deletePeriod($id, $user_id)
    {
        $query = "DELETE FROM " . $this->periods_table . " WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id, $user_id]);
    }

    /**
     * Retrieves all period entries for a given user.
     *
     * @param int $user_id
     * @return array
     */
    public function getAllPeriods($user_id)
    {
        $query = "SELECT * FROM " . $this->periods_table . " WHERE user_id = ? ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retrieves all period entries for a given user that overlap with a specific month.
     *
     * @param int $user_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function getPeriodsOverlappingMonth($user_id, $start_date, $end_date)
    {
        $query = "SELECT * FROM " . $this->periods_table . " WHERE user_id = ? AND start_date <= ? AND end_date >= ? ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $end_date, $start_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adds a new symptom entry.
     *
     * @param int $user_id
     * @param string $date
     * @param string|null $cramps
     * @param string|null $flow_level
     * @param string|null $mood
     * @param string|null $notes
     * @return bool
     */
    public function addSymptom($user_id, $date, $cramps, $flow_level, $mood, $notes)
    {
        $query = "INSERT INTO " . $this->symptoms_table . " (user_id, date, cramps, flow_level, mood, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $date, $cramps, $flow_level, $mood, $notes]);
    }

    /**
     * Updates an existing symptom entry.
     *
     * @param int $id
     * @param int $user_id
     * @param string|null $cramps
     * @param string|null $flow_level
     * @param string|null $mood
     * @param string|null $notes
     * @return bool
     */
    public function updateSymptom($id, $user_id, $cramps, $flow_level, $mood, $notes)
    {
        $query = "UPDATE " . $this->symptoms_table . " SET cramps = ?, flow_level = ?, mood = ?, notes = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$cramps, $flow_level, $mood, $notes, $id, $user_id]);
    }

    /**
     * Deletes a symptom entry.
     *
     * @param int $id
     * @param int $user_id
     * @return bool
     */
    public function deleteSymptom($id, $user_id)
    {
        $query = "DELETE FROM " . $this->symptoms_table . " WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id, $user_id]);
    }

    /**
     * Retrieves all symptom entries for a given user and month.
     *
     * @param int $user_id
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getSymptoms($user_id, $month, $year)
    {
        $query = "SELECT * FROM " . $this->symptoms_table . " WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ? ORDER BY date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $month, $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Retrieves a single symptom entry for a specific date.
     *
     * @param int $user_id
     * @param string $date
     * @return array|false
     */
    public function getSymptomByDate($user_id, $date)
    {
        $query = "SELECT * FROM " . $this->symptoms_table . " WHERE user_id = ? AND date = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Calculates the average cycle length based on user's past period data.
     *
     * @param int $user_id
     * @return int|null
     */
    public function getAverageCycleLength($user_id)
    {
        $query = "SELECT start_date FROM " . $this->periods_table . " WHERE user_id = ? ORDER BY start_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($periods) < 2) {
            return null;
        }

        $cycle_lengths = [];
        for ($i = 0; $i < count($periods) - 1; $i++) {
            $current_start = new DateTime($periods[$i]['start_date']);
            $prev_start = new DateTime($periods[$i + 1]['start_date']);
            $diff = $current_start->diff($prev_start);
            $cycle_lengths[] = $diff->days;
        }

        if (count($cycle_lengths) === 0) {
            return null;
        }

        return round(array_sum($cycle_lengths) / count($cycle_lengths));
    }
}
