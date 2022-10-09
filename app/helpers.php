<?php

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * Get the authenticated user's organization.
 */
function currentOrganization() {
    return Auth::user()->organization;
}

/**
 * Called by router when a type hinted resource is not found.
 */
function resourceNotFound(Request $request) {
    return responseError("Resource not found", 404);
}

/**
 * A success response. Always contains a message.
 */
function responseOk(string $message, array $extra = [], int $status = 200) : JsonResponse
{
    return response()->json([
        "message" => $message
    ] + $extra, $status);
}

/**
 * An error response. Contains an error message, or an array of error messages.
 */
function responseError($errors, int $status = 400) : JsonResponse
{
    return response()->json([
        "errors" => $errors
    ], $status);
}

/**
 * Validate data and return with error in case of fail.
 */
function validate(array $data, $rules)
{
    $validator = Validator::make($data, $rules);

    if ($validator->fails()) {
        return [
            'valid' => false,
            'messages' => $validator->messages()->toArray()
        ];
    }

    return [
        'valid' => true,
        'messages' => ''
    ];
}

/**
 * Convert array to a stdClass.
 */
function makeObj(array $data) : object
{
    return (object)$data;
}

/**
 * Takes in a path separated by forward slashes and returs the last part (file name).
 */
function fileNameFromPath1($path)
{
    $parts = explode('/', rtrim($path, '/'));
    return end($parts);
}
