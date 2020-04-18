<?php
namespace Paranoia\Core\Formatter;

class MultiDigitInstallmentFormatter
{
    /**
     * @param int|null $input
     * @return string
     */
    public function format(?int $input): string
    {
        return (!is_numeric($input) || intval($input) <= 1) ? '00' : sprintf('%02s', $input);
    }
}