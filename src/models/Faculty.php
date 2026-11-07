<?php
class Faculty {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll() {
        return $this->pdo->query("SELECT f.*, i.short_name as institute_short 
            FROM faculties f 
            JOIN institutes i ON f.institute_id = i.id 
            ORDER BY i.short_name, f.name")->fetchAll();
    }
    
    public function getByInstituteId($instituteId) {
        $stmt = $this->pdo->prepare("SELECT * FROM faculties WHERE institute_id = ? ORDER BY name");
        $stmt->execute([$instituteId]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM faculties WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($name, $instituteId) {
        $stmt = $this->pdo->prepare("INSERT INTO faculties (name, institute_id) VALUES (?, ?)");
        return $stmt->execute([$name, $instituteId]);
    }
    
    public function delete($id) {
        // Проверяем, есть ли привязанные мероприятия
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM events WHERE faculty_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['cnt'] > 0) {
            throw new Exception("Нельзя удалить факультет: к нему привязано мероприятий: " . $result['cnt']);
        }
        
        return $this->pdo->prepare("DELETE FROM faculties WHERE id = ?")->execute([$id]);
    }
}
