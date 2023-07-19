<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use App\Models\MemberModel;
use Exception;

class RegistrationController extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        helper(['jwt', 'general']);
    }

    public function index()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[20]',
            'mobile' => 'required|numeric|exact_length[10]',
            'email' => [
                'rules' => 'required|min_length[6]|valid_email|is_unique[members.email]',
                'errors' => [
                    'is_unique' => 'Email address is already registered with us.'
                ],
            ],
            'pan' => 'required|exact_length[10]|valid_pan',
            'password' => 'required|min_length[6]|max_length[255]',
            'confirm_password' => [
                'rules' => 'required|matches[password]',
                'errors' => [
                    'matches' => 'Confirm password does not match the password.'
                ],
            ],
        ];

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

        $introCode = $member['memberCode'];
        $introId = $member['memberId'];
        $introTree = generateIntroTree($introCode);
        $introLevel = generateIntroLevel($introCode);
        $memberCode = generateMemberCode();

        $password = $this->request->getVar('password');
        $name = $this->request->getVar('name');
        $mobile = $this->request->getVar('mobile');
        $email = $this->request->getVar('email');


        $newMemberData = [
            'member_code' => $memberCode,
            'password' => $password,
            'intro_id' => $introId,
            'intro_level' => $introLevel,
            'intro_tree' => $introTree,
            'name' => $name,
            'mobile' => $mobile,
            'email' => $email,
            'created_by' => $introCode,
            'created_on' => new Time('now')
        ];

        $memberModel = new MemberModel();
        if ($memberModel->insert($newMemberData)) {
            $response = [
                'status' => 'success',
                'data' => [
                    'memberCode' => $memberCode,
                    'memberName' => $name,
                    'email' => $email,
                ],
                'message' => 'Registration Successful.'
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
}
