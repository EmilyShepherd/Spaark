<?php namespace Spaark\Core\Model\Base\Entity;

class Order extends UnaryOperator
{
    /**
     * Predicate
     *
     * @var Predicate
     * @readable
     */
    protected $attribute;

    public function __construct($startnput, $attr)
    {
        $this->attribute = $attr;
        parent::__construct($startnput);
    }

    public function get($pos)
    {
        $this->data = array( );

        foreach ($this->input as $item)
        {
            if ($item === NULL) break;

            $this->data[] = $item;
        }

        $this->sort();

        return $this->data[$pos];
    }

    function sort()
    {
        $B = array( );
        $n = count($this->data);

        // Increase Width Incrementally
        for ($width = 1; $width < $n; $width = 2 * $width)
        {
            // Pick each pair of runs out of the list
            for ($start = 0; $start < $n; $start += 2 * $width)
            {
                $this->merge
                (
                    $start,
                    min($n, $start + $width),
                    min($n, $start + 2 * $width),
                    $B
                );
            }

            $this->data = $B;
        }
    }

    function merge($start, $midPoint, $end, &$B)
    {
        $run1 = $start;
        $run2 = $midPoint;

        // Merge the runs
        for ($k = $run1; $k < $end; $k++)
        {
            if
            (
                // If run1 is done, use run2
                $run1 >= $midPoint  || 
                // If run2 has a smaller element in it, use run2
                $run2 < $end && $this->compare($run2, $run1) < 0
            )
            {
                $B[$k] = $this->data[$run2++];
            }
            else
            {
                $B[$k] = $this->data[$run1++];
            }
        }
    }

    function compare($a, $b)
    {
        $a = $this->data[$a][$this->attribute];
        $b = $this->data[$b][$this->attribute];

        return strcmp($a, $b);
    }

    public function calculateCost()
    {
        //
    }
}
