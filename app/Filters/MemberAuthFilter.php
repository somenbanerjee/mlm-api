<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use Exception;

class MemberAuthFilter implements FilterInterface
{
    use ResponseTrait;

    public function __construct()
    {
        helper('jwt');
    }

    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    use ResponseTrait;

    public function before(RequestInterface $request, $arguments = null)
    {
        $bearer_token = $request->header("Authorization");
        try {
            $token = getJwtFromHeader($bearer_token);

            $member = validateJWTForMember($token);
            $request->member = $member; // if we want to pass member data back to the controller
            return $request;
        } catch (Exception $ex) {
            return service('response')->setJSON(
                [
                    'error' => $ex->getMessage()
                ]
            )->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
