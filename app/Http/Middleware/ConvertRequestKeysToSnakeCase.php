<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ConvertRequestKeysToSnakeCase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $convertedInputs = $this->convertKeys($request->all());
        $request->replace($convertedInputs);

        return $next($request);
    }

    private function convertKeys(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = Str::snake($key);

            if (is_array($value)) {
                $value = $this->convertKeys($value);
            }

            $result[$newKey] = $value;
        }

        return $result;
    }
}
