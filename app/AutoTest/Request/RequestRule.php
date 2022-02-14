<?php

namespace App\AutoTest\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use function collect;

class RequestRule
{
    public function __construct(public array $rules, public string $key)
    {
    }

    public static function fromRequest(FormRequest $request): Collection
    {
        $rules = collect($request->rules())->map(
            fn($rule) => is_array($rule) ? $rule : explode('|', $rule)
        );

        return $rules->mapInto(self::class)->values();
    }
}
