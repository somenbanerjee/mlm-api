<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;
use App\Validation\CommonRules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var string[]
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
        CommonRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------

    // Balance request
    public $balanceRequestCash = [
        'transactionDate' => [
            'label' => 'Transaction Date',
            'rules' => 'required|valid_date',
        ],
        'toPersonName' => [
            'label' => 'To Person Name',
            'rules' => 'required|min_length[3]',
        ],
    ];

    public $balanceRequestBank = [
        'transactionDate' => [
            'label' => 'Transaction Date',
            'rules' => 'required|valid_date',
        ],
        'fromBankAcNo' => [
            'label' => 'From Bank AC No',
            'rules' => 'required|numeric|min_length[6]',
        ],
        'fromAcHolderName' => [
            'label' => 'From AC Holder Name',
            'rules' => 'required|min_length[3]',
        ],
        'fromBankName' => [
            'label' => 'From Bank Name',
            'rules' => 'required|min_length[3]',
        ],
        'fromIfsc' => [
            'label' => 'From IFSC',
            'rules' => 'required|min_length[4]',
        ],
    ];

    public $balanceRequestUPI = [
        'transactionDate' => [
            'label' => 'Transaction Date',
            'rules' => 'required|valid_date',
        ],
        'fromUpiId' => [
            'label' => 'From UPI Id',
            'rules' => 'required|min_length[6]',
        ],
        'transactionId' => [
            'label' => 'Transaction Id',
            'rules' => 'required|min_length[6]',
        ],
    ];
    // .Balance request
}
