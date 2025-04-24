<?php

declare(strict_types=1);

namespace SParallelLaravel\Implementation;

use Laravel\SerializableClosure\SerializableClosure;
use SParallel\Contracts\SerializerInterface;
use SParallel\Transport\OpisSerializer;
use Throwable;

class Serializer implements SerializerInterface
{
    public function __construct(protected OpisSerializer $opisSerializer)
    {
    }

    public function serialize(mixed $data): string
    {
        if (is_callable($data)) {
            return serialize(new SerializableClosure($data));
        } else {
            return $this->opisSerializer->serialize($data);
        }
    }

    public function unserialize(?string $data): mixed
    {
        if (is_null($data)) {
            return null;
        }

        try {
            $unSerialized = unserialize($data);

            if ($unSerialized instanceof SerializableClosure) {
                return $unSerialized->getClosure();
            }
        } catch (Throwable) {
            //
        }

        return $this->opisSerializer->unserialize($data);
    }
}
