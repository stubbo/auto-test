<?php

namespace App\Console\Commands;

use App\AutoTest\Request\RequestFaker;
use App\AutoTest\Request\RequestRule;
use Illuminate\Console\Command;
use Illuminate\Foundation\Http\FormRequest;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use Symfony\Component\Console\Input\InputArgument;

class TestGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'test:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate unit test for a controller';

    protected string $requestClass;

    /**
     * Execute the console command.
     *
     * @return int
     * @throws ReflectionException
     */
    public function handle(): int
    {
        $this->requestClass = str_replace('/', '\\', $this->input->getArgument('class'));
        if (! class_exists($this->requestClass)) {
            $this->error(sprintf('The class %s does not exist.', $this->requestClass));
            return 1;
        }

        [$requestType, $returnType] = $this->getTypes();

        /**
         * @var FormRequest $return
         */
        $request = new ((string) $requestType->getType());
        $rules = RequestRule::fromRequest($request);

        $test = new RequestFaker($rules);
        dd($test->make());

        return 0;
    }

    /**
     * @throws ReflectionException
     */
    protected function getTypes(): bool|array
    {
        $reflect = new ReflectionMethod($this->requestClass, $this->input->getArgument('method'));

        $parameters = collect($reflect->getParameters());
        $requestType = $parameters->filter(
            fn(ReflectionParameter $param) => is_subclass_of((string) $param->getType(), FormRequest::class)
        )->first();

        $returnType = $reflect->getReturnType();

        if (empty($requestType) || empty($returnType)) {
            $this->error(sprintf('The class %s must have a request and return type.', $this->requestClass));

            return false;
        }

        return [$requestType, $returnType];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['class', InputArgument::REQUIRED, 'The class name of the controller', null],
            ['method', InputArgument::REQUIRED, 'The method you want to test', null],
        ];
    }
}
