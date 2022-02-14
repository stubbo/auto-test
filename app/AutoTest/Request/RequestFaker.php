<?php

namespace App\AutoTest\Request;

use Exception;
use Faker\Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Validation\Rules\Password;
use Str;

class RequestFaker
{
    use Macroable;

    public array $classMap = [
        Password::class => 'password',
    ];

    protected array $fakeBody = [];
    protected Generator $faker;


    public function __construct(
        protected Collection $body,
    ) {
        $this->faker = app(Generator::class);
    }

    public function make(): array
    {
        $this->body->each(function (RequestRule $requestRule) {
            if (! in_array('required', $requestRule->rules, true)) {
                return;
            }

            $value = null;
            $rules = collect($requestRule->rules)->mapWithKeys(function ($rule) {
                if (is_object($rule) && array_key_exists(get_class($rule), $this->classMap)) {
                    return [$this->classMap[get_class($rule)] => ''];
                }

                if (!is_string($rule)) {
                    return [];
                }

                $ruleParts = explode(':', $rule);
                return count($ruleParts) === 1 ? [$ruleParts[0] => ''] : [$ruleParts[0] => $ruleParts[1]];
            });

            $method = 'property'.Str::ucfirst($requestRule->key);
            if (method_exists($this, $method) && $this->$method($value, $rules)) {
                $this->fakeBody[$requestRule->key] = $value;
                return;
            }

            $rules->each(function ($key, $rule) use (&$value, $rules) {
                if (! is_string($rule)) {
                    return;
                }

                $method = 'rule'.Str::ucfirst($rule);

                if (method_exists($this, $method)) {
                    if ($this->$method($value, $rules)) {
                        return;
                    }
                }
            });

            $this->fakeBody[$requestRule->key] = $value;
        });

        return $this->fakeBody;
    }

    public function propertyName(&$value): bool
    {
        $value = $this->faker->name;

        return true;
    }

    public function propertyEmail(&$value): bool
    {
        $value = $this->faker->email;

        return true;
    }

    /**
     * @throws Exception
     */
    public function ruleString(&$value, Collection $rules): bool
    {
        $max = $rules->get('max', 255);

        // let's just assume each sentence is roughly 50 chars - because it can change we need to also limit this.
        $sentenceCount = ceil($max / 50);
        $value = substr($this->faker->sentences($sentenceCount, true), 0, $max);

        return true;
    }

    public function rulePassword(&$value): bool
    {
        $value = $this->faker->password(12, 30);

        return true;
    }
}
