<?php
class Category {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll() {
        return $this->pdo->query("SELECT * FROM event_categories")->fetchAll();
    }
    
    public function create($name) {
        $stmt = $this->pdo->prepare("INSERT INTO event_categories (name) VALUES (?)");
        return $stmt->execute([$name]);
    }
    
    public function delete($id) {
        // Проверяем, есть ли привязанные мероприятия
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM events WHERE category_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['cnt'] > 0) {
            throw new Exception("Нельзя удалить категорию: к ней привязано мероприятий: " . $result['cnt']);
        }
        
        return $this->pdo->prepare("DELETE FROM event_categories WHERE id = ?")->execute([$id]);
    }
}
