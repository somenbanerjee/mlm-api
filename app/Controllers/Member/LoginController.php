<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;

use App\Models\MemberModel;

class LoginController extends BaseController
{
    use ResponseTrait;

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

                $key = getenv('JWT_SECRET');
                $iss = getenv('JWT_ISSUER');
                $iat = time();
                $exp = $iat + getenv('JWT_EXPIRES_IN');

                $payload = array(
                    'iss' => $iss,
                    'iat' => $iat,
                    'exp' => $exp,
                    'data' => array(
                        'user_type' => 'MEMBER',
                        'member_id' => $member['member_id'],
                        'member_code' => $member['member_code'],
                        'name' => $member['name'],
                    )
                );

                try {
                    $token = JWT::encode($payload, $key, 'HS256');
                    $response = [
                        'status' => 'success',
                        'token' => $token,
                        'message' => 'Login Successful.'
                    ];
                    return $this->respond($response, 200);
                } catch (\Exception $e) {
                    $response = [
                        'status' => 'error',
                        'data' => $e->getMessage(),
                        'message' => 'Login Successful.'
                    ];
                    return $this->respond($response, 200);
                }
            } else {
                $response = [
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Invalid username or password.'
                ];
                return $this->respond($response, 401);
            }
        } else {
            $response = [
                'status' => 'error',
                'data' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs'
            ];
        }
        return $this->respond($response, 409);
    }
}
