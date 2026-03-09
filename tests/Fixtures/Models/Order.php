<?php

declare(strict_types=1);

namespace ImSuperlative\PhpstanPest\Tests\Fixtures\Models;

class Order
{
    public int $id = 1;
    public string $status = 'pending';

    /** @return list<LineItem> */
    public function getItems(): array
    {
        return [];
    }
}
