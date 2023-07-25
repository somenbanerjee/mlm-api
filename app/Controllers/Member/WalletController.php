<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Config\Services;
use App\Models\FundWalletBalanceModel;
use App\Models\FundWalletTransactionModel;

class WalletController extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        helper(['general']);
    }

    public function getBalance()
    {
        $rules = [
            'wallet' => [
                'rules' => 'required|in_list[fund]',
                'errors' => [
                    'in_list' => 'Wallet name is invalid.'
                ],
            ]
        ];

        if ($this->validate($rules)) {
            $wallet = $this->request->getVar('wallet');
            if ($wallet === 'fund') {
                $walletBalanceModel = new FundWalletBalanceModel();
            }

            $jwt = $this->request->header("Authorization");
            $member = getMemberFromJWT($jwt);
            $memberId = $member['memberId'];

            $result = $walletBalanceModel->select('balance')->where(['member_id' => $memberId])->first();
            $response = [
                'status' => 'success',
                'data' => [
                    'balance' => $result['balance']
                ],
                'message' => 'Balance found.'
            ];
            return $this->respond($response, ResponseInterface::HTTP_OK);
        } else {
            $response = [
                'status' => 'error',
                'data' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs.'
            ];
            return $this->respond($response, ResponseInterface::HTTP_CONFLICT);
        }
    }

    public function getTransactions()
    {
        $rules = [
            'wallet' => [
                'rules' => 'required|in_list[fund]',
                'errors' => [
                    'in_list' => 'Wallet name is invalid.'
                ],
            ],
            'dateFrom' => 'permit_empty|valid_date|required_with[dateTo]',
            'dateTo' => 'permit_empty|valid_date|required_with[dateFrom]',
            'page' => 'permit_empty|is_natural_no_zero',
            'size' => 'permit_empty|is_natural_no_zero',
            'oderBy' => 'permit_empty|in_list[desc,asc]',
        ];

        if ($this->validate($rules)) {
            $wallet = $this->request->getVar('wallet');

            if ($wallet === 'fund') {
                $walletTransactionModel = new FundWalletTransactionModel();
            }

            $page = $this->request->getVar('page') ?? 1;
            $size = $this->request->getVar('size') ?? DEFAULT_PAGE_SIZE;
            $oderBy = $this->request->getVar('order') ?? 'DESC';

            // Filters
            $dateFrom = $this->request->getVar('dateFrom') ?? '';
            $dateTo = $this->request->getVar('dateTo') ?? '';

            $jwt = $this->request->header("Authorization");
            $member = getMemberFromJWT($jwt);
            $memberId = $member['memberId'];

            if (!empty($dateFrom) && !empty($dateTo)) {
                $walletTransactionModel
                    ->where("DATE(`created_at`) BETWEEN '" . $dateFrom . "' AND '" . $dateFrom . "' ");
            }
            $walletTransactionModel
                ->where('member_id', $memberId)
                ->orderBy('id', $oderBy);

            $totalElements = $walletTransactionModel->countAllResults(false);
            $walletTransactions = $walletTransactionModel->paginate($size);

            $pagination = $totalElements ? pagination($page, $size, $totalElements) : '';

            $response = [
                'status' => 'success',
                'data' => [
                    'list' => $walletTransactions,
                    'pagination' =>  $pagination
                ],
                'message' => 'Data found.'
            ];
            return $this->respond($response, ResponseInterface::HTTP_OK);
        } else {
            $response = [
                'status' => 'error',
                'data' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs.'
            ];
            return $this->respond($response, ResponseInterface::HTTP_CONFLICT);
        }
    }
}
