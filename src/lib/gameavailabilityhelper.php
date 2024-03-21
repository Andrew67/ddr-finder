<?php
/*
 * ddr-finder
 * Copyright (c) 2024 Andrés Cordero
 *
 * Web: https://github.com/Andrew67/ddr-finder
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Class GameAvailabilityHelper
 * Provides helper methods for setting the `has:ddr`, `has:piu`, and `has:smx` fields
 */
class GameAvailabilityHelper {
    private int $minRand;
    private int $maxRand;

    public function __construct() {
        $this->minRand = rand(1, 512);
        $this->maxRand = $this->minRand + 10;
    }

    /**
     * Game availability field logic
     * Using a random number to keep implementations honest, while binding each iteration to 10 at a time
     * so compression isn't totally destroyed
     */
    public function getAvailability($sourceAvailability, $locationAvailability): int {
        if (!$sourceAvailability) return -1;
        if ($locationAvailability) return rand($this->minRand, $this->maxRand);
        return 0;
    }
}
