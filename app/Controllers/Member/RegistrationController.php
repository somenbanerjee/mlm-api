<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;

class RegistrationController extends BaseController
{
    public function index()
    {
        echo "reg";
        /* $rules = [
            'name' => 'required|min_length[3]|max_length[20]',
            'mobile' => 'required|numeric|exact_length[10]',
            'email' => [
                'rules' => 'required|min_length[6]|valid_email|is_unique[members.email]',
                'errors' => [
                    'is_unique' => 'Email address is already registered with us.'
                ],
            ],
            'pan_no' => 'required|exact_length[10]|valid_pan',
            'password' => 'required|min_length[8]|max_length[255]',
            'confirm_password' => [
                'rules' => 'required|matches[password]',
                'errors' => [
                    'matches' => 'Confirm password does not match the password.'
                ],
            ],
        ]; */
    }
}
