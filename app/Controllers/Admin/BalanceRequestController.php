<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FundRequestModel;
use CodeIgniter\I18n\Time;
use Config\Services;
use Exception;


class BalanceRequestController extends BaseController
{
    use ResponseTrait;

    protected $fundRequestModel;
    public function __construct()
    {
        helper(['general']);
        $this->fundRequestModel = new FundRequestModel();
    }

    public function getBalanceRequest()
    {



        $page = $this->request->getVar('page') ?? 1;
        $size = $this->request->getVar('size') ?? DEFAULT_PAGE_SIZE;
        $oderBy = $this->request->getVar('order') ?? 'DESC';


        // Filter
        $paymentMode = $this->request->getVar('paymentMode') ?? 'ALL';
        $dateFrom = $this->request->getVar('dateFrom') ?? '';
        $dateTo = $this->request->getVar('dateTo') ?? '';

        if ($paymentMode !== 'ALL') {
            $this->fundRequestModel
                ->where('payment_mode', $paymentMode);
        }

        if (!empty($dateFrom) && !empty($dateTo)) {
            $this->fundRequestModel
                ->where("DATE(`created_at`) BETWEEN '" . $dateFrom . "' AND '" . $dateFrom . "' ");
        }
        $this->fundRequestModel
            ->orderBy('id', $oderBy);

        $totalElements = $this->fundRequestModel->countAllResults(false);
        $fundRequests = $this->fundRequestModel->paginate($size);

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

    /**
     * Update the balance request status to accept / reject.
     * Check if the input id is present and pending.
     * On reject just update the status.
     * On accept  update the status and the wallet balance of that member.
     */
    public function updateRequestStatus(): string
    {
        $rules = [
            'balanceRequestId' => 'required|is_natural_no_zero',
            'action' => 'required|in_list[accept,reject]',
        ];
        if ($this->validate($rules)) {
            $balanceRequestId = $this->request->getVar('balanceRequestId');
            $action = $this->request->getVar('action');


            $fundRequest = $this->fundRequestModel
                ->select('member_id', 'amount')
                ->asArray()->where(['id' => $balanceRequestId, 'fundRequest' => 'pending'])->first();
            if ($fundRequest) {
                if ($action === 'reject') {
                    $this->fundRequestModel
                        ->set('status', $action)
                        ->where('id', $balanceRequestId)
                        ->update();
                    $response = [
                        'status' => 'success',
                        'data' => [
                            'amount' => $fundRequest['amount'],
                            'action' => $action
                        ],
                        'message' => 'Balance request is rejected successfully.'
                    ];
                    return $this->respond($response, ResponseInterface::HTTP_NOT_FOUND);
                } else {
                    
                }
            } else {
                $response = [
                    'status' => 'error',
                    'data' => null,
                    'message' => 'No pending request found.'
                ];
                return $this->respond($response, ResponseInterface::HTTP_NOT_FOUND);
            }
        } else {
            $response = [
                'status' => 'error',
                'data' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs.'
            ];
            return $this->respond($response, ResponseInterface::HTTP_CONFLICT);
        }
        return "ok";
    }
}
