<?php

use App\Models\MemberModel;
use App\Models\AdminModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


/**
 * Common functions admin and member
 */
if (!function_exists("getJwtFromHeader")) {
    function getJwtFromHeader(string $bearer_token): string
    {
        if (empty($bearer_token))
            throw new Exception('JWT is missing in the request');

        return explode('Bearer ', $bearer_token)[1];
    }
}


/**
 * Functions member only
 */
if (!function_exists("validateJwtForMember")) {
    function validateJWTForMember(string $token)
    {
        $key = getenv('JWT_SECRET');
        $decoded_token = JWT::decode($token, new Key($key, 'HS256'));

        $MemberModel = new MemberModel();
        $member = $MemberModel->findMemberByMemberCode($decoded_token->data->memberCode);
        return  $member;
    }
}

if (!function_exists("getSignedJWTForMember")) {
    function getSignedJWTForMember(array $member): string
    {
        $key = getenv('JWT_SECRET');
        $iss = getenv('JWT_ISSUER');
        $iat = time();
        $exp = $iat + getenv('JWT_EXPIRES_IN');

        $payload = array(
            'iss' => $iss,
            'iat' => $iat,
            'exp' => $exp,
            'data' => array(
                'userType' => 'MEMBER',
                'memberId' => $member['member_id'],
                'memberCode' => $member['member_code'],
                'memberName' => $member['name'],
            )
        );
        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }
}

if (!function_exists("getMemberFromToken")) {
    function getMemberFromJWT(string $token): array
    {
        $token = getJwtFromHeader($token);
        $key = getenv('JWT_SECRET');

        $decoded_token = JWT::decode($token, new Key($key, 'HS256'));
        if (!$decoded_token)
            throw new Exception('Unable to decode data from JWT.');

        $memberData = array(
            'memberId' => $decoded_token->data->memberId,
            'memberCode' => $decoded_token->data->memberCode,
            'memberName' => $decoded_token->data->memberName,
        );
        return $memberData;
    }
}


/**
 * Functions admin only
 */

if (!function_exists("validateJwtForAdmin")) {
    function validateJWTForAdmin(string $token)
    {
        $key = getenv('JWT_SECRET');
        $decoded_token = JWT::decode($token, new Key($key, 'HS256'));

        $adminModel = new AdminModel();
        $admin = $adminModel->findAdminByUsername($decoded_token->data->username);
        return  $admin;
    }
}

if (!function_exists("getSignedJWTForAdmin")) {
    function getSignedJWTForAdmin(array $admin): string
    {
        $key = getenv('JWT_SECRET');
        $iss = getenv('JWT_ISSUER');
        $iat = time();
        $exp = $iat + getenv('JWT_EXPIRES_IN');

        $payload = array(
            'iss' => $iss,
            'iat' => $iat,
            'exp' => $exp,
            'data' => array(
                'userType' => 'ADMIN',
                'id' => $admin['id'],
                'username' => $admin['username'],
                'name' => $admin['name'],
            )
        );
        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }
}

if (!function_exists("getAdminFromToken")) {
    function getAdminFromToken(string $token): array
    {
        $token = getJwtFromHeader($token);
        $key = getenv('JWT_SECRET');

        $decoded_token = JWT::decode($token, new Key($key, 'HS256'));
        if (!$decoded_token)
            throw new Exception('Unable to decode data from JWT.');

        $adminData = array(
            'id' => $decoded_token->data->id,
            'username' => $decoded_token->data->username,
            'username' => $decoded_token->data->username,
        );
        return $adminData;
    }
}
