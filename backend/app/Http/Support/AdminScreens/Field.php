<?php

namespace App\Http\Support\AdminScreens;

use BackedEnum;
use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One field of an admin edit form (M15), rendered by `<x-admin.input>`. The field name is the
 * JSON key the admin API validates (dot keys for nested values); the value is read from the
 * record with `data_get`, or from the `using` closure. Envelope errors appear beside the field
 * (`[data-error-for]`).
 *
 * Types: text, textarea, html (sanitized server side), number, decimal (money and percent as
 * strings, never a float), select, multi (a list of checkboxes), toggle, datetime, date, json
 * (an object or list typed as JSON), password (write-only, never filled).
 */
final class Field
{
    public string $help = '';

    public bool $required = false;

    public bool $nullable = false;

    public bool $wide = false;

    public bool $ltr = false;

    /** @var array<string|int, string> */
    public array $options = [];

    public ?int $maxLength = null;

    private ?Closure $resolver = null;

    private mixed $fixed = null;

    private bool $hasFixed = false;

    private function __construct(public string $name, public string $label, public string $type) {}

    public static function make(string $type, string $name, string $label): self
    {
        return new self($name, $label, $type);
    }

    public static function text(string $name, string $label): self
    {
        return new self($name, $label, 'text');
    }

    public static function textarea(string $name, string $label): self
    {
        return (new self($name, $label, 'textarea'))->wide();
    }

    public static function html(string $name, string $label): self
    {
        return (new self($name, $label, 'html'))->wide();
    }

    public static function number(string $name, string $label): self
    {
        return (new self($name, $label, 'number'))->ltr();
    }

    public static function decimal(string $name, string $label): self
    {
        return (new self($name, $label, 'decimal'))->ltr();
    }

    /**
     * @param  array<string|int, string>  $options  value => label
     */
    public static function select(string $name, string $label, array $options): self
    {
        $field = new self($name, $label, 'select');
        $field->options = $options;

        return $field;
    }

    /**
     * @param  array<string|int, string>  $options  value => label
     */
    public static function multi(string $name, string $label, array $options): self
    {
        $field = (new self($name, $label, 'multi'))->wide();
        $field->options = $options;

        return $field;
    }

    public static function toggle(string $name, string $label): self
    {
        return new self($name, $label, 'toggle');
    }

    public static function datetime(string $name, string $label): self
    {
        return (new self($name, $label, 'datetime'))->nullable();
    }

    public static function json(string $name, string $label): self
    {
        return (new self($name, $label, 'json'))->wide()->ltr();
    }

    /**
     * A list typed as comma-separated values (ids or codes), sent as a JSON list.
     */
    public static function csv(string $name, string $label): self
    {
        return (new self($name, $label, 'csv'))->wide()->ltr();
    }

    public static function password(string $name, string $label): self
    {
        return (new self($name, $label, 'password'))->ltr();
    }

    public function help(string $help): self
    {
        $this->help = $help;

        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Send null instead of an empty string.
     */
    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function wide(bool $wide = true): self
    {
        $this->wide = $wide;

        return $this;
    }

    public function ltr(bool $ltr = true): self
    {
        $this->ltr = $ltr;

        return $this;
    }

    public function max(int $length): self
    {
        $this->maxLength = $length;

        return $this;
    }

    /**
     * @param  Closure(Model): mixed  $resolver
     */
    public function using(Closure $resolver): self
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * A value for new records (create form).
     */
    public function default(mixed $value): self
    {
        $this->fixed = $value;
        $this->hasFixed = true;

        return $this;
    }

    /**
     * The form value of the field for the record (null: a new record), as the input shows it.
     */
    public function valueFor(?Model $record): mixed
    {
        if ($this->type === 'password') {
            return null;
        }

        if ($record === null) {
            $value = $this->hasFixed ? $this->fixed : null;
        } else {
            $value = $this->resolver !== null ? ($this->resolver)($record) : data_get($record, $this->name);
        }

        return $this->present($value);
    }

    private function present(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        return match ($this->type) {
            'toggle' => (bool) $value,
            'multi' => array_map('strval', array_map(static fn (mixed $item): mixed => $item instanceof BackedEnum ? $item->value : $item, (array) ($value ?? []))),
            'datetime' => $value === null || $value === '' ? '' : ($value instanceof DateTimeInterface ? Carbon::instance($value) : Carbon::parse((string) $value))->timezone((string) config('app.timezone'))->format('Y-m-d\TH:i'),
            'csv' => implode(', ', array_map(static fn (mixed $item): string => (string) ($item instanceof BackedEnum ? $item->value : $item), (array) ($value ?? []))),
            'json' => $value === null || $value === [] ? '' : json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            default => $value === null ? '' : (string) $value,
        };
    }

    /**
     * The `[data-error-for]` key: the dot form of the field name.
     */
    public function errorKey(): string
    {
        return str_replace(['][', '[', ']'], ['.', '.', ''], $this->name);
    }

    /**
     * The HTML name: dot keys become bracket names (`a.b` to `a[b]`).
     */
    public function inputName(): string
    {
        $parts = explode('.', $this->name);

        return array_shift($parts).implode('', array_map(static fn (string $part): string => "[{$part}]", $parts));
    }
}
