<?php

if (!function_exists('encryptToken')) {
    function encryptToken($data) {
        if ($data === 'error') return 'Error';
        return Crypt::encrypt(json_encode($data));
    }
}

if (!function_exists('decryptToken')) {
    function decryptToken($data) {
        try {
            return json_decode(Crypt::decrypt($data));
        } catch (\Exception $e) {
            return 'error';
        }
    }
}

if (!function_exists('setMeta')) {
    function setMeta($status, $message = null) {
        $result = [
            'status' => $status,
            'message' => $message,
        ];
        switch($status) {
            case 200:
                $result['message'] = $message ?? 'Ok';
                break;
            case 201:
                $result['message'] = $message ?? 'Created';
                break;
            case 400:
                $result['message'] = $message ?? 'Bad request - Please double check the data you sent';
                break;
            case 401:
                $result['message'] = $message ?? 'Unauthorized - This action need a token access';
                break;
            case 403:
                $result['message'] = $message ?? "Forbidden - You don't have an autorized for this action";
                break;
            case 404:
                $result['message'] = $message ?? "Data not found";
                break;
            case 405:
                $result['message'] = $message ?? "Method not allowed";
                break;
            default:
                $result['status'] = $status ?? 500;
                $result['message'] = $message ?? 'Somethins went wrong';
        }
        return $result;
    }
}

if (!function_exists('setRes')) {
    function setRes($data, $status, $message = null) {
        $resultMessage = setMeta($status, $message);
        $response = [
            'meta' => [
                'is_success' => $status >= 200 && $status <= 299 ? true : false,
                'status' => $resultMessage['status'],
                'message' => $resultMessage['message'],
            ],
            'data' => $data
        ];

        return response($response, $status);
    }
}

if (!function_exists('isTokenExpired')) {
    function isTokenExpired($valid_until) {
        $result = false;
        $valid_token = Carbon::parse($valid_until);
        $now = Carbon::now();
        if($valid_token < $now) $result = true;
        return $result;
    }
}

if (!function_exists('paginateData')) {
    function paginateData($data) {
        $response = [
            'list' => [],
            'paginate' => [
                'current_page' => 1,
                'prev_page' => null,
                'next_page' => null,
                'per_page' => 10,
                'total_page' => null,
                'total_list' => null,
                'total_data' => null,
                'pages' => []
            ]
        ];

        if ($data) {
            $data = $data->toArray();
            $prev_page = $data['prev_page_url'] ? (int) explode('=', $data['prev_page_url'])[1] : null;
            $next_page = $data['next_page_url'] ? (int) explode('=', $data['next_page_url'])[1] : null;
            $response = [
                'list' => $data['data'],
                'paginate' => [
                    'current_page' => $data['current_page'],
                    'prev_page' => $prev_page,
                    'next_page' => $next_page,
                    'per_page' => (int) $data['per_page'],
                    'total_page' => (int) $data['last_page'],
                    'total_list' => (int) $data['to'],
                    'total_data' => (int) $data['total'],
                ]
            ];
            foreach($data['links'] as $item) {
                if ($item['label'] != '&laquo; Previous' && $item['label'] != 'Next &raquo;') {
                    $page = explode('=',$item['url'])[1];
                    $active = $item['active'];
                    $response['paginate']['pages'][] = [
                        'active' => $active,
                        'value' => (int) $page,
                    ];
                }
            }
        }

        return $response;
    }
}

if (!function_exists('setValidationMessage')) {
    function setValidationMessage($validator, $data) {
        $errors = [
            'error' => [],
            'errors' => []
        ];

        foreach($data as $item => $key) {
            if ($validator->errors()->has($key)) {
                $errors['error'][$key] = $validator->errors()->first($key);
                $errors['errors'][] = [
                    'field' => $key,
                    'message' => $validator->errors()->first($key),
                ];
                $is_error = true;
            }
        }

        return $errors;
    }
}