<?php
class Participant {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getByEventId($eventId) {
        $stmt = $this->pdo->prepare("SELECT * FROM event_participants WHERE event_id = ?");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }
    
    public function saveForEvent($eventId, $participantsData) {
        $this->deleteByEventId($eventId);
        
        if (empty($participantsData) || !is_array($participantsData)) {
            return;
        }
        
        $sql = "INSERT INTO event_participants (event_id, participant_info, role_in_event, award_details, financial_reward) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($participantsData as $participant) {
            $info = trim($participant['info'] ?? '');
            if ($info === '') continue;
            
            $rewardRaw = $participant['reward'] ?? '0';
            if (!is_numeric($rewardRaw) || (float)$rewardRaw < 0) {
                throw new Exception("Приз участника должен быть положительным числом.");
            }
            
            $stmt->execute([
                $eventId,
                $info,
                trim($participant['role'] ?? 'Участник'),
                trim($participant['award'] ?? '') ?: null,
                (float)$rewardRaw
            ]);
        }
    }
    
    public function deleteByEventId($eventId) {
        return $this->pdo->prepare("DELETE FROM event_participants WHERE event_id = ?")->execute([$eventId]);
    }
}
