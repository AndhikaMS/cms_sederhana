<?php

namespace App\Models;

class InviteCodeModel extends BaseModel
{
    protected $table = 'invite_codes';

    /**
     * Get all invite codes with user information
     */
    public function getAllInviteCodes()
    {
        $query = "SELECT ic.*, u1.username as created_by_username, u2.username as used_by_username 
                  FROM invite_codes ic 
                  LEFT JOIN users u1 ON ic.created_by = u1.id 
                  LEFT JOIN users u2 ON ic.used_by = u2.id 
                  ORDER BY ic.created_at DESC";
        
        $result = $this->db->query($query);
        
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        return [];
    }

    /**
     * Generate new invite code
     */
    public function generateInviteCode($role, $expiresIn, $createdBy)
    {
        // Generate random code
        $code = bin2hex(random_bytes(16));
        
        // Calculate expiry date
        $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiresIn days"));
        
        $query = "INSERT INTO invite_codes (code, role, created_by, expires_at) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ssis', $code, $role, $createdBy, $expiresAt);
        
        return $stmt->execute();
    }

    /**
     * Delete invite code
     */
    public function deleteInviteCode($id)
    {
        $query = "DELETE FROM invite_codes WHERE id = ? AND is_used = 0";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $id);
        
        return $stmt->execute();
    }

    /**
     * Get invite code by code
     */
    public function getInviteCodeByCode($code)
    {
        $query = "SELECT * FROM invite_codes WHERE code = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $code);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    /**
     * Mark invite code as used
     */
    public function markAsUsed($codeId, $usedBy)
    {
        $query = "UPDATE invite_codes SET is_used = 1, used_by = ?, used_at = NOW() WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $usedBy, $codeId);
        
        return $stmt->execute();
    }

    /**
     * Check if invite code is valid
     */
    public function isInviteCodeValid($code)
    {
        $inviteCode = $this->getInviteCodeByCode($code);
        
        if (!$inviteCode) {
            return false;
        }
        
        // Check if already used
        if ($inviteCode['is_used']) {
            return false;
        }
        
        // Check if expired
        if (strtotime($inviteCode['expires_at']) < time()) {
            return false;
        }
        
        return true;
    }
} 