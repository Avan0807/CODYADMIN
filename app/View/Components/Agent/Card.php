<?php

namespace App\View\Components\Agent;

use Illuminate\View\Component;

class Card extends Component
{
    public $icon;
    public $title;
    public $value;
    public $color;

    public function __construct($icon, $title, $value, $color = 'primary')
    {
        $this->icon = $icon;
        $this->title = $title;
        $this->value = $value;
        $this->color = $color;
    }

    public function render()
    {
        return view('components.agent.card');
    }
}
