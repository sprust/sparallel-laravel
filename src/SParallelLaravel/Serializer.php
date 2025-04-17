<?php

namespace SParallelLaravel;

use Laravel\SerializableClosure\SerializableClosure;
use SParallel\Contracts\SerializerInterface;

class Serializer implements SerializerInterface
{
    public function serialize(mixed $data): string
    {
        if (is_callable($data)) {
            return serialize(new SerializableClosure($data));
        } else {
            return serialize($data);
        }
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

        return $unSerialized;
    }
}
