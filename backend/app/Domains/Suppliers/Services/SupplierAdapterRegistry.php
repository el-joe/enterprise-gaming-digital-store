<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Suppliers\Contracts\SupplierAdapter;
use App\Domains\Suppliers\Exceptions\SupplierNotConfigured;
use App\Domains\Suppliers\Models\Supplier;
use Illuminate\Contracts\Container\Container;
use LogicException;

/**
 * Resolves `suppliers.adapter_key` to an adapter instance configured for that supplier row.
 * Built-in adapters come from config/suppliers.php. Additional adapters (test doubles) may be
 * registered at runtime, but never in production (SECURITY.md section 6).
 */
class SupplierAdapterRegistry
{
    /**
     * @var array<string, class-string<SupplierAdapter>>
     */
    private array $extra = [];

    public function __construct(private Container $container) {}

    /**
     * @param  class-string<SupplierAdapter>  $class
     */
    public function register(string $key, string $class): void
    {
        if ($this->container->make('app')->isProduction()) {
            throw new LogicException('Supplier adapters cannot be registered at runtime in production.');
        }

        $this->extra[$key] = $class;
    }

    public function has(string $key): bool
    {
        return $this->classFor($key) !== null;
    }

    /**
     * @throws SupplierNotConfigured
     */
    public function for(Supplier $supplier): SupplierAdapter
    {
        $class = $this->classFor($supplier->adapter_key) ?? throw new SupplierNotConfigured("No adapter is registered for {$supplier->adapter_key}.");

        return $this->container->make($class, ['supplier' => $supplier]);
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys([...config('suppliers.adapters'), ...$this->extra]);
    }

    /**
     * @return class-string<SupplierAdapter>|null
     */
    private function classFor(string $key): ?string
    {
        return $this->extra[$key] ?? config("suppliers.adapters.{$key}");
    }
}
