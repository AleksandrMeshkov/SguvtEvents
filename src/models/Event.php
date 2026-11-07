<?php
class Event {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAllWithFilters($dateFrom = null, $dateTo = null, $instituteId = null, $facultyId = null) {
        $query = "SELECT e.*, i.short_name, f.name as faculty_name, c.name as category_name,
            (SELECT GROUP_CONCAT(participant_info SEPARATOR ', ') FROM event_participants WHERE event_id = e.id) as parts_list,
            (SELECT COUNT(*) FROM event_participants WHERE event_id = e.id) as parts_count
            FROM events e
            JOIN institutes i ON e.institute_id = i.id
            LEFT JOIN faculties f ON e.faculty_id = f.id
            JOIN event_categories c ON e.category_id = c.id WHERE 1=1";
        
        $params = [];
        if ($dateFrom) {
            $query .= " AND e.date_start >= ?";
            $params[] = date('Y-m-d', strtotime($dateFrom));
        }
        if ($dateTo) {
            $query .= " AND e.date_start <= ?";
            $params[] = date('Y-m-d', strtotime($dateTo));
        }
        if ($instituteId) {
            $query .= " AND e.institute_id = ?";
            $params[] = $instituteId;
        }
        if ($facultyId) {
            $query .= " AND e.faculty_id = ?";
            $params[] = $facultyId;
        }
        $query .= " ORDER BY e.date_start DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $sql = "INSERT INTO events (title, category_id, institute_id, faculty_id, date_start, date_end, location, organizer, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->pdo->prepare($sql)->execute([
            $data['title'],
            $data['category_id'],
            $data['institute_id'],
            $data['faculty_id'] ?? null,
            $data['date_start'],
            $data['date_end'],
            $data['location'],
            $data['organizer'],
            $data['description']
        ]);
        return $this->pdo->lastInsertId();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE events SET title=?, category_id=?, institute_id=?, faculty_id=?, date_start=?, date_end=?, location=?, organizer=?, description=? WHERE id=?";
        return $this->pdo->prepare($sql)->execute([
            $data['title'],
            $data['category_id'],
            $data['institute_id'],
            $data['faculty_id'] ?? null,
            $data['date_start'],
            $data['date_end'],
            $data['location'],
            $data['organizer'],
            $data['description'],
            $id
        ]);
    }
    
    public function delete($id) {
        return $this->pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);
    }
    
    public function getMetrics($dateFrom, $dateTo, $instituteId = null, $facultyId = null) {
        $sql = "SELECT
            COUNT(DISTINCT e.id) as ev_count,
            SUM(CASE WHEN p.role_in_event IN ('Победитель', 'Призер') THEN 1 ELSE 0 END) as winners_count,
            SUM(CASE WHEN p.award_details IS NOT NULL AND p.award_details != '' AND p.award_details != 'ПУСТО' AND p.award_details != '—' THEN 1 ELSE 0 END) as awards_count
            FROM events e LEFT JOIN event_participants p ON e.id = p.event_id
            WHERE e.date_start >= ? AND e.date_start <= ?";
        $params = [$dateFrom, $dateTo];
        if ($instituteId) { $sql .= " AND e.institute_id = ?"; $params[] = $instituteId; }
        if ($facultyId) { $sql .= " AND e.faculty_id = ?"; $params[] = $facultyId; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function getReportData($dateFrom, $dateTo, $instituteId = null, $facultyId = null) {
        $sql = "SELECT e.title, e.date_start, i.short_name, f.name as faculty_name, c.name as cat_name, p.participant_info, p.role_in_event, p.award_details
                FROM events e
                JOIN institutes i ON e.institute_id = i.id
                LEFT JOIN faculties f ON e.faculty_id = f.id
                JOIN event_categories c ON e.category_id = c.id
                LEFT JOIN event_participants p ON e.id = p.event_id
                WHERE e.date_start >= ? AND e.date_start <= ?";
        $params = [$dateFrom, $dateTo];
        if ($instituteId) { $sql .= " AND e.institute_id = ?"; $params[] = $instituteId; }
        if ($facultyId) { $sql .= " AND e.faculty_id = ?"; $params[] = $facultyId; }
        $sql .= " ORDER BY e.date_start ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
