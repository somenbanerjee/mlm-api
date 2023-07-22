<?php

use App\Models\MemberModel;

if (!function_exists("pagination")) {
    function pagination($page, $size, $totalElements): array
    {
        $currentPage = (int) $page;
        $totalPages = ceil((int)$totalElements / (int)$size);
        $firstPage = ($currentPage === 1);
        $lastPage = ((int)$currentPage === (int)$totalPages);
        $previousPage = $firstPage ? null : $currentPage - 1;
        $nextPage = $lastPage ? null : $currentPage + 1;
        return [
            'currentPage' => $currentPage,
            'nextPage' => $nextPage,
            'previousPage' => $previousPage,
            'firstPage' => $firstPage,
            'lastPage' => $lastPage,
            'pageSize' => (int)$size,
            'totalElements' => (int)$totalElements,
            'totalPages' => $totalPages,
        ];
    }
}

if (!function_exists("generateTransactionId")) {
    function generateTransactionId(string $prefix): string
    {
        return $prefix . time() . uniqid();
    }
}


if (!function_exists("generateIntroTree")) {
    function generateIntroTree(string $introCode): string
    {
        if (strtoupper($introCode) === 'ADMIN')
            return $introCode;

        $memberModel = new MemberModel();
        $memberData = $memberModel->findMemberByMemberCode($introCode);
        return $memberData['intro_tree'] . $introCode . ",";
    }
}

if (!function_exists("generateIntroLevel")) {
    function generateIntroLevel(string $introCode): int
    {
        if (strtoupper($introCode) === 'ADMIN')
            return 1;

        $memberModel = new MemberModel();
        $memberData = $memberModel->findMemberByMemberCode($introCode);
        return $memberData['intro_level'] + 1;
    }
}

if (!function_exists("generateNumber")) {
    function generateNumber($digits)
    {
        return random_int(10 ** ($digits - 1), (10 ** $digits) - 1);
    }
}


if (!function_exists("generateMemberCode")) {
    function generateMemberCode()
    {
        $memberModel = new MemberModel();
        do {
            $random_numbers = generateNumber(6);
            $memberCode = USERNAME_PREFIX . $random_numbers;

            $valid = $memberModel->isMemberValid($memberCode);
        } while ($valid);

        return $memberCode;
    }
}
