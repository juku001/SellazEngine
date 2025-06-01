<?php

namespace App\Helpers;


class ResponseHelper
{



    public static function error(
        $message = "Something went wrong.",
        $errors = [],
        $code = 500
    ) {
        $response = [
            'status' => false,
            'message' => $message,
            'code' => $code
        ];

        if (isset($errors) && !empty($errors)) {
            $response['errors'] = $errors;
        }
        return response()->json(
            $response,
            $code
        );
    }


    public static function success(
        $message = "Request was successful.",
        $data = [],
        $code = 200
    ) {

        $response = [
            'status' => true,
            'message' =>$message,
            'code' => $code
        ];

        if (isset($data) && !empty($data)) {
            $response['data'] = $data;
        }

        return response()->json(
            $response,
            $code
        );
    }


}