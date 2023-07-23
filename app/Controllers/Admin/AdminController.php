<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AdminModel;
use Exception;

class AdminController extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        helper('jwt');
    }

    public function login()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if ($this->validate($rules)) {
            $username = $this->request->getVar('username');
            $password = $this->request->getVar('password');

            $adminModel = new AdminModel();
            $admin = $adminModel->where('username', $username)->first();

            if ($admin && password_verify($password, $admin['password'])) {

                try {
                    $token = getSignedJWTForAdmin($admin);

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
