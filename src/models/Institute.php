<?php
class Institute {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAll() {
        return $this->pdo->query("SELECT * FROM institutes")->fetchAll();
    }
    
    public function getForSelection() {
        return $this->pdo->query("SELECT * FROM institutes WHERE short_name != 'ОБЩЕЕ'")->fetchAll();
    }
    
    public function create($shortName, $fullName) {
        $stmt = $this->pdo->prepare("INSERT INTO institutes (short_name, full_name) VALUES (?, ?)");
        return $stmt->execute([$shortName, $fullName]);
    }
    
    public function delete($id) {
        // Проверяем, есть ли привязанные мероприятия
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM events WHERE institute_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['cnt'] > 0) {
            throw new Exception("Нельзя удалить институт: к нему привязано мероприятий: " . $result['cnt']);
        }
        
        return $this->pdo->prepare("DELETE FROM institutes WHERE id = ?")->execute([$id]);
    }
}
