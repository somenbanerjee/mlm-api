<?php

namespace App\Validation;

use App\Models\MemberModel;


class CommonRules
{

    public function validateUser(string $str, string $fields, array $data): bool
    {
        $model = new MemberModel();
        $member = $model->where('member_id', $data['username'])
            ->first();
        if (!$member)
            return false;

        return password_verify($data['password'], $member['password']);
    }

    public function valid_pan($str = null, ?string &$error = null): bool
    {
        $pattern = '/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/';
        $error = "Please enter a valid pan number.";
        if (preg_match($pattern, $str)) {
            $firstStringSet = 'CPHFATBLJG';
            $firstString = ucfirst(substr($str, 3, 1));
            return (strpos($firstStringSet, $firstString)) ? true : false;
        }
        return false;
    }
}
