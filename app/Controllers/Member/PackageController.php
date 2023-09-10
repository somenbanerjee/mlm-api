<?php

namespace App\Controllers\Member;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PackageModel;
use App\Models\MemberPackageModel;

class PackageController extends BaseController
{
    use ResponseTrait;

    protected $packageModel;
    protected $memberPackageModel;

    public function __construct()
    {
        helper(['general']);
        $this->packageModel = new PackageModel();
        $this->memberPackageModel = new MemberPackageModel();
    }

    public function getAllPackages()
    {
        $packages = $this->packageModel->select(['id', 'package_name', 'amount'])->getWhere(['status' => 1])->getResult();
        if ($packages) {
            $response = [
                'status' => 'success',
                'data' => $packages,
                'message' => 'Data found.'
            ];
            return $this->respond($response, ResponseInterface::HTTP_OK);
        }
        $response = [
            'status' => 'error',
            'data' => null,
            'message' => 'No data found.'
        ];
        return $this->respond($response, ResponseInterface::HTTP_NOT_FOUND);
    }


    public function packageActivation()
    {
        $rules = [
            'packageId' => 'required|is_natural_no_zero|integer',
        ];

        if (!$this->validate($rules)) {
            $response = [
                'status' => 'error',
                'data' => $this->validator->getErrors(),
                'message' => 'Invalid Inputs.'
            ];
            return $this->respond($response, ResponseInterface::HTTP_CONFLICT);
        }

        
    }
}
