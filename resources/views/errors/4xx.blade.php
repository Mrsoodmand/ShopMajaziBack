@include('errors.d', ['code' => (string) (isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 400)])
