<?php

class NanoChart
{
    private array $values;
    private int $totalValue;
    private array $keys;
    private string $legend = '';
    private string $class = '';
    private string $direction = 'row';
    private string $style = '';
    private int $size = 200;
    private int $hue = 220;
    private array $palette;
    private int $startAngle = 270;
    private int $cx;
    private int $cy;
    private int $radius;
    private int $innerRadius;

    public function __construct(array $values)
    {
        $this->keys = $values;

        $values = array_values($values);

        if (count($values) === 1) {

            $values[] = 0;
        }

        $this->totalValue = array_sum($values) !== 0 ? array_sum($values) : 1;
        $this->values = $this->totalValue === 0 ? [] : $values;
    }

    public function setSize(int $size)
    {
      $this->size = $size;
    }

    public function setDirection(string $direction)
    {
        $directions = [
            'column',
            'row'
        ];

        if (in_array($direction, $directions, true)) {

            $this->direction = $direction;
        }
    }

    public function setStyle(string $style)
    {
      $this->style = $style;
    }

    public function setStartPosition(string $startPosition)
    {
        $angles = [
            'top' => 270,
            'right' => 360,
            'bottom' => 90,
            'left' => 180
        ];

        if (isset($angles[$startPosition])) {

            $this->startAngle = $angles[$startPosition];
        }
    }

    public function setHue(int $hue)
    {
      $this->hue = $hue;
    }

    public function setPalette(array $palette)
    {
      $this->palette = $palette;
    }

    public function build(): string
    {
        $this->buildDimensions();
        $this->buildPalette();
        $this->makeLegend();
        $this->makeCssStyles();

        $chart = $this->makeSvgOpenTag();
        $chart = $this->totalValue === 0 ? $this->makeEmptyChart($chart) : $this->makeChart($chart);
        $chart = $this->makeSvgCloseTag($chart);
        $chart = $this->addCssStyles($chart);
        $chart = $this->addLegend($chart);

        return $this->encloseInWrapper($chart);
    }

    private function buildDimensions(): void
    {
        $this->cx = round($this->size / 2);
        $this->cy = round($this->size / 2);
        $this->radius = min($this->cx, $this->cy) - 4;
        $this->innerRadius = $this->style === 'doughnut' ? round($this->radius / 2) : 0;
    }

    private function buildPalette()
    {
        $count = 100 / count($this->values);
        $range = range(10, 90, $count);

        foreach ($range as $lightness) {

            $this->palette[] = 'hsl(' . $this->hue . ', 100%, ' . $lightness . '%)';
        }
    }

    private function makeSvgOpenTag(): string
    {
        return '<svg style="aspect-ratio:1" width="' . $this->size . '" viewBox=" " xmlns="http://www.w3.org/2000/svg">';
    }

    private function makeSvgCloseTag(string $chart): string
    {
        return $chart . '</svg>';
    }

    private function addCssStyles(string $chart)
    {
        if (!empty($this->class)) {

            $chart .= $this->class;
        }

        return $chart;
    }

    private function addLegend(string $chart)
    {
        if (!empty($this->legend)) {

            $chart .= $this->legend;
        }

        return $chart;
    }

    private function encloseInWrapper(string $item): string
    {
        switch ($this->direction) {

            case 'column':

                return '<div style="display:flex;flex-direction:column;gap:1.5rem;align-items:center">' . $item   . '</div>';

            case 'row':

                return '<div style="display:flex;flex-direction:row;gap:1.5rem;align-items:center">' . $item   . '</div>';
        }
    }

    private function makeEmptyChart(string $chart): string
    {
        return $chart . '<circle cx="50%" cy="50%" r="50%" fill="hsla(0, 0%, 100%, .025)" />';
    }

    private function makeChart(string $chart): string
    {
        $startAngle = $this->startAngle;
        $endAngle = 0;

        $positiveCounts = [];

        foreach ($this->values as $value) {

            if ($value > 0) {

                $positiveCounts[] = $value;
            }
        }

        if (count($positiveCounts) === 1) {

            $index = array_key_first($positiveCounts);

            $colour = $this->palette[$index];

            $chart .= '<path d="M' . $this->cx . ',' . ($this->cy - $this->radius) . ' A' . $this->radius . ',' . $this->radius . ' 0 1,1 ' . $this->cx . ',' . ($this->cy + $this->radius) . ' A' . $this->radius . ',' . $this->radius . ' 0 1,1 ' . $this->cx . ',' . ($this->cy - $this->radius) . ' L' . $this->cx . ',' . ($this->cy - $this->innerRadius) . ' A' . $this->innerRadius . ',' . $this->innerRadius . ' 0 1,0 ' . $this->cx . ',' . ($this->cy + $this->innerRadius) . ' A' . $this->innerRadius . ',' . $this->innerRadius . ' 0 1,0 ' . $this->cx . ',' . ($this->cy - $this->innerRadius) . ' Z" fill="' . $colour . '"/>';

            return $chart;
        }

        foreach ($this->values as $index => $value) {

            $endAngle = $startAngle + ($value / $this->totalValue) * 360;

            $x1 = $this->cx + $this->radius * cos(deg2rad($startAngle));
            $y1 = $this->cy + $this->radius * sin(deg2rad($startAngle));
            $x2 = $this->cx + $this->radius * cos(deg2rad($endAngle));
            $y2 = $this->cy + $this->radius * sin(deg2rad($endAngle));

            $x1Inner = $this->cx + $this->innerRadius * cos(deg2rad($startAngle));
            $y1Inner = $this->cy + $this->innerRadius * sin(deg2rad($startAngle));
            $x2Inner = $this->cx + $this->innerRadius * cos(deg2rad($endAngle));
            $y2Inner = $this->cy + $this->innerRadius * sin(deg2rad($endAngle));

            $largeArc = ($endAngle - $startAngle) > 180 ? 1 : 0;

            $chart .= '<path d="M' . $x1 . ',' . $y1 . ' A' . $this->radius . ',' . $this->radius . ' 0 ' . $largeArc . ',1 ' . $x2 . ',' . $y2 . ' L' . $x2Inner . ',' . $y2Inner . ' A' . $this->innerRadius . ',' . $this->innerRadius . ' 0 ' . $largeArc . ',0 ' . $x1Inner . ',' . $y1Inner . ' Z" fill="' . $this->palette[$index] . '"/>';

            $startAngle = $endAngle;
        }

        return $chart;
    }

    private function makeLegend()
    {
        if (!empty($this->keys)) {

            $this->legend = '<ul>';

            foreach ($this->keys as $key => $value) {

                if (is_int($key)) {

                    $this->legend .= '<li>' . $value . '</li>';
                }

                else {

                    $this->legend .= '<li><span>' . $key . '</span><span>' . $value . '</span></li>';
                }
            }

            $this->legend .= '</ul>';
        }
    }

    private function makeCssStyles()
    {
        if (!empty($this->palette)) {

            $this->class .= '<style>';

            foreach ($this->palette as $index => $colour) {

                $this->class .= 'ul li {display: flex; justify-content: space-between; gap: 1.5rem; width: 100%} ul li:nth-child(' . ($index + 1) . ') {position:relative;left:-1rem;list-style:none; color:' . $colour . ';}';
            }

            $this->class .= '</style>';
        }
    }
}
