<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
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
            'fromDate' => 'permit_empty|valid_date|required_with[toDate]',
            'toDate' => 'permit_empty|valid_date|required_with[fromDate]',
            'page' => 'permit_empty|is_natural_no_zero',
            'size' => 'permit_empty|is_natural_no_zero',
            'oderBy' => 'permit_empty|in_list[desc,asc]',
        ];

        if ($this->validate($rules)) {
            $wallet = $this->request->getVar('wallet');

            if ($wallet === 'fund') {
                $walletTransactionModel = new FundWalletTransactionModel();
            }

            // Filters
            $perPage = $this->request->getVar('size') ?? DEFAULT_PAGE_SIZE;
            $oderBy = $this->request->getVar('order') ?? 'DESC';

            $fromDate = $this->request->getVar('fromDate') ?? '';
            $toDate = $this->request->getVar('toDate') ?? '';

            $jwt = $this->request->header("Authorization");
            $member = getMemberFromJWT($jwt);
            $memberId = $member['memberId'];

            if (!empty($fromDate) && !empty($toDate)) {
                $walletTransactionModel
                    ->where("DATE(`created_at`) BETWEEN '" . $fromDate . "' AND '" . $fromDate . "' ");
            }
            $walletTransactionModel
                ->where('member_id', $memberId)
                ->orderBy('id', $oderBy);

            $walletTransactions = $walletTransactionModel->paginate($perPage);

            // Load pager instance
            $pager = $walletTransactionModel->pager;
            $totalElements = $pager->getTotal();

            $pagination = $totalElements ? generatePagination($pager) : '';

            $response = [
                'status' => 'success',
                'data' => $walletTransactions,
                'pagination' =>  $pagination,
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
