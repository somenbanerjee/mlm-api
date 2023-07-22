<?php

namespace App\Models;

use CodeIgniter\Model;

class FundRequestModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'fund_requests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        "request_token",
        "member_id",
        "amount",
        "payment_mode",
        "to_person_name",
        "from_bank_ac_no",
        "from_ac_holder_name",
        "from_bank_name",
        "from_ifsc",
        "from_upi_id",
        "transaction_id",
        "transaction_date",
        "request_status",
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    // Dates
    protected $useTimestamps = false;
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
}
