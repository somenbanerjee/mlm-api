<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FundRequestModel;
use CodeIgniter\I18n\Time;
use Config\Services;

class BalanceRequestController extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        helper(['general']);
    }
    public function balanceRequest()
    {
        $validation = Services::validation();

        $rules = [
            'amount' => 'required|is_natural_no_zero|integer',
            'paymentMode' => 'required'
        ];
        if (!$this->validate($rules)) {
            $response = [
                'status' => 'error',
                'data' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs.'
            ];
            return $this->respond($response, ResponseInterface::HTTP_CONFLICT);
        }

        $paymentMode = $this->request->getVar('paymentMode');

        if ($paymentMode === 'CASH') {
            $postData = [
                'to_person_name' => $this->request->getVar('toPersonName'),
                'transaction_date' => $this->request->getVar('transactionDate'),
            ];

            $rules = $validation->getRuleGroup('balanceRequestCash');
        } elseif ($paymentMode === 'BANK') {
            $postData = [
                'from_bank_ac_no' => $this->request->getVar('fromBankAcNo'),
                'from_ac_holder_name' => $this->request->getVar('fromAcHolderName'),
                'from_bank_name' => $this->request->getVar('fromBankName'),
                'from_ifsc' => $this->request->getVar('fromIfsc'),
                'transaction_date' => $this->request->getVar('transactionDate'),
            ];

            $rules = $validation->getRuleGroup('balanceRequestBank');
        } elseif ($paymentMode === 'UPI') {
            $postData = [
                'from_upi_id' => $this->request->getVar('fromUpiId'),
                'transaction_id' => $this->request->getVar('transactionId'),
                'transaction_date' => $this->request->getVar('transactionDate'),
            ];
            $rules = $validation->getRuleGroup('balanceRequestUPI');
        } else {
            $response = [
                'status' => 'error',
                'data' => null,
                'message' => 'Invalid payment mode.'
            ];
            return $this->respond($response, ResponseInterface::HTTP_CONFLICT);
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => 'error',
                'data' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs.'
            ];
            return $this->respond($response, ResponseInterface::HTTP_CONFLICT);
        }

        $jwt = $this->request->header("Authorization");
        $member = getMemberFromJWT($jwt);
        $memberId = $member['memberId'];

        $requestToken = generateTransactionId('BR');
        $amount = $this->request->getVar('amount');

        $postData['amount'] = $amount;
        $postData['member_id'] = $memberId;
        $postData['payment_mode'] = $paymentMode;
        $postData['request_token'] = $requestToken;
        $postData['created_by'] = $memberId;
        $postData['created_at'] = new Time('now');

        $fundRequestModel = new FundRequestModel();
        if ($fundRequestModel->save($postData)) {
            //print_r($fundRequestModel->getLastQuery());
            //$fundRequestModel->getLastQuery()->getQuery();
            $response = [
                'status' => 'success',
                'data' => [
                    'amount' => $amount,
                    'requestToken' => $requestToken,
                ],
                'message' => 'Balance request successful.'
            ];
            return $this->respond($response, ResponseInterface::HTTP_CREATED);
        } else {
            $response = [
                'status' => 'error',
                'data' => null,
                'message' => 'Something went wrong.'
            ];
            return $this->respond($response, ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getBalanceRequest()
    {

        $fundRequestModel = new FundRequestModel();

        $page = $this->request->getVar('page') ?? 1;
        $size = $this->request->getVar('size') ?? DEFAULT_PAGE_SIZE;
        $oderBy = $this->request->getVar('order') ?? 'DESC';

        $jwt = $this->request->header("Authorization");
        $member = getMemberFromJWT($jwt);
        $memberId = $member['memberId'];

        // Filter
        $paymentMode = $this->request->getVar('paymentMode') ?? 'ALL';
        $dateFrom = $this->request->getVar('dateFrom') ?? '';
        $dateTo = $this->request->getVar('dateTo') ?? '';

        if ($paymentMode !== 'ALL') {
            $fundRequestModel
                ->where('payment_mode', $paymentMode);
        }

        if (!empty($dateFrom) && !empty($dateTo)) {
            $fundRequestModel
                ->where("DATE(`created_at`) BETWEEN '" . $dateFrom . "' AND '" . $dateFrom . "' ");
        }
        $fundRequestModel
            ->where('member_id', $memberId)
            ->orderBy('id', $oderBy);

        $totalElements = $fundRequestModel->countAllResults(false);
        $fundRequests = $fundRequestModel->paginate($size);

        $pagination = $totalElements ? pagination($page, $size, $totalElements) : '';

        $response = [
            'status' => 'success',
            'data' => [
                'list' => $fundRequests,
                'pagination' =>  $pagination
            ],
            'message' => 'Data found.'
        ];
        return $this->respond($response, ResponseInterface::HTTP_OK);
    }
}
