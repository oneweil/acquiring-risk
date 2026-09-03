<?php

declare(strict_types=1);

namespace app\resource;

use ArrayAccess;
use JsonSerializable;
use think\Model;
use think\Paginator;

/**
 * 轻量 API Resource（仿 Laravel JsonResource，无第三方依赖）
 */
abstract class JsonResource implements ArrayAccess, JsonSerializable
{
    public function __construct(protected mixed $resource)
    {
    }

    public static function make(mixed $resource): static
    {
        return new static($resource);
    }

    /**
     * @param iterable<mixed> $items
     * @return list<array<string, mixed>>
     */
    public static function collection(iterable $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = (new static($item))->toArray();
        }

        return $result;
    }

    /**
     * 包装 ThinkPHP 分页器，保留 total/per_page/current_page/last_page，替换 data 为资源数组
     *
     * @return array<string, mixed>
     */
    public static function paginate(Paginator $paginator): array
    {
        $payload         = $paginator->toArray();
        $payload['data'] = static::collection($paginator);

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __get(string $key): mixed
    {
        $resource = $this->resource;

        if ($resource instanceof Model) {
            return $resource->getAttr($key);
        }

        if (is_array($resource)) {
            return $resource[$key] ?? null;
        }

        if (is_object($resource) && isset($resource->{$key})) {
            return $resource->{$key};
        }

        return null;
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->__get((string) $offset) !== null;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('API Resource 为只读');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('API Resource 为只读');
    }
}
