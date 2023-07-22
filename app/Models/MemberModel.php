<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class MemberModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'members';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'member_code',
        'password',
        'name',
        'intro_id',
        'intro_level',
        'intro_tree',
        'name',
        'gender',
        'mobile',
        'email',
        'pan',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

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
    protected $beforeInsert   = ['hashPassword'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['hashPassword'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }

        return $data;
    }

    public function findMemberByMemberCode(string $memberCode)
    {
        $member = $this->asArray()->where(['member_code' => $memberCode])->first();

        if (!$member) {
            throw new Exception('Member does not exist for specified member code');
        }
        return $member;
    }

    public function isMemberValid(string $memberCode)
    {
        $member = $this->asArray()->where(['member_code' => $memberCode])->first();

        if (!$member) {
            return false;
        }
        return true;
    }
}
