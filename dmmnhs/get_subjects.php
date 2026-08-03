<?php
include 'includes/db.php';

$student_id = (int)($_GET['student_id'] ?? 0);
$teacher_id = (int)($_GET['teacher_id'] ?? 0);

header('Content-Type: application/json');

if($student_id && $teacher_id){
    $subjects_result = $conn->query("
        SELECT 
            s.id, 
            s.subject_name as name,
            COALESCE(
                (
                    SELECT g2.grade 
                    FROM grades g2 
                    WHERE g2.subject_id = s.id AND g2.student_id = {$student_id}
                    ORDER BY g2.date_submitted DESC
                    LIMIT 1
                ), 
                'N/A'
            ) as current_grade
        FROM subjects s
        WHERE s.teacher_id = {$teacher_id}
        ORDER BY s.subject_name
    ");

    $subjects = $subjects_result ? $subjects_result->fetch_all(MYSQLI_ASSOC) : [];
    echo json_encode($subjects);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing student_id or teacher_id']);
}
?>