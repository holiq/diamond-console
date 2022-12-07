<?php

namespace KoalaFacade\DiamondConsole\Commands;

use Illuminate\Console\Command;
use KoalaFacade\DiamondConsole\Commands\Concerns\HasArguments;
use KoalaFacade\DiamondConsole\Commands\Concerns\HasOptions;
use KoalaFacade\DiamondConsole\Commands\Concerns\InteractsWithConsole;
use KoalaFacade\DiamondConsole\Contracts\Console;
use KoalaFacade\DiamondConsole\DataTransferObjects\PlaceholderData;
use KoalaFacade\DiamondConsole\Support\Source;

class ValueObjectMakeCommand extends Command implements Console
{
    use HasArguments, HasOptions, InteractsWithConsole;

    protected $signature = 'domain:make:value-object {name} {domain} {--force}';

    protected $description = 'Create a new ValueObject';

    public function beforeCreate(): void
    {
        $this->info(string: 'Generating value object file to your project');
    }

    public function afterCreate(): void
    {
        $this->info(string: 'Successfully generate ValueObject file');
    }

    public function getNamespace(): string
    {
        return Source::resolveNamespace(
            structures: Source::resolveDomainPath(),
            prefix: $this->resolveDomainArgument(),
            suffix: 'ValueObjects'
        );
    }

    public function getStubPath(): string
    {
        return Source::resolveStubForPath(name: 'value-object');
    }

    public function resolvePlaceholders(): PlaceholderData
    {
        return new PlaceholderData(
            namespace: $this->getNamespace(),
            class: $this->getClassName(),
        );
    }
}
