<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @OA\Info(
     *   title="Joybox API for Library",
     *   version="1.0",
     *   @OA\Contact(
     *     email="giarsyani.nuli@gmail.com",
     *     name="Support Team"
     *   )
     * )
     */

    /**
    *  @OA\SecurityScheme(
    *      securityScheme="bearerAuth",
    *      type="http",
    *      scheme="bearer"
    *  )
    **/
}
