<?php

declare(strict_types=1);

namespace Rawilk\Printing\Api\Cups\Types;

use Rawilk\Printing\Api\Cups\Exceptions\RangeOverlap;
use Rawilk\Printing\Api\Cups\Type;
use Rawilk\Printing\Api\Cups\TypeTag;

class RangeOfInteger extends Type
{
    protected int $tag = TypeTag::RANGEOFINTEGER->value;

    /**
     * @param int[] $value - Array of 2 integers
     */
    public function __construct(public mixed $value)
    {
    }

    public function encode(): string
    {
        return pack('n', 8) . pack('N', $this->value[0]) .  pack('N', $this->value[1]);
    }

    public static function fromBinary(string $binary, ?int $length = null): self
    {
        $value = unpack('Nl/Nu', $binary);
        return new static([$value['l'], $value['u']]);
    }

    /**
     * Sorts and checks the array for overlaps
     *
     * @param array<int, RangeOfInteger> $values
     * @throws RangeOverlap
     */
    public static function checkOverlaps(array &$values)
    {
        usort(
            $values,
            function ($a, $b) {
                return $a->value[0] - $b->value[0];
            }
        );

        $count = count($values);
        for ($i = 0; $i < $count - 1; $i++) {
            if ($values[$i]->value[1] >= $values[$i + 1]->value[0]) {
                throw new \Rawilk\Printing\Api\Cups\Exceptions\RangeOverlap('Range overlap is not allowed!');
            }
        }
        return true; // No overlaps found
    }
}
