<?php

namespace Pest;

/**
 * @template TValue
 */
class Expectation
{
    /**
     * @template TSequenceValue
     *
     * @param  (callable(self<value-of<TValue>>, self<key-of<TValue>>): void)|TSequenceValue  $callbacks
     * @return self<TValue>
     */
    public function sequence(mixed $callbacks): self {}
}
