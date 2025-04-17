<?php

namespace SParallelLaravel;

use Laravel\SerializableClosure\SerializableClosure;
use RuntimeException;
use SParallel\Contracts\SerializerInterface;

class Serializer implements SerializerInterface
{
    public function serialize(mixed $data): string
    {
        return serialize(new SerializableClosure($data));
    }

    public function unserialize(?string $data): mixed
    {
        if (is_null($data)) {
            return null;
        }

        $unSerialized = unserialize($data);

        if ($unSerialized instanceof SerializableClosure) {
            return $unSerialized->getClosure();
        }

        throw new RuntimeException(
            "Expected SerializableClosure, got: " . (is_null($unSerialized) ? 'null' : gettype($unSerialized))
        );
    }
}
