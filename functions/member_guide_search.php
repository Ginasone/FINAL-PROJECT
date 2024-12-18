<?php
function searchMembers($connection, $search = '') {
    $sql = "SELECT gm.*, kg.group_name 
            FROM group_members gm
            JOIN kpop_groups kg ON gm.group_id = kg.group_id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $sql .= " AND (
            gm.stage_name LIKE ? 
            OR gm.real_name LIKE ? 
            OR kg.group_name LIKE ? 
            OR gm.nationality LIKE ?
            OR gm.position LIKE ?
        )";
        
        $searchParam = "%{$search}%";
        $params = [
            $searchParam, 
            $searchParam, 
            $searchParam, 
            $searchParam, 
            $searchParam
        ];
        $types = 'sssss';
    }
    
    $stmt = $connection->prepare($sql);
    
    if (!empty($search)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
    
    return $members;
}

function searchMemberGuides($connection, $search = '') {
    $sql = "SELECT c.*, ct.type_name, kg.group_name, 
                   (SELECT GROUP_CONCAT(DISTINCT stage_name) 
                    FROM group_members gm 
                    WHERE gm.group_id = c.group_id) AS group_members
            FROM content c
            JOIN content_types ct ON c.content_type_id = ct.content_type_id
            JOIN kpop_groups kg ON c.group_id = kg.group_id
            WHERE ct.type_name = 'Member Guide'";
    
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $sql .= " AND (
            c.title LIKE ? 
            OR kg.group_name LIKE ? 
            OR EXISTS (
                SELECT 1 FROM group_members gm 
                WHERE gm.group_id = c.group_id 
                AND (gm.stage_name LIKE ? OR gm.real_name LIKE ?)
            )
        )";
        
        $searchParam = "%{$search}%";
        $params = [
            $searchParam, 
            $searchParam, 
            $searchParam,
            $searchParam
        ];
        $types = 'ssss';
    }
    
    $stmt = $connection->prepare($sql);
    
    if (!empty($search)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $guides = [];
    while ($row = $result->fetch_assoc()) {
        $guides[] = $row;
    }
    
    return $guides;
}
?>