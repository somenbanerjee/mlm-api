<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class AdminModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'admins';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['username', 'password', 'name'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function findAdminByUsername(string $username)
    {
        $admin = $this->asArray()->where(['username' => $username])->first();

        if (!$admin) {
            throw new Exception('Member does not exist for specified member code');
        }
        return $admin;
    }

    public function isAdminValid(string $username)
    {
        $admin = $this->asArray()->where(['username' => $username])->first();

        if (!$admin) {
            return false;
        }
        return true;
    }
}
