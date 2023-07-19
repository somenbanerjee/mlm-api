<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MemberModel;
use Exception;

class LoginController extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        helper('jwt');
    }

    public function index()
    {
        $rules = [
            'username' => 'required|exact_length[8]',
            'password' => 'required',
        ];

        if ($this->validate($rules)) {
            $username = $this->request->getVar('username');
            $password = $this->request->getVar('password');

            $memberModel = new MemberModel();
            $member = $memberModel->where('member_code', $username)->first();

            if ($member && password_verify($password, $member['password'])) {

                try {
                    $token = getSignedJWTForMember($member);

                    $response = [
                        'status' => 'success',
                        'token' => $token,
                        'message' => 'Login Successful.'
                    ];
                    return $this->respond($response, ResponseInterface::HTTP_OK);
                } catch (Exception $ex) {
                    $response = [
                        'status' => 'error',
                        'data' => $ex->getMessage(),
                        'message' => 'Failed to generate token.'
                    ];
                    return $this->respond($response, ResponseInterface::HTTP_UNAUTHORIZED);
                }
            } else {
                $response = [
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Invalid username or password.'
                ];
                return $this->respond($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
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
