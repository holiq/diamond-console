<?php

namespace KoalaFacade\DiamondConsole\Commands\Infrastructure;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use KoalaFacade\DiamondConsole\Actions\Factory\FactoryContractMakeAction;
use KoalaFacade\DiamondConsole\Commands\Concerns\HasArguments;
use KoalaFacade\DiamondConsole\Commands\Concerns\HasOptions;
use KoalaFacade\DiamondConsole\Commands\Concerns\InteractsWithConsole;
use KoalaFacade\DiamondConsole\Contracts\Console;
use KoalaFacade\DiamondConsole\DataTransferObjects\NamespaceData;
use KoalaFacade\DiamondConsole\DataTransferObjects\PlaceholderData;
use KoalaFacade\DiamondConsole\Exceptions\FileAlreadyExistException;
use KoalaFacade\DiamondConsole\Support\Source;

class FactoryMakeCommand extends Command implements Console
{
    use HasArguments, HasOptions, InteractsWithConsole;

    protected $signature = 'infrastructure:make:factory {name} {domain} {--model=} {--force}';

    protected $description = 'Create a model Factory';

    protected Console $factoryContractMakeAction;

    /**
     * @throws FileNotFoundException
     * @throws FileAlreadyExistException
     */
    public function beforeCreate(): void
    {
        $this->info(string: 'Generating factory & interface file to your project');

        $this->resolveFactoryContractConsole(
            console: FactoryContractMakeAction::resolve(parameters: ['console' => $this])->execute()
        );
    }

    public function afterCreate(): void
    {
        $this->info(string: 'Succeed generate Factory concrete at ' . $this->getFullPath());
    }

    public function resolveFactoryContractConsole(Console $console): void
    {
        $this->factoryContractMakeAction = $console;
    }

    public function resolvePlaceholders(): PlaceholderData
    {
        return new PlaceholderData(
            namespace: $this->getNamespace(),
            class: $this->resolveNameArgument(),
            contractName: $this->factoryContractMakeAction->getClassName(),
            contractNamespace: $this->factoryContractMakeAction->getNamespace(),
            model: $this->resolveModelName(),
            modelNamespace: $this->getModelNamespace(),
        );
    }

    protected function getModelNamespace(): ?string
    {
        return Source::resolveNamespace(
            data: new NamespaceData(
                domainArgument: $this->resolveDomainArgument(),
                structures: Source::resolveInfrastructurePath(),
                nameArgument: $this->resolveModelName(),
                endsWith: 'Database\\Models',
            )
        );
    }

    protected function resolveModelName(): string
    {
        /** @var string | null $model */
        $model = $this->option(key: 'model');

        return $model ?? Str::replaceLast(search: 'Factory', replace: '', subject: $this->getClassName());
    }

    public function getStubPath(): string
    {
        return Source::resolveStubForPath(name: 'infrastructure/factory');
    }

    public function getNamespace(): string
    {
        return Source::resolveNamespace(
            data: new NamespaceData(
                domainArgument: $this->resolveDomainArgument(),
                structures: Source::resolveInfrastructurePath(),
                nameArgument: $this->resolveNameArgument(),
                endsWith: 'Database\\Factories',
            )
        );
    }

    public function getClassName(): string
    {
        return Str::finish(Str::ucfirst($this->resolveNameArgument()), cap: 'Factory');
    }
}
